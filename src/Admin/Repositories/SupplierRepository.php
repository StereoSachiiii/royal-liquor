<?php
declare(strict_types=1);

namespace App\Admin\Repositories;

use PDO;
use App\Admin\Models\SupplierModel;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

class SupplierRepository extends BaseRepository
{
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM suppliers 
                 WHERE is_active = TRUE AND deleted_at IS NULL 
                 ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM suppliers ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function getById(int $id): ?SupplierModel
    {
        $sql = "SELECT * FROM suppliers 
                 WHERE id = :id AND is_active = TRUE AND deleted_at IS NULL";
        $row = $this->fetchOne($sql, [':id' => $id]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByIdAdmin(int $id): ?SupplierModel
    {
        $row = $this->fetchOne("SELECT * FROM suppliers WHERE id = :id", [':id' => $id]);
        return $row ? $this->mapToModel($row) : null;
    }

    /**
     * Get supplier by ID with enriched product data for admin modal view
     */
    public function getByIdEnriched(int $id): ?array
    {
        // Get basic supplier info with aggregated product stats
        $sql = "SELECT 
                s.id, s.name, s.email, s.phone, s.address,
                s.is_active, s.created_at, s.updated_at,
                COUNT(DISTINCT p.id)::int as total_products,
                COUNT(DISTINCT CASE WHEN p.is_active THEN p.id END)::int as active_products,
                COALESCE(SUM(st.quantity - st.reserved), 0)::int as total_inventory,
                ROUND(AVG(p.price_cents)::numeric, 0)::int as avg_product_price_cents
            FROM suppliers s
            LEFT JOIN products p ON s.id = p.supplier_id AND p.deleted_at IS NULL
            LEFT JOIN stock st ON p.id = st.product_id
            WHERE s.id = :id AND s.deleted_at IS NULL
            GROUP BY s.id";
        
        $row = $this->fetchOne($sql, [':id' => $id]);
        if (!$row) {
            return null;
        }
        
        // Get products list for this supplier (top 10 by sales or recent)
        $productsSql = "SELECT 
                p.id, p.name, p.price_cents, p.is_active,
                p.image_url, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.supplier_id = :supplier_id AND p.deleted_at IS NULL
            ORDER BY p.is_active DESC, p.created_at DESC
            LIMIT 10";
        
        $products = $this->fetchAll($productsSql, [':supplier_id' => $id]);
        $row['products'] = $products;
        
        return $row;
    }

    public function getByName(string $name): ?SupplierModel
    {
        $sql = "SELECT * FROM suppliers 
                 WHERE name = :name AND is_active = TRUE AND deleted_at IS NULL";
        $row = $this->fetchOne($sql, [':name' => $name]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByEmail(string $email): ?SupplierModel
    {
        $sql = "SELECT * FROM suppliers 
                 WHERE email = :email AND is_active = TRUE AND deleted_at IS NULL";
        $row = $this->fetchOne($sql, [':email' => $email]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM suppliers WHERE name = :name AND deleted_at IS NULL";
        $params = [':name' => $name];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        return (int)$this->fetchColumn($sql, $params) > 0;
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM suppliers 
                 WHERE (name ILIKE :query OR email ILIKE :query OR phone ILIKE :query) 
                 AND is_active = TRUE AND deleted_at IS NULL
                 ORDER BY name LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':query' => "%$query%",
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM suppliers 
                 WHERE is_active = TRUE AND deleted_at IS NULL";
        return (int)$this->fetchColumn($sql);
    }

    public function countAll(): int
    {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM suppliers WHERE deleted_at IS NULL");
    }

    public function create(array $data): SupplierModel
    {
        $sql = "INSERT INTO suppliers (name, email, phone, address, is_active) 
                 VALUES (:name, :email, :phone, :address, :is_active) 
                 RETURNING *";
        
        $stmt = $this->executeStatement($sql, [
            ':name' => $data['name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':is_active' => $data['is_active'] ?? true
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DatabaseException('Failed to create supplier');
        }
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?SupplierModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['name', 'email', 'phone', 'address', 'is_active'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) {
            return null;
        }

        $sql = "UPDATE suppliers SET " . implode(', ', $sets) . " 
                WHERE id = :id RETURNING *";
        
        $stmt = $this->executeStatement($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function partialUpdate(int $id, array $data): ?SupplierModel
    {
        return $this->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE suppliers SET is_active = FALSE, deleted_at = NOW() 
                 WHERE id = :id AND is_active = TRUE";
        $stmt = $this->executeStatement($sql, [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $sql = "UPDATE suppliers SET is_active = TRUE, deleted_at = NULL 
                 WHERE id = :id AND is_active = FALSE";
        $stmt = $this->executeStatement($sql, [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->executeStatement("DELETE FROM suppliers WHERE id = :id", [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    protected function mapToModel(array $row): SupplierModel
    {
        return new SupplierModel(
            id: (int)$row['id'],
            name: $row['name'],
            email: $row['email'] ?? null,
            phone: $row['phone'] ?? null,
            address: $row['address'] ?? null,
            is_active: (bool)($row['is_active'] ?? true),
            created_at: $row['created_at'] ?? null,
            updated_at: $row['updated_at'] ?? null,
            deleted_at: $row['deleted_at'] ?? null
        );
    }

    protected function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}

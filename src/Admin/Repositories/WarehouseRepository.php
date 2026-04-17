<?php
declare(strict_types=1);

namespace App\Admin\Repositories;

use PDO;
use App\Admin\Models\WarehouseModel;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

class WarehouseRepository extends BaseRepository
{
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM warehouses 
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
        $sql = "SELECT * FROM warehouses ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function getById(int $id): ?WarehouseModel
    {
        $sql = "SELECT * FROM warehouses 
                 WHERE id = :id AND is_active = TRUE AND deleted_at IS NULL";
        $row = $this->fetchOne($sql, [':id' => $id]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByIdAdmin(int $id): ?WarehouseModel
    {
        $row = $this->fetchOne("SELECT * FROM warehouses WHERE id = :id", [':id' => $id]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByName(string $name): ?WarehouseModel
    {
        $sql = "SELECT * FROM warehouses 
                 WHERE name = :name AND is_active = TRUE AND deleted_at IS NULL";
        $row = $this->fetchOne($sql, [':name' => $name]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM warehouses WHERE name = :name AND deleted_at IS NULL";
        $params = [':name' => $name];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        return (int)$this->fetchColumn($sql, $params) > 0;
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM warehouses 
                 WHERE (name ILIKE :query OR address ILIKE :query) 
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
        $sql = "SELECT COUNT(*) FROM warehouses 
                 WHERE is_active = TRUE AND deleted_at IS NULL";
        return (int)$this->fetchColumn($sql);
    }

    public function countAll(): int
    {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM warehouses WHERE deleted_at IS NULL");
    }

    public function create(array $data): WarehouseModel
    {
        $sql = "INSERT INTO warehouses (name, address, phone, image_url, is_active) 
                 VALUES (:name, :address, :phone, :image_url, :is_active) 
                 RETURNING *";
        
        $stmt = $this->executeStatement($sql, [
            ':name' => $data['name'],
            ':address' => $data['address'] ?? null,
            ':phone'   => $data['phone'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':is_active' => $data['is_active'] ?? true
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DatabaseException('Failed to create warehouse');
        }
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?WarehouseModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['name', 'address', 'phone', 'image_url', 'is_active'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) {
            return null;
        }

        $sql = "UPDATE warehouses SET " . implode(', ', $sets) . " 
                WHERE id = :id RETURNING *";
        
        $stmt = $this->executeStatement($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function partialUpdate(int $id, array $data): ?WarehouseModel
    {
        return $this->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE warehouses SET is_active = FALSE, deleted_at = NOW() 
                 WHERE id = :id AND is_active = TRUE";
        $stmt = $this->executeStatement($sql, [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $sql = "UPDATE warehouses SET is_active = TRUE, deleted_at = NULL 
                 WHERE id = :id AND is_active = FALSE";
        $stmt = $this->executeStatement($sql, [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->executeStatement("DELETE FROM warehouses WHERE id = :id", [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    protected function mapToModel(array $row): WarehouseModel
    {
        return new WarehouseModel(
            id: (int)$row['id'],
            name: $row['name'],
            address: $row['address'] ?? null,
            phone: $row['phone'] ?? null,
            image_url: $row['image_url'] ?? null,
            is_active: (bool)$row['is_active'],
            created_at: $row['created_at'],
            updated_at: $row['updated_at'],
            deleted_at: $row['deleted_at']
        );
    }

    protected function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}

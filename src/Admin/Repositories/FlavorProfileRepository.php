<?php
declare(strict_types=1);

namespace App\Admin\Repositories;

use PDO;
use App\Admin\Models\FlavorProfileModel;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

class FlavorProfileRepository extends BaseRepository
{
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->getAllPaginated($limit, $offset);
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0): array
    {
        $sql = "
            SELECT fp.*, 
                   p.name as product_name, 
                   p.slug as product_slug, 
                   p.image_url as product_image_url
            FROM flavor_profiles fp
            JOIN products p ON fp.product_id = p.id
            ORDER BY fp.product_id ASC 
            LIMIT :limit OFFSET :offset
        ";
        $rows = $this->fetchAll($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        
        // Map postgres array manually for list view if needed, or rely on frontend parsing
        return array_map(function($row) {
            $row['tags'] = $this->parsePostgresArray($row['tags'] ?? null);
            return $row;
        }, $rows);
    }

    public function getByProductId(int $productId): ?FlavorProfileModel
    {
        $row = $this->fetchOne("SELECT * FROM flavor_profiles WHERE product_id = :product_id", [':product_id' => $productId]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByProductIdEnriched(int $productId): ?array
    {
        $sql = "SELECT fp.*, 
                       p.name as product_name, 
                       p.slug as product_slug, 
                       p.image_url as product_image_url
                FROM flavor_profiles fp
                JOIN products p ON fp.product_id = p.id
                WHERE fp.product_id = :product_id";
        
        $row = $this->fetchOne($sql, [':product_id' => $productId]);
        
        if (!$row) return null;

        $row['tags'] = $this->parsePostgresArray($row['tags'] ?? null);
        return $row;
    }

    public function exists(int $productId): bool
    {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM flavor_profiles WHERE product_id = :id", [':id' => $productId]) > 0;
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT fp.*, p.name as product_name 
                 FROM flavor_profiles fp
                 JOIN products p ON fp.product_id = p.id
                 WHERE p.name ILIKE :query 
                 ORDER BY p.name LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':query' => "%$query%",
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        
        return array_map(function($row) {
            // Return raw array with product name for search results
            $row['tags'] = $this->parsePostgresArray($row['tags'] ?? null);
            return $row;
        }, $rows);
    }

    public function count(): int
    {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM flavor_profiles");
    }

    public function create(array $data): FlavorProfileModel
    {
        // Convert array to Postgres array string format "{tag1,tag2}"
        $tagsSql = isset($data['tags']) && is_array($data['tags']) 
            ? '{' . implode(',', array_map(fn($t) => '"' . str_replace('"', '\"', $t) . '"', $data['tags'])) . '}' 
            : '{}';

        $sql = "INSERT INTO flavor_profiles (product_id, sweetness, bitterness, strength, smokiness, fruitiness, spiciness, tags)
                VALUES (:product_id, :sweetness, :bitterness, :strength, :smokiness, :fruitiness, :spiciness, :tags)
                RETURNING *";
        
        $stmt = $this->executeStatement($sql, [
            ':product_id' => $data['product_id'],
            ':sweetness' => $data['sweetness'] ?? 5,
            ':bitterness' => $data['bitterness'] ?? 5,
            ':strength' => $data['strength'] ?? 5,
            ':smokiness' => $data['smokiness'] ?? 5,
            ':fruitiness' => $data['fruitiness'] ?? 5,
            ':spiciness' => $data['spiciness'] ?? 5,
            ':tags' => $tagsSql
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DatabaseException('Failed to create flavor profile');
        }
        return $this->mapToModel($row);
    }

    public function update(int $product_id, array $data): ?FlavorProfileModel
    {
        $sets = [];
        $params = [':product_id' => $product_id];

        foreach (['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            $sets[] = "tags = :tags";
            $params[':tags'] = '{' . implode(',', array_map(fn($t) => '"' . str_replace('"', '\"', $t) . '"', $data['tags'])) . '}';
        }

        if (empty($sets)) {
            return $this->getByProductId($product_id); // Return existing
        }

        $sql = "UPDATE flavor_profiles SET " . implode(', ', $sets) . " 
                WHERE product_id = :product_id RETURNING *";
        
        $stmt = $this->executeStatement($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $productId): bool
    {
        $stmt = $this->executeStatement("DELETE FROM flavor_profiles WHERE product_id = :id", [':id' => $productId]);
        return $stmt->rowCount() > 0;
    }

    protected function mapToModel(array $row): FlavorProfileModel
    {
        return new FlavorProfileModel(
            product_id: (int)$row['product_id'],
            sweetness: (int)$row['sweetness'],
            bitterness: (int)$row['bitterness'],
            strength: (int)$row['strength'],
            smokiness: (int)$row['smokiness'],
            fruitiness: (int)$row['fruitiness'],
            spiciness: (int)$row['spiciness'],
            tags: $this->parsePostgresArray($row['tags'] ?? null)
        );
    }

    protected function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }

    private function parsePostgresArray(?string $arrayStr): array
    {
        if (!$arrayStr || $arrayStr === '{}') {
            return [];
        }
        
        // Remove braces and split by comma
        $items = str_replace(['{', '}'], '', $arrayStr);
        $items = explode(',', $items);
        
        // Clean up quoted items
        return array_map(function($item) {
            $item = trim($item);
            // Remove surrounding quotes if present
            if (str_starts_with($item, '"') && str_ends_with($item, '"')) {
                $item = substr($item, 1, -1);
                // Unescape escaped quotes
                $item = str_replace('\"', '"', $item);
            }
            return $item;
        }, $items);
    }
}

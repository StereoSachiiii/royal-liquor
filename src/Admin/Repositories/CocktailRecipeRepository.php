<?php
declare(strict_types=1);

namespace App\Admin\Repositories;

use PDO;
use App\Admin\Models\CocktailRecipeModel;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

class CocktailRecipeRepository extends BaseRepository
{
    public function getAll(int $limit = 50, int $offset = 0, bool $includeDeleted = false): array
    {
        $sql = "SELECT * FROM cocktail_recipes";
        $params = [];
        
        if (!$includeDeleted) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $rows = $this->fetchAll($sql, $params);
        return $this->mapToModels($rows);
    }

    public function getById(int $id, bool $includeDeleted = false): ?CocktailRecipeModel
    {
        $sql = "SELECT * FROM cocktail_recipes WHERE id = :id";
        $params = [':id' => $id];
        
        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }

        $row = $this->fetchOne($sql, $params);
        return $row ? $this->mapToModel($row) : null;
    }

    public function create(array $data): CocktailRecipeModel
    {
        $sql = "INSERT INTO cocktail_recipes (name, description, instructions, image_url, difficulty, preparation_time, serves, is_active) 
                 VALUES (:name, :description, :instructions, :image_url, :difficulty, :preparation_time, :serves, :is_active) 
                 RETURNING *";
        
        $stmt = $this->executeStatement($sql, [
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':instructions' => $data['instructions'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':difficulty' => $data['difficulty'] ?? 'easy',
            ':preparation_time' => $data['preparation_time'] ?? null,
            ':serves' => $data['serves'] ?? 1,
            ':is_active' => $data['is_active'] ?? true
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DatabaseException('Failed to create cocktail recipe');
        }
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?CocktailRecipeModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['name', 'description', 'instructions', 'image_url', 'difficulty', 'preparation_time', 'serves', 'is_active'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) {
            return null;
        }

        $sql = "UPDATE cocktail_recipes SET " . implode(', ', $sets) . " 
                WHERE id = :id RETURNING *";
        
        $stmt = $this->executeStatement($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE cocktail_recipes SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->executeStatement($sql, [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM cocktail_recipes 
                 WHERE (name ILIKE :query OR description ILIKE :query) 
                 AND deleted_at IS NULL
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
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM cocktail_recipes WHERE deleted_at IS NULL");
    }

    protected function mapToModel(array $row): CocktailRecipeModel
    {
        return new CocktailRecipeModel(
            id: (int)$row['id'],
            name: $row['name'],
            description: $row['description'],
            instructions: $row['instructions'],
            image_url: $row['image_url'],
            difficulty: $row['difficulty'] ?? 'easy',
            preparation_time: isset($row['preparation_time']) ? (int)$row['preparation_time'] : null,
            serves: isset($row['serves']) ? (int)$row['serves'] : 1,
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

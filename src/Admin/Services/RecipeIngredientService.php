<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\RecipeIngredientRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\UnauthorizedException;
use App\Admin\Exceptions\DatabaseException;

use App\DTO\Requests\CreateRecipeIngredientRequest;
use App\DTO\Requests\UpdateRecipeIngredientRequest;
use App\DTO\DTOException;

class RecipeIngredientService
{
    public function __construct(
        private RecipeIngredientRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateRecipeIngredientRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $ingredient = $this->repo->create($dto->toArray());
        return $ingredient->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $ingredients = $this->repo->getAll($limit, $offset);
        return array_map(fn($i) => $i->toArray(), $ingredients);
    }

    public function searchByProduct(string $query, int $limit = 50, int $offset = 0): array
    {
        $ingredients = $this->repo->searchByProduct($query, $limit, $offset);
        return array_map(fn($i) => $i->toArray(), $ingredients);
    }

    public function getById(int $id): array
    {
        $ingredient = $this->repo->getById($id);
        if (!$ingredient) {
            throw new NotFoundException('Recipe ingredient not found');
        }
        return $ingredient->toArray();
    }

    public function getByRecipe(int $recipeId): array
    {
        $ingredients = $this->repo->getByRecipe($recipeId);
        return array_map(fn($i) => $i->toArray(), $ingredients);
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateRecipeIngredientRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Recipe ingredient not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Recipe ingredient not found');
        }
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function bulkCreate(array $ingredients): array
    {
        $dtos = [];
        $errors = [];
        
        foreach ($ingredients as $index => $itemData) {
            try {
                $dtos[] = CreateRecipeIngredientRequest::fromArray($itemData);
            } catch (DTOException $e) {
                // Return accumulated errors per index for the bulk request
                $errors["item_$index"] = $e->getErrors();
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Bulk validation failed', $errors);
        }
        
        $validData = array_map(fn($dto) => $dto->toArray(), $dtos);
        $results = $this->repo->bulkCreate($validData);
        
        return array_map(fn($r) => $r->toArray(), $results);
    }

    public function deleteByRecipe(int $recipeId): void
    {
        $this->repo->deleteByRecipe($recipeId);
    }
}

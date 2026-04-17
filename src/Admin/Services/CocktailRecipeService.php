<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\CocktailRecipeRepository;
use App\Admin\Repositories\RecipeIngredientRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

use App\DTO\Requests\CreateCocktailRecipeRequest;
use App\DTO\Requests\UpdateCocktailRecipeRequest;
use App\DTO\DTOException;

class CocktailRecipeService
{
    public function __construct(
        private CocktailRecipeRepository $repo,
        private RecipeIngredientRepository $ingredientRepo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateCocktailRecipeRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $recipe = $this->repo->create($dto->toArray());
        return $recipe->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $recipes = $this->repo->getAll($limit, $offset);
        
        $data = array_map(function($r) {
            $recipeData = $r->toArray();
            $ingredients = $this->ingredientRepo->getByRecipe($r->id);
            $recipeData['ingredients'] = array_map(fn($i) => $i->toArray(), $ingredients);
            return $recipeData;
        }, $recipes);

        return [
            'items' => $data,
            'total' => $this->repo->count()
        ];
    }

    public function getById(int $id): array
    {
        $recipe = $this->repo->getById($id);
        if (!$recipe) {
            throw new NotFoundException('Cocktail recipe not found');
        }
        
        $data = $recipe->toArray();
        $ingredients = $this->ingredientRepo->getByRecipe($id);
        $data['ingredients'] = array_map(fn($i) => $i->toArray(), $ingredients);
        
        return $data;
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateCocktailRecipeRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Cocktail recipe not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Cocktail recipe not found');
        }
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $recipes = $this->repo->search($query, $limit, $offset);
        return array_map(fn($r) => $r->toArray(), $recipes);
    }

    public function count(): int
    {
        return $this->repo->count();
    }
}

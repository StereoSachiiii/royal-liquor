<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a recipe ingredient.
 * Replaces RecipeIngredientValidator::validateCreate().
 */
class CreateRecipeIngredientRequest extends BaseDTO
{
    public int $recipe_id;
    public int $product_id;
    public float $quantity;
    public string $unit;

    public ?bool $is_optional = false;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('recipe_id', 'required|integer|min:1')
            ->field('product_id', 'required|integer|min:1')
            ->field('quantity', 'required|min:0.01|max:10000') // our validator handles min/max generically for floats
            ->field('unit', 'required|string|min_length:1|max_length:50');

        if (isset($data['is_optional'])) {
            $validator->field('is_optional', 'boolean');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        // Properly cast float
        if (isset($data['quantity'])) {
            $data['quantity'] = (float)$data['quantity'];
        }

        return parent::fromArray($data);
    }
}

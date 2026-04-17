<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a recipe ingredient.
 * Replaces RecipeIngredientValidator::validateUpdate().
 */
class UpdateRecipeIngredientRequest extends BaseDTO
{
    public ?float $quantity = null;
    public ?string $unit = null;
    public ?bool $is_optional = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['quantity'])) {
            $validator->field('quantity', 'min:0.01|max:10000');
            $data['quantity'] = (float)$data['quantity'];
        }
        if (isset($data['unit'])) {
            $validator->field('unit', 'string|min_length:1|max_length:50');
        }
        if (isset($data['is_optional'])) {
            $validator->field('is_optional', 'boolean');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }

    public function toChangeset(): array
    {
        return array_filter($this->toArray(), fn($v) => $v !== null);
    }
}

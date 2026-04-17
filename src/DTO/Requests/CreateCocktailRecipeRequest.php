<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a cocktail recipe.
 * Replaces CocktailRecipeValidator::validateCreate().
 */
class CreateCocktailRecipeRequest extends BaseDTO
{
    public string $name;
    public string $instructions;

    public ?string $description = null;
    public ?string $image_url = null;
    public ?string $difficulty = 'medium';
    public ?int $preparation_time = null;
    public ?int $serves = 1;
    public ?bool $is_active = true;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('name', 'required|string|min_length:2|max_length:200')
            ->field('instructions', 'required|string|min_length:5');

        if (isset($data['description'])) {
            $validator->field('description', 'max_length:2000');
        }
        if (isset($data['image_url'])) {
            $validator->field('image_url', 'url');
        }
        if (isset($data['difficulty'])) {
            $validator->field('difficulty', 'in:easy,medium,hard');
        }
        if (isset($data['preparation_time'])) {
            $validator->field('preparation_time', 'integer|min:0');
        }
        if (isset($data['serves'])) {
            $validator->field('serves', 'integer|min:1');
        }
        if (isset($data['is_active'])) {
            $validator->field('is_active', 'boolean');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

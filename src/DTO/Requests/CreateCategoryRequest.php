<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a category.
 * Replaces CategoryValidator::validateCreate().
 * Model fields: id, name, slug, description, image_url, is_active, created_at, updated_at, deleted_at
 */
class CreateCategoryRequest extends BaseDTO
{
    // Required
    public string $name;

    // Optional
    public ?string $slug        = null;
    public ?string $description = null;
    public ?string $image_url   = null;
    public ?bool   $is_active   = true;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('name',        'required|min_length:2|max_length:100')
            ->field('slug',        'max_length:120')
            ->field('description', 'max_length:1000')
            ->field('image_url',   'max_length:500')
            ->field('is_active',   'boolean');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

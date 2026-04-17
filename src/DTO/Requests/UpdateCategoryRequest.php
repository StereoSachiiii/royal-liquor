<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a category.
 * All fields optional — only present fields are validated (PATCH semantics).
 * Replaces CategoryValidator::validateUpdate().
 */
class UpdateCategoryRequest extends BaseDTO
{
    public ?string $name        = null;
    public ?string $slug        = null;
    public ?string $description = null;
    public ?string $image_url   = null;
    public ?bool   $is_active   = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['name'])) {
            $validator->field('name', 'min_length:2|max_length:100');
        }
        if (isset($data['slug'])) {
            $validator->field('slug', 'max_length:120');
        }
        if (isset($data['description'])) {
            $validator->field('description', 'max_length:1000');
        }
        if (isset($data['image_url'])) {
            $validator->field('image_url', 'max_length:500');
        }
        if (isset($data['is_active'])) {
            $validator->field('is_active', 'boolean');
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

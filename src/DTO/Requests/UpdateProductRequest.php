<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a product.
 * All fields optional — only present fields are validated (PATCH semantics).
 * Replaces ProductValidator::validateUpdate().
 */
class UpdateProductRequest extends BaseDTO
{
    // All nullable — only fields sent by the client are validated
    public ?string $name        = null;
    public ?string $slug        = null;
    public ?string $description = null;
    public ?int    $price_cents = null;
    public ?string $image_url   = null;
    public ?int    $category_id = null;
    public ?int    $supplier_id = null;
    public ?bool   $is_active   = null;

    /**
     * @throws DTOException on validation failure
     */
    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['name'])) {
            $validator->field('name', 'min_length:2|max_length:200');
        }
        if (isset($data['slug'])) {
            $validator->field('slug', 'max_length:220');
        }
        if (isset($data['price_cents'])) {
            $validator->field('price_cents', 'integer|min:1');
        }
        if (isset($data['image_url'])) {
            $validator->field('image_url', 'max_length:500');
        }
        if (isset($data['category_id'])) {
            $validator->field('category_id', 'integer|min:1');
        }
        if (isset($data['supplier_id'])) {
            $validator->field('supplier_id', 'integer');
        }
        if (isset($data['is_active'])) {
            $validator->field('is_active', 'boolean');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }

    /**
     * Returns only the fields that were actually sent (non-null).
     * Used by the service to build the SQL update payload.
     */
    public function toChangeset(): array
    {
        return array_filter(
            $this->toArray(),
            fn($v) => $v !== null
        );
    }
}

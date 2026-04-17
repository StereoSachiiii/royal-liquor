<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a product.
 * Replaces ProductValidator::validateCreate().
 */
class CreateProductRequest extends BaseDTO
{
    // Required
    public string $name;
    public int $price_cents;
    public int $category_id;

    // Optional
    public ?string $slug        = null;
    public ?string $description = null;
    public ?string $image_url   = null;
    public ?int    $supplier_id = null;
    public ?bool   $is_active   = true;

    /**
     * Build and validate from raw request body.
     *
     * @throws DTOException on validation failure
     */
    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('name',        'required|min_length:2|max_length:200')
            ->field('price_cents', 'required|integer|min:1')
            ->field('category_id', 'required|integer|min:1')
            ->field('slug',        'max_length:220')
            ->field('image_url',   'max_length:500')
            ->field('supplier_id', 'integer|min:1')
            ->field('is_active',   'boolean');

        if (!$validator->validate()) {
            throw new DTOException(
                'Validation failed',
                $validator->errors()
            );
        }

        return parent::fromArray($data);
    }
}

<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a stock entry.
 * Replaces legacy StockValidator::validateCreate().
 */
class CreateStockRequest extends BaseDTO
{
    // Required
    public int $product_id;
    public int $warehouse_id;

    // Optional
    public int $quantity = 0;
    public int $reserved = 0;

    /**
     * Build and validate from raw request body.
     *
     * @throws DTOException on validation failure
     */
    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('product_id',   'required|integer|min:1')
            ->field('warehouse_id', 'required|integer|min:1')
            ->field('quantity',     'integer|min:0')
            ->field('reserved',     'integer|min:0');

        if (!$validator->validate()) {
            throw new DTOException(
                'Stock validation failed',
                $validator->errors()
            );
        }

        return parent::fromArray($data);
    }
}

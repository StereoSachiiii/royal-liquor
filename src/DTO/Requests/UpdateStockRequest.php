<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a stock entry.
 * Replaces legacy StockValidator::validateUpdate().
 */
class UpdateStockRequest extends BaseDTO
{
    public ?int $quantity = null;
    public ?int $reserved = null;

    /**
     * Build and validate from raw request body.
     *
     * @throws DTOException on validation failure
     */
    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('quantity', 'integer|min:0')
            ->field('reserved', 'integer|min:0');

        if (!$validator->validate()) {
            throw new DTOException(
                'Stock update validation failed',
                $validator->errors()
            );
        }

        return parent::fromArray($data);
    }
}

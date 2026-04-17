<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a cart item.
 * Replaces CartItemValidator::validateUpdate().
 */
class UpdateCartItemRequest extends BaseDTO
{
    public ?int $quantity           = null;
    public ?int $price_at_add_cents = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['quantity'])) {
            $validator->field('quantity', 'integer|min:1');
        }
        if (isset($data['price_at_add_cents'])) {
            $validator->field('price_at_add_cents', 'integer|min:1');
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

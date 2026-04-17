<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a cart item.
 * Replaces CartItemValidator::validateCreate().
 * Model: id, cart_id, product_id, quantity, price_at_add_cents, created_at, updated_at
 */
class CreateCartItemRequest extends BaseDTO
{
    public int $cart_id;
    public int $product_id;
    public int $quantity;
    public int $price_at_add_cents;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('cart_id',            'required|integer|min:1')
            ->field('product_id',         'required|integer|min:1')
            ->field('quantity',           'required|integer|min:1')
            ->field('price_at_add_cents', 'required|integer|min:1');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

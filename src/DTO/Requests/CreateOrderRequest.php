<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating an order.
 * Replaces OrderValidator::validateCreate().
 * Model: id, order_number, cart_id, user_id, status, total_cents,
 *        shipping_address_id, billing_address_id, notes, created_at, ...
 */
class CreateOrderRequest extends BaseDTO
{
    public int  $cart_id;
    public int  $total_cents;

    public ?int    $user_id             = null;
    public ?int    $shipping_address_id = null;
    public ?int    $billing_address_id  = null;
    public ?string $notes               = null;
    public ?array  $items               = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('cart_id',             'required|integer|min:1')
            ->field('total_cents',         'required|integer|min:1')
            ->field('user_id',             'integer|min:1')
            ->field('shipping_address_id', 'integer|min:1')
            ->field('billing_address_id',  'integer|min:1');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

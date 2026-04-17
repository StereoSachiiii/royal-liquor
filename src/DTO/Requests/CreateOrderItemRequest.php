<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating an order item.
 * Replaces OrderItemValidator::validateCreate().
 */
class CreateOrderItemRequest extends BaseDTO
{
    public int $order_id;
    public int $product_id;
    public string $product_name;
    public int $price_cents;
    public int $quantity;

    public ?string $product_image_url = null;
    public ?int $warehouse_id = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('order_id', 'required|integer|min:1')
            ->field('product_id', 'required|integer|min:1')
            ->field('product_name', 'required|string|max_length:200')
            ->field('price_cents', 'required|integer|min:0')
            ->field('quantity', 'required|integer|min:1|max:10000');

        if (isset($data['product_image_url'])) {
            $validator->field('product_image_url', 'max_length:500');
        }
        if (isset($data['warehouse_id'])) {
            $validator->field('warehouse_id', 'integer|min:1');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

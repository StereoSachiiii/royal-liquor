<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating an order.
 * Replaces OrderValidator::validateUpdate().
 */
class UpdateOrderRequest extends BaseDTO
{
    public ?string $status              = null;
    public ?string $notes               = null;
    public ?int    $shipping_address_id = null;
    public ?int    $billing_address_id  = null;

    private const VALID_STATUSES = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'failed'];

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['status'])) {
            $validator->field('status', 'in:' . implode(',', self::VALID_STATUSES));
        }
        if (isset($data['shipping_address_id'])) {
            $validator->field('shipping_address_id', 'integer|min:1');
        }
        if (isset($data['billing_address_id'])) {
            $validator->field('billing_address_id', 'integer|min:1');
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

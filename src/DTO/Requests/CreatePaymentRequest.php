<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a payment.
 * Replaces PaymentValidator::validateCreate().
 * Model: id, order_id, amount_cents, currency, gateway, gateway_order_id,
 *        transaction_id, status, payload (array), created_at
 */
class CreatePaymentRequest extends BaseDTO
{
    public int    $order_id;
    public int    $amount_cents;
    public string $gateway;

    public ?string $currency         = 'LKR';
    public ?string $gateway_order_id = null;
    public ?string $transaction_id   = null;
    public ?string $status           = 'pending';
    public ?array  $payload          = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('order_id',     'required|integer|min:1')
            ->field('amount_cents', 'required|integer|min:1')
            ->field('gateway',      'required|max_length:50')
            ->field('currency',     'min_length:3|max_length:3')
            ->field('status',       'in:pending,captured,failed,refunded,voided');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

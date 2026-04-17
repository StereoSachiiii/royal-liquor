<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a payment.
 * Replaces PaymentValidator::validateUpdate().
 */
class UpdatePaymentRequest extends BaseDTO
{
    public ?string $status         = null;
    public ?string $transaction_id = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['status'])) {
            $validator->field('status', 'in:pending,captured,failed,refunded,voided');
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

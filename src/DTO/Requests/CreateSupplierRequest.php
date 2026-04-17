<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a supplier.
 * Replaces SupplierValidator::validateCreate().
 */
class CreateSupplierRequest extends BaseDTO
{
    public string $name;

    public ?string $email     = null;
    public ?string $phone     = null;
    public ?string $address   = null;
    public ?bool   $is_active = true;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator->field('name', 'required|string|min_length:2|max_length:100');

        if (isset($data['email'])) {
            $validator->field('email', 'email');
        }
        if (isset($data['phone'])) {
            $validator->field('phone', 'regex:/^\+?[0-9]{8,15}$/');
        }
        if (isset($data['address'])) {
            $validator->field('address', 'string');
        }
        if (isset($data['is_active'])) {
            $validator->field('is_active', 'boolean');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

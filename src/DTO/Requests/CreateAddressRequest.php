<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating an address.
 * Replaces AddressValidator::validateCreate().
 */
class CreateAddressRequest extends BaseDTO
{
    public int    $user_id;
    public string $address_line1;
    public string $city;
    public string $postal_code;

    public ?string $address_line2  = null;
    public ?string $state          = null;
    public ?string $country        = null;
    public ?string $recipient_name = null;
    public ?string $phone          = null;
    public ?string $address_type   = 'both';
    public ?bool   $is_default     = false;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('user_id',       'required|integer|min:1')
            ->field('address_line1', 'required|string|max_length:255')
            ->field('address_line2', 'max_length:255')
            ->field('city',          'required|string|max_length:100')
            ->field('state',         'max_length:100')
            ->field('postal_code',   'required|string|max_length:20')
            ->field('country',       'max_length:100')
            ->field('recipient_name','max_length:100')
            ->field('address_type',  'in:billing,shipping,both')
            ->field('is_default',    'boolean');

        if (error_reporting() === 0) {
            // ignore
        }

        if (isset($data['phone'])) {
            $validator->field('phone', 'regex:/^\+?[0-9]{8,15}$/');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

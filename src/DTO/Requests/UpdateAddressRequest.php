<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating an address.
 * Replaces AddressValidator::validateUpdate().
 */
class UpdateAddressRequest extends BaseDTO
{
    public ?string $address_line1  = null;
    public ?string $address_line2  = null;
    public ?string $city           = null;
    public ?string $state          = null;
    public ?string $postal_code    = null;
    public ?string $country        = null;
    public ?string $recipient_name = null;
    public ?string $phone          = null;
    public ?string $address_type   = null;
    public ?bool   $is_default     = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['address_line1']))  $validator->field('address_line1',  'string|min_length:1|max_length:255');
        if (isset($data['address_line2']))  $validator->field('address_line2',  'max_length:255');
        if (isset($data['city']))           $validator->field('city',           'string|min_length:1|max_length:100');
        if (isset($data['state']))          $validator->field('state',          'max_length:100');
        if (isset($data['postal_code']))    $validator->field('postal_code',    'string|min_length:1|max_length:20');
        if (isset($data['country']))        $validator->field('country',        'max_length:100');
        if (isset($data['recipient_name'])) $validator->field('recipient_name', 'max_length:100');
        if (isset($data['address_type']))   $validator->field('address_type',   'in:billing,shipping,both');
        if (isset($data['is_default']))     $validator->field('is_default',     'boolean');
        if (isset($data['phone']))          $validator->field('phone',          'regex:/^\+?[0-9]{8,15}$/');

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

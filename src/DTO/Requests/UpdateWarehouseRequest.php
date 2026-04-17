<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a warehouse.
 * Replaces WarehouseValidator::validateUpdate().
 */
class UpdateWarehouseRequest extends BaseDTO
{
    public ?string $name      = null;
    public ?string $address   = null;
    public ?string $phone     = null;
    public ?string $image_url = null;
    public ?bool   $is_active = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['name'])) {
            $validator->field('name', 'string|min_length:2|max_length:100');
        }
        if (isset($data['address'])) {
            $validator->field('address', 'string');
        }
        if (isset($data['phone'])) {
            $validator->field('phone', 'regex:/^\+?[0-9]{8,15}$/');
        }
        if (isset($data['image_url'])) {
            $validator->field('image_url', 'max_length:500');
        }
        if (isset($data['is_active'])) {
            $validator->field('is_active', 'boolean');
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

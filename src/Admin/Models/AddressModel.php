<?php
declare(strict_types=1);

namespace App\Admin\Models;

class AddressModel
{
    public function __construct(
        private ?int $id = null,
        private ?int $user_id = null,
        private ?string $address_type = 'both',
        private ?string $recipient_name = null,
        private ?string $phone = null,
        private ?string $address_line1 = null,
        private ?string $address_line2 = null,
        private ?string $city = null,
        private ?string $state = null,
        private ?string $postal_code = null,
        private ?string $country = 'Sri Lanka',
        private bool $is_default = false,
        private ?string $created_at = null,
        private ?string $updated_at = null,
        private ?string $deleted_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->user_id; }
    public function getAddressType(): ?string { return $this->address_type; }
    public function getRecipientName(): ?string { return $this->recipient_name; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAddressLine1(): ?string { return $this->address_line1; }
    public function getAddressLine2(): ?string { return $this->address_line2; }
    public function getCity(): ?string { return $this->city; }
    public function getState(): ?string { return $this->state; }
    public function getPostalCode(): ?string { return $this->postal_code; }
    public function getCountry(): ?string { return $this->country; }
    public function isDefault(): bool { return $this->is_default; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getDeletedAt(): ?string { return $this->deleted_at; }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'address_type'    => $this->address_type,
            'recipient_name'  => $this->recipient_name,
            'phone'           => $this->phone,
            'address_line1'   => $this->address_line1,
            'address_line2'   => $this->address_line2,
            'city'            => $this->city,
            'state'           => $this->state,
            'postal_code'     => $this->postal_code,
            'country'         => $this->country,
            'is_default'      => $this->is_default,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'deleted_at'      => $this->deleted_at,
        ];
    }
}

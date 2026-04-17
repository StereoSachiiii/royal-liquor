<?php
declare(strict_types=1);

namespace App\Admin\Models;

class SupplierModel
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $email = null,
        private ?string $phone = null,
        private ?string $address = null,
        private bool $is_active = true,
        private ?string $created_at = null,
        private ?string $updated_at = null,
        private ?string $deleted_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAddress(): ?string { return $this->address; }
    public function isActive(): bool { return $this->is_active; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getDeletedAt(): ?string { return $this->deleted_at; }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'address'    => $this->address,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

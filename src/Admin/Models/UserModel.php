<?php
declare(strict_types=1);

namespace App\Admin\Models;

class UserModel
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $email = null,
        private ?string $phone = null,
        private ?string $passwordHash = null,
        private ?string $profileImageUrl = null,
        private bool $isActive = true,
        private bool $isAdmin = false,
        private bool $isAnonymized = false,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
        private ?string $deletedAt = null,
        private ?string $anonymizedAt = null,
        private ?string $lastLoginAt = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function getProfileImageUrl(): ?string { return $this->profileImageUrl; }
    public function isActive(): bool { return $this->isActive; }
    public function isAdmin(): bool { return $this->isAdmin; }
    public function isAnonymized(): bool { return $this->isAnonymized; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }
    public function getAnonymizedAt(): ?string { return $this->anonymizedAt; }
    public function getLastLoginAt(): ?string { return $this->lastLoginAt; }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'profile_image_url' => $this->profileImageUrl,
            'is_active'         => $this->isActive,
            'is_admin'          => $this->isAdmin,
            'is_anonymized'     => $this->isAnonymized,
            'created_at'        => $this->createdAt,
            'updated_at'        => $this->updatedAt,
            'deleted_at'        => $this->deletedAt,
            'anonymized_at'     => $this->anonymizedAt,
            'last_login_at'     => $this->lastLoginAt,
        ];
    }
}

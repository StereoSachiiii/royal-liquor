<?php
declare(strict_types=1);

namespace App\Admin\Models;

class CategoryModel
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $slug = null,
        private ?string $description = null,
        private ?string $image_url = null,
        private bool $is_active = true,
        private ?string $created_at = null,
        private ?string $updated_at = null,
        private ?string $deleted_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function getSlug(): ?string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getImageUrl(): ?string { return $this->image_url; }
    public function isActive(): bool { return $this->is_active; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getDeletedAt(): ?string { return $this->deleted_at; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'image_url'   => $this->image_url,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
        ];
    }
}

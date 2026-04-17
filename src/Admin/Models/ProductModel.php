<?php
declare(strict_types=1);

namespace App\Admin\Models;

class ProductModel
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $slug = null,
        private ?string $description = null,
        private ?int $price_cents = null,
        private ?string $image_url = null,
        private ?int $category_id = null,
        private ?int $supplier_id = null,
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
    public function getPriceCents(): ?int { return $this->price_cents; }
    public function getImageUrl(): ?string { return $this->image_url; }
    public function getCategoryId(): ?int { return $this->category_id; }
    public function getSupplierId(): ?int { return $this->supplier_id; }
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
            'price_cents' => $this->price_cents,
            'image_url'   => $this->image_url,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
        ];
    }
}

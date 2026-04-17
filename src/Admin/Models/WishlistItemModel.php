<?php
declare(strict_types=1);

namespace App\Admin\Models;

class WishlistItemModel
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $product_id,
        public string $created_at,
        // Joined details
        public ?string $product_name = null,
        public ?string $product_slug = null,
        public ?int $price_cents = null,
        public ?string $image_url = null,
        public ?bool $is_active = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'product_name' => $this->product_name,
            'product_slug' => $this->product_slug,
            'price_cents' => $this->price_cents,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active
        ];
    }
}

<?php
declare(strict_types=1);

namespace App\Admin\Models;

class OrderItemModel
{
    public function __construct(
        public ?int $id = null,
        public ?int $order_id = null,
        public ?int $product_id = null,
        public ?string $product_name = null,
        public ?string $product_image_url = null,
        public int $price_cents = 0,
        public int $quantity = 0,
        public ?string $created_at = null,
        public ?int $warehouse_id = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getOrderId(): ?int { return $this->order_id; }
    public function getProductId(): ?int { return $this->product_id; }
    public function getProductName(): ?string { return $this->product_name; }
    public function getProductImageUrl(): ?string { return $this->product_image_url; }
    public function getPriceCents(): int { return $this->price_cents; }
    public function getQuantity(): int { return $this->quantity; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'order_id'             => $this->order_id,
            'product_id'           => $this->product_id,
            'product_name'         => $this->product_name,
            'product_image_url'    => $this->product_image_url,
            'price_cents'          => $this->price_cents,
            'quantity'             => $this->quantity,
            'warehouse_id'         => $this->warehouse_id,
            'created_at'           => $this->created_at,
        ];
    }
}

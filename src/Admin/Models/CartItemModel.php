<?php
declare(strict_types=1);

namespace App\Admin\Models;

class CartItemModel
{
    public function __construct(
        private ?int $id = null,
        private ?int $cart_id = null,
        private ?int $product_id = null,
        private int $quantity = 0,
        private int $price_at_add_cents = 0,
        private ?string $created_at = null,
        private ?string $updated_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCartId(): ?int { return $this->cart_id; }
    public function getProductId(): ?int { return $this->product_id; }
    public function getQuantity(): int { return $this->quantity; }
    public function getPriceAtAddCents(): int { return $this->price_at_add_cents; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'cart_id'             => $this->cart_id,
            'product_id'          => $this->product_id,
            'quantity'            => $this->quantity,
            'price_at_add_cents'  => $this->price_at_add_cents,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}

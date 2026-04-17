<?php
declare(strict_types=1);

namespace App\Admin\Models;

class OrderModel
{
    public function __construct(
        private ?int $id = null,
        private ?string $order_number = null,
        private ?int $cart_id = null,
        public ?int $user_id = null,
        private ?string $status = 'pending',
        private int $total_cents = 0,
        private ?int $shipping_address_id = null,
        private ?int $billing_address_id = null,
        private ?string $notes = null,
        private ?string $created_at = null,
        private ?string $updated_at = null,
        private ?string $paid_at = null,
        private ?string $shipped_at = null,
        private ?string $delivered_at = null,
        private ?string $cancelled_at = null,
        public ?int $item_count = null
    ) {}


    // Getters
    public function getId(): ?int { return $this->id; }
    public function getOrderNumber(): ?string { return $this->order_number; }
    public function getCartId(): ?int { return $this->cart_id; }
    public function getUserId(): ?int { return $this->user_id; }
    public function getStatus(): ?string { return $this->status; }
    public function getTotalCents(): int { return $this->total_cents; }
    public function getShippingAddressId(): ?int { return $this->shipping_address_id; }
    public function getBillingAddressId(): ?int { return $this->billing_address_id; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getPaidAt(): ?string { return $this->paid_at; }
    public function getShippedAt(): ?string { return $this->shipped_at; }
    public function getDeliveredAt(): ?string { return $this->delivered_at; }
    public function getCancelledAt(): ?string { return $this->cancelled_at; }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'order_number'         => $this->order_number,
            'cart_id'              => $this->cart_id,
            'user_id'              => $this->user_id,
            'status'               => $this->status,
            'total_cents'          => $this->total_cents,
            'shipping_address_id'  => $this->shipping_address_id,
            'billing_address_id'   => $this->billing_address_id,
            'notes'                => $this->notes,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
            'paid_at'              => $this->paid_at,
            'shipped_at'           => $this->shipped_at,
            'delivered_at'         => $this->delivered_at,
            'cancelled_at'         => $this->cancelled_at,
            'item_count'           => $this->item_count,
        ];
    }
}

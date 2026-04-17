<?php
declare(strict_types=1);

namespace App\Admin\Models;

class StockModel
{
    public function __construct(
        public ?int $id = null,
        public ?int $product_id = null,
        public ?int $warehouse_id = null,
        public int $quantity = 0,
        public int $reserved = 0,
        public ?string $created_at = null,
        public ?string $updated_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getProductId(): ?int { return $this->product_id; }
    public function getWarehouseId(): ?int { return $this->warehouse_id; }
    public function getQuantity(): int { return $this->quantity; }
    public function getReserved(): int { return $this->reserved; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'quantity'     => $this->quantity,
            'reserved'     => $this->reserved,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}

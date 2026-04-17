<?php
declare(strict_types=1);

namespace App\Admin\Models;

class CartModel
{
    public function __construct(
        private ?int $id = null,
        private ?int $user_id = null,
        private ?string $session_id = null,
        private ?string $status = 'active',
        private int $total_cents = 0,
        private int $item_count = 0,
        private ?string $created_at = null,
        private ?string $updated_at = null,
        private ?string $converted_at = null,
        private ?string $abandoned_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->user_id; }
    public function getSessionId(): ?string { return $this->session_id; }
    public function getStatus(): ?string { return $this->status; }
    public function getTotalCents(): int { return $this->total_cents; }
    public function getItemCount(): int { return $this->item_count; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getConvertedAt(): ?string { return $this->converted_at; }
    public function getAbandonedAt(): ?string { return $this->abandoned_at; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'session_id'    => $this->session_id,
            'status'        => $this->status,
            'total_cents'   => $this->total_cents,
            'item_count'    => $this->item_count,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'converted_at'  => $this->converted_at,
            'abandoned_at'  => $this->abandoned_at,
        ];
    }
}

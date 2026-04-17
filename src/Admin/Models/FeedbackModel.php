<?php
declare(strict_types=1);

namespace App\Admin\Models;

class FeedbackModel {
    public int $id;
    public int $userId;
    public int $productId;
    public int $rating;
    public ?string $comment;
    public bool $isVerifiedPurchase;
    public bool $isActive;
    public string $createdAt;
    public string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $userId,
        int $productId,
        int $rating,
        ?string $comment,
        bool $isVerifiedPurchase,
        bool $isActive,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->productId = $productId;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->isVerifiedPurchase = $isVerifiedPurchase;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    public function toArray(): array {
        return get_object_vars($this);
    }
}
?>

<?php
declare(strict_types=1);

namespace App\DTO\Requests;

class CreateWishlistItemRequest
{
    public int $product_id;

    public function __construct(array $data)
    {
        $this->product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    }
}

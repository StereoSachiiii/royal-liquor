<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\WishlistRepository;
use App\Admin\Repositories\ProductRepository;
use App\DTO\Requests\CreateWishlistItemRequest;
use App\Validation\Validator;

class WishlistService
{
    private WishlistRepository $wishlistRepository;
    private ProductRepository $productRepository;

    public function __construct(
        WishlistRepository $wishlistRepository,
        ProductRepository $productRepository
    ) {
        $this->wishlistRepository = $wishlistRepository;
        $this->productRepository = $productRepository;
    }

    public function getWishlist(int $userId): array
    {
        $items = $this->wishlistRepository->getByUserId($userId);
        return array_map(fn($item) => $item->toArray(), $items);
    }

    public function addItem(int $userId, CreateWishlistItemRequest $request): array
    {
        // Validate product exists natively
        $product = $this->productRepository->getById($request->product_id);
        if (!$product) {
            throw new \Exception("Product not found.", 404);
        }

        $result = $this->wishlistRepository->addItem($userId, $request->product_id);
        
        return [
            'success' => true,
            'message' => $result ? 'Added to wishlist' : 'Already in wishlist',
            'item' => $result ? $result->toArray() : null
        ];
    }

    public function removeItem(int $userId, int $productId): array
    {
        $removed = $this->wishlistRepository->removeItem($userId, $productId);
        
        return [
            'success' => $removed,
            'message' => $removed ? 'Removed from wishlist' : 'Item not found in wishlist'
        ];
    }

    public function sync(int $userId, array $productIds): array
    {
        // Filter out bad inputs
        $validIds = array_filter(array_map('intval', $productIds), fn($id) => $id > 0);
        
        $mergedList = $this->wishlistRepository->syncBulk($userId, $validIds);
        return array_map(fn($item) => $item->toArray(), $mergedList);
    }
}

<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\WishlistService;
use App\DTO\Requests\CreateWishlistItemRequest;
use App\Core\Session;

class WishlistController extends BaseController
{
    private WishlistService $service;

    public function __construct(WishlistService $service)
    {
        $this->service = $service;
    }

    public function getMine(): void
    {
        $userId = Session::getInstance()->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $items = $this->service->getWishlist($userId);
            $this->jsonResponse(['success' => true, 'data' => $items]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function add(): void
    {
        $userId = Session::getInstance()->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $data = $this->getJsonInput();
            $request = new CreateWishlistItemRequest($data);
            
            if ($request->product_id <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid product id'], 400);
                return;
            }

            $result = $this->service->addItem($userId, $request);
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function remove(int $productId): void
    {
        $userId = Session::getInstance()->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $result = $this->service->removeItem($userId, $productId);
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function syncBulk(): void
    {
        $userId = Session::getInstance()->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $data = $this->getJsonInput();
            $productIds = $data['product_ids'] ?? [];
            
            if (!is_array($productIds)) {
                $this->jsonResponse(['success' => false, 'message' => 'product_ids must be an array'], 400);
                return;
            }

            $mergedItems = $this->service->sync($userId, $productIds);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Wishlist synchronized successfully',
                'data' => $mergedItems
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

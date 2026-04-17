<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\CartItemService;

class CartItemController extends BaseController
{
    public function __construct(
        private CartItemService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $item = $this->service->create($data);
            return $this->success('Cart item created', $item, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Cart items retrieved', $this->service->getAll($limit, $offset));
        });
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Cart items retrieved', $this->service->getAllPaginated($limit, $offset));
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            return $this->success('Cart items retrieved', $this->service->search($query, $limit, $offset));
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            return $this->success('Cart item retrieved', $this->service->getById($id));
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            return $this->success('Cart item retrieved', $this->service->getByIdEnriched($id));
        });
    }

    public function getByCartProduct(int $cartId, int $productId): array
    {
        return $this->handle(function () use ($cartId, $productId) {
            return $this->success('Cart item retrieved', $this->service->getByCartProduct($cartId, $productId));
        });
    }

    public function getByCart(int $cartId): array
    {
        return $this->handle(function () use ($cartId) {
            return $this->success('Cart items retrieved', $this->service->getByCart($cartId));
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            return $this->success('Count retrieved', ['count' => $this->service->count()]);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            return $this->success('Cart item updated', $this->service->update($id, $data));
        });
    }

    public function updateByCartProduct(int $cartId, int $productId, array $data): array
    {
        return $this->handle(function () use ($cartId, $productId, $data) {
            return $this->success('Cart item updated', $this->service->updateByCartProduct($cartId, $productId, $data));
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Cart item deleted', ['deleted' => true]);
        });
    }

    public function deleteByCartProduct(int $cartId, int $productId): array
    {
        return $this->handle(function () use ($cartId, $productId) {
            $this->service->deleteByCartProduct($cartId, $productId);
            return $this->success('Cart item deleted', ['deleted' => true]);
        });
    }
}

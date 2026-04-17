<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\CartService;

class CartController extends BaseController
{
    public function __construct(
        private CartService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $cart = $this->service->create($data);
            return $this->success('Cart created', $cart, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Carts retrieved', $this->service->getAll($limit, $offset));
        });
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Carts retrieved', $this->service->getAllPaginated($limit, $offset));
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            return $this->success('Carts retrieved', $this->service->search($query, $limit, $offset));
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            return $this->success('Cart retrieved', $this->service->getById($id));
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            return $this->success('Cart retrieved', $this->service->getByIdEnriched($id));
        });
    }

    public function getActiveByUser(int $userId): array
    {
        return $this->handle(function () use ($userId) {
            return $this->success('Active cart retrieved', $this->service->getActiveByUser($userId));
        });
    }

    public function getActiveBySession(string $sessionId): array
    {
        return $this->handle(function () use ($sessionId) {
            return $this->success('Active cart retrieved', $this->service->getActiveBySession($sessionId));
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
            return $this->success('Cart updated', $this->service->update($id, $data));
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Cart deleted', ['deleted' => true]);
        });
    }
}

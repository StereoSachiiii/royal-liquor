<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\OrderService;
use App\Core\Session;

class OrderController extends BaseController
{
    public function __construct(
        private OrderService $service,
        private Session $session,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $order = $this->service->create($data);
            return $this->success('Order created', $order, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $orders = $this->service->getAll($limit, $offset);
            return $this->success('Orders retrieved successfully', $orders);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $order = $this->service->getById($id);
            return $this->success('Order retrieved successfully', $order);
        });
    }

    public function getDetailedOrderById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $order = $this->service->getDetailedOrderById($id);
            return $this->success('Order details retrieved successfully', $order);
        });
    }

    public function getByOrderNumber(string $orderNumber): array
    {
        return $this->handle(function () use ($orderNumber) {
            $order = $this->service->getByOrderNumber($orderNumber);
            return $this->success('Order retrieved', $order);
        });
    }

    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($userId, $limit, $offset) {
            $orders = $this->service->getByUser($userId, $limit, $offset);
            return $this->success('User orders retrieved', $orders);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            return $this->success('Count retrieved', ['count' => $this->service->count()]);
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            $orders = $this->service->search($query, $limit, $offset);
            return $this->success('Orders found', $orders);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $order = $this->service->update($id, $data);
            return $this->success('Order updated', $order);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Order deleted', ['deleted' => true]);
        });
    }

    public function cancel(int $id): array
    {
        return $this->handle(function () use ($id) {
            $currentUserId = $this->session->get('user_id');
            $isAdmin       = (bool)$this->session->get('is_admin');
            $order = $this->service->cancel($id, $currentUserId, $isAdmin);
            return $this->success('Order cancelled', $order);
        });
    }
}

<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\OrderItemService;

class OrderItemController extends BaseController
{
    public function __construct(
        private OrderItemService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $item = $this->service->create($data);
            return $this->success('Order item created', $item, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $data = $this->service->getAllPaginated($limit, $offset);
            return $this->success('Order items retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getByIdEnriched($id);
            if (!$data) {
                return ['success' => false, 'message' => 'Order item not found', 'data' => null, 'code' => 404];
            }
            return $this->success('Order item retrieved', $data);
        });
    }

    public function getByOrder(int $orderId): array
    {
        return $this->handle(function () use ($orderId) {
            $data = $this->service->getByOrder($orderId);
            return $this->success('Order items retrieved', $data);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            return $this->success('Order item updated', $updated);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->service->count();
            return $this->success('Count retrieved', ['count' => $count]);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Order item deleted', ['deleted' => true]);
        });
    }
}

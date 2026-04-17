<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\PaymentService;

class PaymentController extends BaseController
{
    public function __construct(
        private PaymentService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $payment = $this->service->create($data);
            return $this->success('Payment created', $payment, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Payments retrieved', $this->service->getAll($limit, $offset));
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            return $this->success('Search results', $this->service->search($query, $limit, $offset));
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            return $this->success('Payment retrieved', $this->service->getById($id));
        });
    }

    public function getByOrder(int $orderId): array
    {
        return $this->handle(function () use ($orderId) {
            return $this->success('Order payments retrieved', $this->service->getByOrder($orderId));
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
            return $this->success('Payment updated', $this->service->update($id, $data));
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Payment deleted');
        });
    }

    public function hardDelete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->hardDelete($id);
            return $this->success('Payment permanently deleted');
        });
    }
}

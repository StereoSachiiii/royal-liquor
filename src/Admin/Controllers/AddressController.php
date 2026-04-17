<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\AddressService;

class AddressController extends BaseController
{
    public function __construct(
        private AddressService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $address = $this->service->create($data);
            return $this->success('Address created', $address, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $data = $this->service->getAll($limit, $offset);
            return $this->success('Addresses retrieved', $data);
        });
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $data = $this->service->getAllEnriched($limit, $offset);
            return $this->success('Addresses retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getById($id);
            return $this->success('Address retrieved', $data);
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getByIdEnriched($id);
            return $this->success('Address retrieved', $data);
        });
    }

    public function getByUser(int $userId): array
    {
        return $this->handle(function () use ($userId) {
            $data = $this->service->getByUser($userId);
            return $this->success('User addresses retrieved', $data);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->service->count();
            return $this->success('Count retrieved', ['count' => $count]);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            return $this->success('Address updated', $updated);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Address deleted', ['deleted' => true]);
        });
    }
}

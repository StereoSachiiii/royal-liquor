<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\WarehouseService;


class WarehouseController extends BaseController
{
    public function __construct(
        private WarehouseService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            return $this->success('Warehouse created successfully', $this->service->create($data), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Warehouses retrieved successfully', $this->service->getAll($limit, $offset));
        });
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Warehouses (including inactive) retrieved successfully', $this->service->getAllIncludingInactive($limit, $offset));
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            return $this->success('Warehouse retrieved successfully', $this->service->getById($id));
        });
    }

    public function getByIdAdmin(int $id): array
    {
        return $this->handle(function () use ($id) {
            return $this->success('Warehouse (admin) retrieved successfully', $this->service->getByIdAdmin($id));
        });
    }

    public function getByName(string $name): array
    {
        return $this->handle(function () use ($name) {
            return $this->success('Warehouse retrieved successfully', $this->service->getByName($name));
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            return $this->success('Warehouse search results', $this->service->search($query, $limit, $offset));
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            return $this->success('Warehouse count retrieved successfully', ['count' => $this->service->count()]);
        });
    }

    public function countAll(): array
    {
        return $this->handle(function () {
            return $this->success('Warehouse total count retrieved successfully', ['count' => $this->service->countAll()]);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            return $this->success('Warehouse updated successfully', $this->service->update($id, $data));
        });
    }

    public function partialUpdate(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            return $this->success('Warehouse updated successfully', $this->service->partialUpdate($id, $data));
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Warehouse deleted');
        });
    }

    public function restore(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->restore($id);
            return $this->success('Warehouse restored');
        });
    }

    public function hardDelete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->hardDelete($id);
            return $this->success('Warehouse permanently deleted');
        });
    }
}

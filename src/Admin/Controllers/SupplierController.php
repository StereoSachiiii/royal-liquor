<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\SupplierService;


class SupplierController extends BaseController
{
    public function __construct(
        private SupplierService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $created = $this->service->create($data);
            return $this->success('Supplier created successfully', $created, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $suppliers = $this->service->getAll($limit, $offset);
            return $this->success('Suppliers retrieved successfully', $suppliers);
        });
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $suppliers = $this->service->getAllIncludingInactive($limit, $offset);
            return $this->success('Suppliers (including inactive) retrieved successfully', $suppliers);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $supplier = $this->service->getById($id);
            return $this->success('Supplier retrieved successfully', $supplier);
        });
    }

    public function getByIdAdmin(int $id): array
    {
        return $this->handle(function () use ($id) {
            $supplier = $this->service->getByIdAdmin($id);
            return $this->success('Supplier (admin) retrieved successfully', $supplier);
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            $supplier = $this->service->getByIdEnriched($id);
            return $this->success('Supplier details loaded successfully', $supplier);
        });
    }

    public function getByName(string $name): array
    {
        return $this->handle(function () use ($name) {
            $supplier = $this->service->getByName($name);
            return $this->success('Supplier retrieved successfully', $supplier);
        });
    }

    public function getByEmail(array $data): array
    {
        return $this->handle(function () use ($data) {
            $supplier = $this->service->getByEmail($data['email'] ?? '');
            return $this->success('Supplier retrieved successfully', $supplier);
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            $results = $this->service->search($query, $limit, $offset);
            return $this->success('Suppliers search results', $results);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = ['count' => $this->service->count()];
            return $this->success('Supplier count retrieved successfully', $count);
        });
    }

    public function countAll(): array
    {
        return $this->handle(function () {
            $count = ['count' => $this->service->countAll()];
            return $this->success('Supplier total count retrieved successfully', $count);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            return $this->success('Supplier updated successfully', $updated);
        });
    }

    public function partialUpdate(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->partialUpdate($id, $data);
            return $this->success('Supplier partially updated successfully', $updated);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Supplier deleted', ['deleted' => true]);
        });
    }

    public function restore(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->restore($id);
            return $this->success('Supplier restored', ['restored' => true]);
        });
    }

    public function hardDelete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->hardDelete($id);
            return $this->success('Supplier permanently deleted', ['deleted' => true]);
        });
    }
}

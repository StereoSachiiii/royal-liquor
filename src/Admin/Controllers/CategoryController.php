<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\CategoryService;

class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $created = $this->service->create($data);
            return $this->success('Category created successfully', $created, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $categories = $this->service->getAll($limit, $offset);
            return $this->success('Categories retrieved successfully', $categories);
        });
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $categories = $this->service->getAllIncludingInactive($limit, $offset);
            return $this->success('All categories (including inactive) retrieved', $categories);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $category = $this->service->getById($id);
            return $this->success('Category retrieved successfully', $category);
        });
    }

    public function getByIdAdmin(int $id): array
    {
        return $this->handle(function () use ($id) {
            $category = $this->service->getByIdAdmin($id);
            return $this->success('Category (admin) retrieved', $category);
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getByIdEnriched($id);
            return $this->success('Category (enriched) retrieved', $data);
        });
    }

    public function getByName(string $name): array
    {
        return $this->handle(function () use ($name) {
            $category = $this->service->getByName($name);
            return $this->success('Category retrieved successfully', $category);
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            $results = $this->service->search($query, $limit, $offset);
            return $this->success('Categories search results', $results);
        });
    }

    public function searchEnriched(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            $results = $this->service->searchEnriched($query, $limit, $offset);
            return $this->success('Categories search results (enriched)', $results);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = ['count' => $this->service->count(false)];
            return $this->success('Category count retrieved', $count);
        });
    }

    public function countAll(): array
    {
        return $this->handle(function () {
            $count = ['count' => $this->service->count(true)];
            return $this->success('Category total count retrieved', $count);
        });
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $data = $this->service->getAllEnriched($limit, $offset);
            return $this->success('Categories (enriched) retrieved', $data);
        });
    }

    public function getProductsByCategoryIdEnriched(int $categoryId, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($categoryId, $limit, $offset) {
            $data = $this->service->getProductsByCategoryIdEnriched($categoryId, $limit, $offset);
            return $this->success('Category products retrieved', $data);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            return $this->success('Category updated successfully', $updated);
        });
    }

    public function delete(int $id, bool $hard = false): array
    {
        return $this->handle(function () use ($id, $hard) {
            $this->service->delete($id, $hard);
            return $this->success('Category deleted successfully', ['deleted' => true]);
        });
    }

    public function restore(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->restore($id);
            return $this->success('Category restored', ['restored' => true]);
        });
    }
}

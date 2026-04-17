<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\FeedbackService;

class FeedbackController extends BaseController
{
    public function __construct(
        private FeedbackService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $feedback = $this->service->create($data);
            return $this->success('Feedback created', $feedback, 201);
        });
    }

    public function getAll(): array
    {
        return $this->handle(function () {
            $data = $this->service->getAll();
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0, ?bool $isActive = null): array
    {
        return $this->handle(function () use ($limit, $offset, $isActive) {
            $data = $this->service->getAllPaginated($limit, $offset, $isActive);
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function getAllWithProductDetails(): array
    {
        return $this->handle(function () {
            $data = $this->service->getAllWithProductDetails();
            return $this->success('Feedback with product details retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getById($id);
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getByIdEnriched($id);
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            return $this->success('Feedback updated', $updated);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Feedback deleted', ['deleted' => true]);
        });
    }

    public function hardDelete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->hardDelete($id);
            return $this->success('Feedback permanently deleted', ['deleted' => true]);
        });
    }
}

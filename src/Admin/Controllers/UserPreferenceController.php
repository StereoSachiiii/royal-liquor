<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\UserPreferenceService;


class UserPreferenceController extends BaseController
{
    public function __construct(
        private UserPreferenceService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $pref = $this->service->create($data);
            return $this->success('User preference created', $pref, 201);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getById($id);
            return $this->success('User preference retrieved', $data);
        });
    }

    public function getByUserId(int $userId): array
    {
        return $this->handle(function () use ($userId) {
            $data = $this->service->getByUserId($userId);
            return $this->success('User preferences retrieved', $data);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            return $this->success('User preference updated', $updated);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('User preference deleted', ['deleted' => true]);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $data = $this->service->getAll($limit, $offset);
            return $this->success('User preferences retrieved', $data);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->service->count();
            return $this->success('Count retrieved', ['count' => $count]);
        });
    }
}

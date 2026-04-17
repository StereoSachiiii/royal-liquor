<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\FlavorProfileService;

class FlavorProfileController extends BaseController
{
    public function __construct(
        private FlavorProfileService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            error_log('[FlavorProfileController] Create Data: ' . print_r($data, true));
            file_put_contents(__DIR__ . '/../../debug_flavor_log.txt', date('Y-m-d H:i:s') . " [CREATE] Payload: " . json_encode($data) . "\n", FILE_APPEND);
            
            return $this->success('Created flavor profile', $this->service->create($data), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            return $this->success('Fetched flavor profiles', $this->service->getAll($limit, $offset));
        });
    }

    public function getByProductId(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            return $this->success('Fetched flavor profile', $this->service->getByProductId($productId));
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            return $this->success('Search results', $this->service->search($query, $limit, $offset));
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
            error_log('[FlavorProfileController] Update ID: ' . $id . ' Data: ' . print_r($data, true));
            file_put_contents(__DIR__ . '/../../debug_flavor_log.txt', date('Y-m-d H:i:s') . " [UPDATE] ID: $id Payload: " . json_encode($data) . "\n", FILE_APPEND);
            return $this->success('Flavor profile updated', $this->service->update($id, $data));
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Flavor profile deleted', ['deleted' => true]);
        });
    }
}

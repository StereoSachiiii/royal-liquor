<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\WarehouseRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;
use App\Admin\Exceptions\DuplicateException;

use App\DTO\Requests\CreateWarehouseRequest;
use App\DTO\Requests\UpdateWarehouseRequest;
use App\DTO\DTOException;

class WarehouseService
{
    public function __construct(
        private WarehouseRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateWarehouseRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }
        
        if ($this->repo->existsByName($dto->name)) {
            throw new DuplicateException('Warehouse with this name already exists');
        }
        
        $warehouse = $this->repo->create($dto->toArray());
        return $warehouse->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $warehouses = $this->repo->getAll($limit, $offset);
        return array_map(fn($w) => $w->toArray(), $warehouses);
    }

    public function getById(int $id): array
    {
        $warehouse = $this->repo->getById($id);
        if (!$warehouse) {
            throw new NotFoundException('Warehouse not found');
        }
        return $warehouse->toArray();
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateWarehouseRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        if (isset($dto->name) && $this->repo->existsByName($dto->name, $id)) {
            throw new DuplicateException('Warehouse with this name already exists');
        }
        
        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Warehouse not found');
        }
        return $updated->toArray();
    }

    public function partialUpdate(int $id, array $data): array
    {
        return $this->update($id, $data);
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Warehouse not found');
        }
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $warehouses = $this->repo->getAllIncludingInactive($limit, $offset);
        return array_map(fn($w) => $w->toArray(), $warehouses);
    }

    public function getByIdAdmin(int $id): array
    {
        $warehouse = $this->repo->getByIdAdmin($id);
        if (!$warehouse) {
            throw new NotFoundException('Warehouse not found');
        }
        return $warehouse->toArray();
    }

    public function getByName(string $name): array
    {
        $warehouse = $this->repo->getByName($name);
        if (!$warehouse) {
            throw new NotFoundException('Warehouse not found');
        }
        return $warehouse->toArray();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $warehouses = $this->repo->search($query, $limit, $offset);
        return array_map(fn($w) => $w->toArray(), $warehouses);
    }

    public function countAll(): int
    {
        return $this->repo->countAll();
    }

    public function restore(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Warehouse not found');
        }
        
        $restored = $this->repo->restore($id);
        if (!$restored) {
            throw new DatabaseException('Restore failed');
        }
    }

    public function hardDelete(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Warehouse not found');
        }
        
        $deleted = $this->repo->hardDelete($id);
        if (!$deleted) {
            throw new DatabaseException('Hard delete failed');
        }
    }
}

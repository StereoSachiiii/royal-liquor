<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\SupplierRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;
use App\Admin\Exceptions\DuplicateException;

use App\DTO\Requests\CreateSupplierRequest;
use App\DTO\Requests\UpdateSupplierRequest;
use App\DTO\DTOException;

class SupplierService
{
    public function __construct(
        private SupplierRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateSupplierRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        if ($this->repo->existsByName($dto->name)) {
            throw new DuplicateException('Supplier with this name already exists');
        }
        
        $supplier = $this->repo->create($dto->toArray());
        return $supplier->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->repo->getAll($limit, $offset);
        return array_map(fn($s) => $s->toArray(), $suppliers);
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->repo->getAllIncludingInactive($limit, $offset);
        return array_map(fn($s) => $s->toArray(), $suppliers);
    }

    public function getById(int $id): array
    {
        $supplier = $this->repo->getById($id);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function getByIdEnriched(int $id): array
    {
        $supplier = $this->repo->getByIdEnriched($id);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier;
    }

    public function getByIdAdmin(int $id): array
    {
        $supplier = $this->repo->getByIdAdmin($id);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function getByName(string $name): array
    {
        $supplier = $this->repo->getByName($name);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function getByEmail(string $email): array
    {
        $supplier = $this->repo->getByEmail($email);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->repo->search($query, $limit, $offset);
        return array_map(fn($s) => $s->toArray(), $suppliers);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function countAll(): int
    {
        return $this->repo->countAll();
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateSupplierRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }
        
        if (isset($dto->name) && $this->repo->existsByName($dto->name, $id)) {
            throw new DuplicateException('Supplier with this name already exists');
        }
        
        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Supplier not found');
        }
        return $updated->toArray();
    }

    public function partialUpdate(int $id, array $data): array
    {
        // For our DTO structure, partial update behaves the same as update since UpdateRequest has optional fields
        return $this->update($id, $data);
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Supplier not found');
        }
    }

    public function restore(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Supplier not found');
        }
        
        $restored = $this->repo->restore($id);
        if (!$restored) {
            throw new DatabaseException('Restore failed');
        }
    }

    public function hardDelete(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Supplier not found');
        }
        
        $deleted = $this->repo->hardDelete($id);
        if (!$deleted) {
            throw new DatabaseException('Hard delete failed');
        }
    }
}

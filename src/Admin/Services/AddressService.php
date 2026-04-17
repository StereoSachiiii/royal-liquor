<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\AddressRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

use App\DTO\Requests\CreateAddressRequest;
use App\DTO\Requests\UpdateAddressRequest;
use App\DTO\DTOException;

class AddressService
{
    public function __construct(
        private AddressRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateAddressRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $address = $this->repo->create($dto->toArray());
        return $address->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $addresses = $this->repo->getAll($limit, $offset);
        return array_map(fn($a) => $a->toArray(), $addresses);
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllPaginated($limit, $offset);
    }

    public function getById(int $id): array
    {
        $address = $this->repo->getById($id);
        if (!$address) {
            throw new NotFoundException('Address not found');
        }
        return $address->toArray();
    }

    public function getByIdEnriched(int $id): array
    {
        $address = $this->repo->getByIdEnriched($id);
        if (!$address) {
            throw new NotFoundException('Address not found');
        }
        return $address;
    }

    public function getByUser(int $userId): array
    {
        $addresses = $this->repo->getByUser($userId);
        return array_map(fn($a) => $a->toArray(), $addresses);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateAddressRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Address not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Address not found');
        }
    }
}

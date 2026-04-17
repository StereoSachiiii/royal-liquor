<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\UserPreferenceRepository;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\ValidationException;

use App\DTO\Requests\CreateUserPreferenceRequest;
use App\DTO\Requests\UpdateUserPreferenceRequest;
use App\DTO\DTOException;

class UserPreferenceService
{
    public function __construct(
        private UserPreferenceRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateUserPreferenceRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }
        
        if ($this->repo->existsByUserId($dto->user_id)) {
            throw new ValidationException('User preference for this user already exists');
        }
        
        // Repository now returns array directly
        return $this->repo->create($dto->toArray());
    }

    public function getById(int $id): array
    {
        $pref = $this->repo->getById($id);
        if (!$pref) {
            throw new NotFoundException('User preference not found');
        }
        // Repository returns array, no need to call toArray()
        return $pref;
    }

    public function getByUserId(int $userId): array
    {
        $pref = $this->repo->getByUserId($userId);
        if (!$pref) {
            throw new NotFoundException('User preference not found');
        }
        return $pref;
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateUserPreferenceRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('User preference not found');
        }
        return $updated;
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('User preference not found');
        }
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        // Repository returns array of arrays now
        return $this->repo->getAll($limit, $offset);
    }

    public function count(): int
    {
        return $this->repo->count();
    }
}


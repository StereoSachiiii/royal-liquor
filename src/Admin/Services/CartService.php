<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\CartRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

use App\DTO\Requests\CreateCartRequest;
use App\DTO\Requests\UpdateCartRequest;
use App\DTO\DTOException;

class CartService
{
    public function __construct(
        private CartRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateCartRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $cart = $this->repo->create($dto->toArray());
        return $cart->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $carts = $this->repo->getAll($limit, $offset);
        return array_map(fn($c) => $c->toArray(), $carts);
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllPaginated($limit, $offset);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->repo->search($query, $limit, $offset);
    }

    public function getByIdEnriched(int $id): array
    {
        $cart = $this->repo->getByIdEnriched($id);
        if (!$cart) {
            throw new NotFoundException('Cart not found');
        }
        return $cart;
    }

    public function getById(int $id): array
    {
        $cart = $this->repo->getById($id);
        if (!$cart) {
            throw new NotFoundException('Cart not found');
        }
        return $cart->toArray();
    }

    public function getActiveByUser(int $userId): array
    {
        $cart = $this->repo->getActiveByUser($userId);
        if (!$cart) {
            throw new NotFoundException('Active cart not found');
        }
        return $cart->toArray();
    }

    public function getActiveBySession(string $sessionId): array
    {
        $cart = $this->repo->getActiveBySession($sessionId);
        if (!$cart) {
            throw new NotFoundException('Active cart not found');
        }
        return $cart->toArray();
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateCartRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Cart not found');
        }

        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Cart not found');
        }
    }
}

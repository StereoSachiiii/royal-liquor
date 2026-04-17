<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\CartItemRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;
use App\Admin\Exceptions\DuplicateException;

use App\DTO\Requests\CreateCartItemRequest;
use App\DTO\Requests\UpdateCartItemRequest;
use App\DTO\DTOException;

class CartItemService
{
    public function __construct(
        private CartItemRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateCartItemRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        if ($this->repo->getByCartProduct($dto->cart_id, $dto->product_id)) {
            throw new DuplicateException('Item already in cart');
        }

        $item = $this->repo->create($dto->toArray());
        return $item->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $items = $this->repo->getAll($limit, $offset);
        return array_map(fn($i) => $i->toArray(), $items);
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllPaginated($limit, $offset);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->repo->search($query, $limit, $offset);
    }

    public function getById(int $id): array
    {
        $item = $this->repo->getById($id);
        if (!$item) {
            throw new NotFoundException('Cart item not found');
        }
        return $item->toArray();
    }

    public function getByIdEnriched(int $id): array
    {
        $item = $this->repo->getByIdEnriched($id);
        if (!$item) {
            throw new NotFoundException('Cart item not found');
        }
        return $item;
    }

    public function getByCartProduct(int $cartId, int $productId): array
    {
        $item = $this->repo->getByCartProduct($cartId, $productId);
        if (!$item) {
            throw new NotFoundException('Cart item not found');
        }
        return $item->toArray();
    }

    public function getByCart(int $cartId): array
    {
        return $this->repo->getByCart($cartId);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateCartItemRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Cart item not found');
        }

        return $updated->toArray();
    }

    public function updateByCartProduct(int $cartId, int $productId, array $data): array
    {
        try {
            $dto = UpdateCartItemRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->updateByCartProduct($cartId, $productId, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Cart item not found');
        }

        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Cart item not found');
        }
    }

    public function deleteByCartProduct(int $cartId, int $productId): void
    {
        $deleted = $this->repo->deleteByCartProduct($cartId, $productId);
        if (!$deleted) {
            throw new NotFoundException('Cart item not found');
        }
    }
}

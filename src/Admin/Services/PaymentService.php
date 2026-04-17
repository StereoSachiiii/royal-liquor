<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\PaymentRepository;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

use App\DTO\Requests\CreatePaymentRequest;
use App\DTO\Requests\UpdatePaymentRequest;
use App\DTO\DTOException;

class PaymentService
{
    public function __construct(
        private PaymentRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreatePaymentRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $payment = $this->repo->create($dto->toArray());
        return $payment->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $payments = $this->repo->getAll($limit, $offset);
        return array_map(fn($p) => $p->toArray(), $payments);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $payments = $this->repo->search($query, $limit, $offset);
        return array_map(fn($p) => $p->toArray(), $payments);
    }

    public function getById(int $id): array
    {
        $payment = $this->repo->getById($id);
        if (!$payment) {
            throw new NotFoundException('Payment not found');
        }
        return $payment->toArray();
    }

    public function getByOrder(int $orderId): array
    {
        $payments = $this->repo->getByOrder($orderId);
        return array_map(fn($p) => $p->toArray(), $payments);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdatePaymentRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Payment not found');
        }

        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Payment not found');
        }
    }

    public function hardDelete(int $id): void
    {
        $deleted = $this->repo->hardDelete($id);
        if (!$deleted) {
            throw new NotFoundException('Payment not found');
        }
    }
}

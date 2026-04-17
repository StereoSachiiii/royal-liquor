<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\FeedbackRepository;
use App\Admin\Exceptions\DuplicateException;
use App\Admin\Exceptions\ValidationException;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

use App\DTO\Requests\CreateFeedbackRequest;
use App\DTO\Requests\UpdateFeedbackRequest;
use App\DTO\DTOException;

class FeedbackService
{
    public function __construct(
        private FeedbackRepository $repo,
    ) {}

    public function create(array $data): array
    {
        try {
            $dto = CreateFeedbackRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }

        if ($this->repo->exists($dto->user_id, $dto->product_id)) {
            throw new DuplicateException('Feedback from this user for this product already exists.');
        }

        $feedback = $this->repo->create($dto->toArray());
        return $feedback->toArray();
    }

    public function getAll(): array
    {
        return $this->repo->getAllWithProductDetails();
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0, ?bool $isActive = null): array
    {
        return $this->repo->getAllPaginated($limit, $offset, $isActive);
    }

    public function getById(int $id): array
    {
        $feedback = $this->repo->getByIdEnriched($id);
        if (!$feedback) {
            throw new NotFoundException('Feedback not found.');
        }
        return $feedback;
    }

    public function getByIdEnriched(int $id): array
    {
        $feedback = $this->repo->getByIdEnriched($id);
        if (!$feedback) {
            throw new NotFoundException('Feedback not found.');
        }
        return $feedback;
    }

    public function getAllWithProductDetails(): array
    {
        $feedbacks = $this->repo->getAllWithProductDetails();
        return array_map(fn($f) => is_array($f) ? $f : $f->toArray(), $feedbacks);
    }

    public function update(int $id, array $data): array
    {
        try {
            $dto = UpdateFeedbackRequest::fromArray($data);
        } catch (DTOException $e) {
            throw new ValidationException($e->getMessage(), $e->getErrors());
        }
        
        $updated = $this->repo->update($id, $dto->toChangeset());
        if (!$updated) {
            throw new NotFoundException('Feedback not found.');
        }
        
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        // Soft delete
        $result = $this->repo->softDelete($id);
        if (!$result) {
            throw new NotFoundException('Feedback not found.');
        }
    }

    public function hardDelete(int $id): void
    {
        $result = $this->repo->hardDelete($id);
        if (!$result) {
            throw new NotFoundException('Feedback not found.');
        }
    }
}

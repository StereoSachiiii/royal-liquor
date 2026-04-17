<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating feedback.
 * Replaces FeedbackValidator::validateUpdate().
 */
class UpdateFeedbackRequest extends BaseDTO
{
    public ?int $rating = null;
    public ?string $comment = null;
    public ?bool $is_verified_purchase = null;
    public ?bool $is_active = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['rating'])) {
            $validator->field('rating', 'integer|min:1|max:5');
        }
        if (isset($data['comment'])) {
            $validator->field('comment', 'max_length:1000');
        }
        if (isset($data['is_verified_purchase'])) {
            $validator->field('is_verified_purchase', 'boolean');
        }
        if (isset($data['is_active'])) {
            $validator->field('is_active', 'boolean');
        }

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }

    public function toChangeset(): array
    {
        return array_filter($this->toArray(), fn($v) => $v !== null);
    }
}

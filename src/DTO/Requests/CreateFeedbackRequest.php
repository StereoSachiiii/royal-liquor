<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating feedback.
 * Replaces FeedbackValidator::validateCreate().
 */
class CreateFeedbackRequest extends BaseDTO
{
    public int $user_id;
    public int $product_id;
    public int $rating;

    public ?string $comment = null;
    public ?bool $is_verified_purchase = false;
    public ?bool $is_active = true;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('user_id', 'required|integer|min:1')
            ->field('product_id', 'required|integer|min:1')
            ->field('rating', 'required|integer|min:1|max:5');

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
}

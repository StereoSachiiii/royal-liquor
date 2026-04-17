<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a cart.
 * Replaces CartValidator::validateCreate().
 * Model: id, user_id, session_id, status, total_cents, item_count, ...
 */
class CreateCartRequest extends BaseDTO
{
    public string $session_id;

    public ?int    $user_id = null;
    public ?string $status  = 'active';

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('session_id', 'required|max_length:64')
            ->field('user_id',    'integer|min:1')
            ->field('status',     'in:active,converted,abandoned,expired');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

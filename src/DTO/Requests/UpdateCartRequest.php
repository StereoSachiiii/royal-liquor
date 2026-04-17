<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a cart.
 * Replaces CartValidator::validateUpdate().
 */
class UpdateCartRequest extends BaseDTO
{
    public ?string $status      = null;
    public ?int    $total_cents = null;
    public ?int    $item_count  = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['status'])) {
            $validator->field('status', 'in:active,converted,abandoned,expired');
        }
        if (isset($data['total_cents'])) {
            $validator->field('total_cents', 'integer|min:0');
        }
        if (isset($data['item_count'])) {
            $validator->field('item_count', 'integer|min:0');
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

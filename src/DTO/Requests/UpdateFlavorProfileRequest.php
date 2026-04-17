<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for updating a flavor profile.
 * Replaces FlavorProfileValidator::validateUpdate().
 */
class UpdateFlavorProfileRequest extends BaseDTO
{
    public ?int $sweetness = null;
    public ?int $bitterness = null;
    public ?int $strength = null;
    public ?int $smokiness = null;
    public ?int $fruitiness = null;
    public ?int $spiciness = null;
    public ?array $tags = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        if (isset($data['sweetness']))  $validator->field('sweetness',  'integer|min:0|max:10');
        if (isset($data['bitterness'])) $validator->field('bitterness', 'integer|min:0|max:10');
        if (isset($data['strength']))   $validator->field('strength',   'integer|min:0|max:10');
        if (isset($data['smokiness']))  $validator->field('smokiness',  'integer|min:0|max:10');
        if (isset($data['fruitiness'])) $validator->field('fruitiness', 'integer|min:0|max:10');
        if (isset($data['spiciness']))  $validator->field('spiciness',  'integer|min:0|max:10');

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

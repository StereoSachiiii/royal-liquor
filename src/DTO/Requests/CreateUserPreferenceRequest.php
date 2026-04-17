<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for creating a user preference.
 * Replaces UserPreferenceValidator::validateCreate().
 */
class CreateUserPreferenceRequest extends BaseDTO
{
    public int $user_id;

    public ?float $preferred_sweetness = null;
    public ?float $preferred_bitterness = null;
    public ?float $preferred_strength = null;
    public ?float $preferred_smokiness = null;
    public ?float $preferred_fruitiness = null;
    public ?float $preferred_spiciness = null;
    public ?array $favorite_categories = null;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator->field('user_id', 'required|integer|min:1');

        if (isset($data['preferred_sweetness']))  $validator->field('preferred_sweetness',  'min:0|max:10');
        if (isset($data['preferred_bitterness'])) $validator->field('preferred_bitterness', 'min:0|max:10');
        if (isset($data['preferred_strength']))   $validator->field('preferred_strength',   'min:0|max:10');
        if (isset($data['preferred_smokiness']))  $validator->field('preferred_smokiness',  'min:0|max:10');
        if (isset($data['preferred_fruitiness'])) $validator->field('preferred_fruitiness', 'min:0|max:10');
        if (isset($data['preferred_spiciness']))  $validator->field('preferred_spiciness',  'min:0|max:10');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }
        
        $fields = ['preferred_sweetness', 'preferred_bitterness', 'preferred_strength', 'preferred_smokiness', 'preferred_fruitiness', 'preferred_spiciness'];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $data[$f] = (float)$data[$f];
            }
        }

        return parent::fromArray($data);
    }
}

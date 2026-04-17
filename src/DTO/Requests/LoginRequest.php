<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for user login.
 * Replaces UserValidator::loginValidate().
 */
class LoginRequest extends BaseDTO
{
    public string $email;
    public string $password;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('email',    'required|email')
            ->field('password', 'required');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        return parent::fromArray($data);
    }
}

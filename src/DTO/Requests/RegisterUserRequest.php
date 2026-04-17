<?php
declare(strict_types=1);

namespace App\DTO\Requests;

use App\DTO\BaseDTO;
use App\DTO\DTOException;
use App\Validation\Validator;

/**
 * Validated request DTO for user registration.
 * Replaces UserValidator::validateCreate().
 * UserModel uses camelCase — keeping snake_case here for request body convention.
 */
class RegisterUserRequest extends BaseDTO
{
    // Required
    public string $name;
    public string $email;
    public string $password;

    // Optional
    public ?string $phone            = null;
    public ?string $profile_image_url = null;
    public ?bool   $is_admin         = false;

    public static function fromArray(array $data): static
    {
        $validator = new Validator($data);

        $validator
            ->field('name',     'required|min_length:2|max_length:100')
            ->field('email',    'required|email')
            ->field('password', 'required|min_length:8')
            ->field('phone',    'regex:/^\+?[0-9]{8,15}$/');

        if (!$validator->validate()) {
            throw new DTOException('Validation failed', $validator->errors());
        }

        // Password complexity check (kept as-is from UserValidator)
        if (!preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            $data['password'] ?? ''
        )) {
            throw new DTOException('Validation failed', [
                'password' => ['Password must contain uppercase, lowercase, number & special char'],
            ]);
        }

        return parent::fromArray($data);
    }
}

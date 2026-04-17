<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class EmailRule implements RuleInterface
{
    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function message(string $field): string
    {
        return "The {$field} field must be a valid email address.";
    }
}

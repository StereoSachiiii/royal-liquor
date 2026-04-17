<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class IntegerRule implements RuleInterface
{
    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_int($value) || (is_string($value) && ctype_digit($value));
    }

    public function message(string $field): string
    {
        return "The {$field} field must be an integer.";
    }
}

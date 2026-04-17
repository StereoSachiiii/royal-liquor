<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class StringRule implements RuleInterface
{
    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_string($value);
    }

    public function message(string $field): string
    {
        return "The {$field} field must be a string.";
    }
}

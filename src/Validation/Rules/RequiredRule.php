<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class RequiredRule implements RuleInterface
{
    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && count($value) === 0) {
            return false;
        }

        return true;
    }

    public function message(string $field): string
    {
        return "The {$field} field is required.";
    }
}

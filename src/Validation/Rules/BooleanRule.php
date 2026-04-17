<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

/**
 * Validates that a value is boolean-like.
 * Accepts: true, false, 1, 0, "true", "false", "1", "0".
 */
class BooleanRule implements RuleInterface
{
    private const VALID = [true, false, 1, 0, '1', '0', 'true', 'false'];

    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null) {
            return true; // optional by default — use required|boolean to enforce
        }

        return in_array($value, self::VALID, strict: true);
    }

    public function message(string $field): string
    {
        return "The {$field} field must be a boolean (true/false/1/0).";
    }
}

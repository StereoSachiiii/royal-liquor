<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

/**
 * Validates that a string value has at most N characters.
 * Usage: max_length:255
 */
class MaxLengthRule implements RuleInterface
{
    private int $max;

    public function __construct(string $max)
    {
        $this->max = (int)$max;
    }

    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return mb_strlen((string)$value) <= $this->max;
    }

    public function message(string $field): string
    {
        return "The {$field} field must not exceed {$this->max} characters.";
    }
}

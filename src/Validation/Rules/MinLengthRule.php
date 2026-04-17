<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

/**
 * Validates that a string value has at least N characters.
 * Usage: min_length:2
 */
class MinLengthRule implements RuleInterface
{
    private int $min;

    public function __construct(string $min)
    {
        $this->min = (int)$min;
    }

    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true; // optional — combine with required to enforce presence
        }

        return mb_strlen((string)$value) >= $this->min;
    }

    public function message(string $field): string
    {
        return "The {$field} field must be at least {$this->min} characters.";
    }
}

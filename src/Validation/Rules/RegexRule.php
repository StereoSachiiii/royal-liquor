<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;
use InvalidArgumentException;

/**
 * Validates that a value matches a regular expression.
 * Usage: regex:/^\+?[0-9]{8,15}$/
 *
 * Note: when passing via pipe-delimited string, the pattern itself must not
 * contain unescaped pipe characters. Use the RuleInterface object form for
 * complex patterns, or escape pipes.
 */
class RegexRule implements RuleInterface
{
    private string $pattern;

    public function __construct(string $pattern)
    {
        // Validate the pattern compiles
        if (@preg_match($pattern, '') === false) {
            throw new InvalidArgumentException("Invalid regex pattern: {$pattern}");
        }

        $this->pattern = $pattern;
    }

    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match($this->pattern, (string)$value) === 1;
    }

    public function message(string $field): string
    {
        return "The {$field} field format is invalid.";
    }
}

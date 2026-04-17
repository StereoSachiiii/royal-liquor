<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

/**
 * Validates that a value is in a given set of allowed values.
 * Usage (string token): in:pending,paid,cancelled
 */
class InRule implements RuleInterface
{
    /** @var string[] */
    private array $allowed;

    public function __construct(string ...$values)
    {
        $this->allowed = $values;
    }

    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null) {
            return true; // optional — combine with required to enforce
        }

        return in_array((string)$value, $this->allowed, strict: true);
    }

    public function message(string $field): string
    {
        $list = implode(', ', $this->allowed);
        return "The {$field} field must be one of: {$list}.";
    }
}

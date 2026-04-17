<?php
declare(strict_types=1);

namespace App\Validation;

/**
 * Interface for all validation rules
 */
interface RuleInterface
{
    /**
     * Check if the value passes the rule
     *
     * @param mixed $value
     * @param array $data The entire data being validated (for dependent rules)
     * @return bool
     */
    public function passes(mixed $value, array $data = []): bool;

    /**
     * Get the error message for this rule
     *
     * @param string $field The field name
     * @return string
     */
    public function message(string $field): string;
}

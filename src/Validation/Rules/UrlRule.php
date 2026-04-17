<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

/**
 * Validates that a value is a URL or an allowed relative path.
 * Accepts:
 *   - Full URLs (http/https)
 *   - Relative paths starting with /
 *   - Paths containing uploads/ or images/
 *   - Base64 data URLs (data:image/...)
 *
 * Usage: url
 */
class UrlRule implements RuleInterface
{
    public function passes(mixed $value, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $str = (string)$value;

        // Full valid URL
        if (filter_var($str, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        // Relative path
        if (str_starts_with($str, '/')) {
            return true;
        }

        // Common asset paths
        if (str_contains($str, 'uploads/') || str_contains($str, 'images/')) {
            return true;
        }

        // Base64 data URL
        if (str_starts_with($str, 'data:image/')) {
            return true;
        }

        return false;
    }

    public function message(string $field): string
    {
        return "The {$field} field must be a valid URL or image path.";
    }
}

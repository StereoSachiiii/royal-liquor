<?php
declare(strict_types=1);

namespace App\DTO;

use Exception;

/**
 * Thrown when DTO hydration or validation fails.
 * Carries a field-keyed errors array for API responses.
 */
class DTOException extends Exception
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

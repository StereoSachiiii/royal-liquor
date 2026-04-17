<?php
declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Response Object
 * 
 * Encapsulates HTTP response data and provides convenient response methods
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private mixed $body = null;

    /**
     * Set HTTP status code
     *
     * @param int $code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set a header
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set response body
     *
     * @param mixed $body
     * @return self
     */
    public function setBody(mixed $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get response body
     *
     * @return mixed
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Set JSON response
     *
     * @param mixed $data
     * @param int $statusCode
     * @return self
     */
    public function json(mixed $data, int $statusCode = 200): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        $this->setBody(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $this;
    }

    /**
     * Send the response
     *
     * @return void
     */
    public function send(): void
    {
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Output body
        if ($this->body !== null) {
            echo is_string($this->body) ? $this->body : json_encode($this->body);
        }
    }

    /**
     * Create a success response
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return self
     */
    public static function success(string $message, mixed $data = null, int $code = 200): self
    {
        $response = new self();
        
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return $response->json($payload, $code);
    }

    /**
     * Create an error response
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @return self
     */
    public static function error(string $message, array $errors = [], int $code = 400): self
    {
        $response = new self();
        
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return $response->json($payload, $code);
    }

    /**
     * Create a 404 Not Found response
     *
     * @param string $message
     * @return self
     */
    public static function notFound(string $message = 'Resource not found'): self
    {
        return self::error($message, [], 404);
    }

    /**
     * Create a 401 Unauthorized response
     *
     * @param string $message
     * @return self
     */
    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return self::error($message, [], 401);
    }

    /**
     * Create a 403 Forbidden response
     *
     * @param string $message
     * @return self
     */
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return self::error($message, [], 403);
    }

    /**
     * Create a 500 Server Error response
     *
     * @param string $message
     * @return self
     */
    public static function serverError(string $message = 'Internal server error'): self
    {
        return self::error($message, [], 500);
    }

    /**
     * Create a 201 Created response
     *
     * @param string $message
     * @param mixed $data
     * @return self
     */
    public static function created(string $message, mixed $data = null): self
    {
        return self::success($message, $data, 201);
    }

    /**
     * Create a 204 No Content response
     *
     * @return self
     */
    public static function noContent(): self
    {
        $response = new self();
        return $response->setStatusCode(204);
    }

    /**
     * Create a validation error response
     *
     * @param string $message
     * @param array $errors
     * @return self
     */
    public static function validationError(string $message = 'Validation failed', array $errors = []): self
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Create a conflict response
     *
     * @param string $message
     * @return self
     */
    public static function conflict(string $message): self
    {
        return self::error($message, [], 409);
    }

    /**
     * Create a rate limit exceeded response
     *
     * @param string $message
     * @return self
     */
    public static function tooManyRequests(string $message = 'Too many requests'): self
    {
        return self::error($message, [], 429);
    }

    /**
     * Create a paginated response
     *
     * @param array $items
     * @param int $total
     * @param int $limit
     * @param int $offset
     * @param string $message
     * @return self
     */
    public static function paginated(
        array $items,
        int $total,
        int $limit,
        int $offset,
        string $message = 'Data retrieved successfully'
    ): self {
        $response = new self();
        
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'count' => count($items),
            ],
        ];

        return $response->json($payload, 200);
    }
}

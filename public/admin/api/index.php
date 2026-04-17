<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/Core/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Admin\Middleware\JsonMiddleware;
use App\Admin\Exceptions\BaseException;
use App\Admin\API\ApiServiceProvider;
use App\Admin\API\RouteLoader;

// 1. Initialize System & Container
// $autoloader was initialized in bootstrap.php
$container = new \App\DIContainer\Container();
$provider = new ApiServiceProvider($container);
$provider->register();

// Make container globally available for route files
$GLOBALS['container'] = $container;

// Basic CORS & JSON headers
header('Content-Type: application/json');

// Dynamically handle localhost CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (str_contains($origin, 'localhost')) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header('Access-Control-Allow-Origin: http://localhost');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$request = Request::createFromGlobals();
$router  = new Router();

// 2. Load routes via the namespaced Loader
RouteLoader::load($router);

// Dispatch
try {
    $result = $router->dispatch($request);

    // Route not found
    if ($result === null) {
        JsonMiddleware::sendResponse([
            'success' => false,
            'message' => 'Route not found',
            'data'    => null,
            'errors'  => [],
            'meta'    => null,
            'code'    => 404,
        ], 404);
    }

    // Normalize any array result into a unified structure
    if (is_array($result)) {
        $normalized = [
            'success' => $result['success'] ?? true,
            'message' => $result['message'] ?? 'OK',
            'data'    => $result['data']    ?? ($result['success'] ?? true ? ($result['data'] ?? $result) : null),
            'errors'  => $result['errors']  ?? [],
            'meta'    => $result['meta']    ?? null,
            'code'    => isset($result['code']) && is_int($result['code']) ? $result['code'] : 200,
        ];

        // For non-success without explicit errors but with context, surface context as errors if present
        if ($normalized['success'] === false && empty($normalized['errors']) && isset($result['context']) && is_array($result['context'])) {
            $normalized['errors'] = $result['context'];
        }

        $status = $normalized['code'];
        JsonMiddleware::sendResponse($normalized, $status);
    }

    // Non-array result (unexpected) -> wrap into standard shape
    $payload = [
        'success' => true,
        'message' => 'OK',
        'data'    => $result,
        'errors'  => [],
        'meta'    => null,
        'code'    => 200,
    ];
    JsonMiddleware::sendResponse($payload, 200);
} catch (Throwable $e) {
    // Use domain exception info when available
    if ($e instanceof BaseException) {
        $code    = $e->getStatusCode();
        $context = $e->getContext();
        $code    = ($code >= 400 && $code < 600) ? $code : 500;

        $errorPayload = [
            'success' => false,
            'message' => $e->getMessage(),
            'data'    => null,
            'errors'  => $context,
            'meta'    => null,
            'code'    => $code,
        ];

        JsonMiddleware::sendResponse($errorPayload, $code);
    }

    // Fallback for non-domain exceptions
    $code = $e->getCode() ?: 500;
    $code = ($code >= 400 && $code < 600) ? $code : 500;

    $errorPayload = [
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => null,
        'errors'  => [],
        'meta'    => null,
        'code'    => $code,
    ];

    JsonMiddleware::sendResponse($errorPayload, $code);
}

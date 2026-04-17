<?php
declare(strict_types=1);

namespace App\Admin\API\Routes;

use App\Core\Request;
use App\Core\Session;
use App\Admin\Controllers\CartController;
use App\Admin\Middleware\RateLimitMiddleware;
use App\Admin\Middleware\AuthMiddleware;
use App\Admin\Middleware\CsrfMiddleware;
use App\Core\Router;

/** @var Router $router */

$router->group('/api/v1', function (Router $router): void {
    // Admin list of all carts
    $router->get('/carts', function (Request $request): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        // Only when no specific filters are applied (same as original condition)
        AuthMiddleware::requireAdmin();
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        $search = $request->getQuery('search', '');
        
        if ($search) {
            return $cartController->search($search, $limit, $offset);
        }
        return $cartController->getAllEnriched($limit, $offset);
    });

    // Get cart by ID (enriched with user + items)
    $router->get('/carts/:id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart ID required',
                'code'    => 400,
            ];
        }
        return $cartController->getByIdEnriched($id);
    });

    // Active cart by user ID
    $router->get('/carts/by-user/:user_id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        $userId     = (int)($params['user_id'] ?? 0);
        return $cartController->getActiveByUser($userId);
    });

    // Active cart by session ID
    $router->get('/carts/by-session/:session_id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        $sessionId  = (string)($params['session_id'] ?? '');
        return $cartController->getActiveBySession($sessionId);
    });

    // Count carts
    $router->get('/carts/count', function (Request $request): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        AuthMiddleware::requireAdmin();
        return $cartController->count();
    });

    // Create cart
    $router->post('/carts', function (Request $request): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_create', 10, 60);

        $body       = $request->getAllBody();
        return $cartController->create($body);
    });

    // Update cart
    $router->put('/carts/:id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_update', 10, 60);

        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart ID required',
                'code'    => 400,
            ];
        }

        return $cartController->update($id, $body);
    });

    // Delete cart
    $router->delete('/carts/:id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_delete', 5, 60);

        $id         = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart ID required',
                'code'    => 400,
            ];
        }

        return $cartController->delete($id);
    });
});

<?php
declare(strict_types=1);

namespace App\Admin\API\Routes;

use App\Core\Request;
use App\Core\Session;
use App\Admin\Controllers\OrderController;
use App\Admin\Middleware\RateLimitMiddleware;
use App\Admin\Middleware\AuthMiddleware;
use App\Admin\Middleware\CsrfMiddleware;
use App\Core\Router;

/** @var Router $router */

$router->group('/api/v1', function (Router $router): void {
    // Admin list of all orders (with optional search)
    $router->get('/orders', function (Request $request): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        AuthMiddleware::requireAdmin();
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        $search = trim((string)$request->getQuery('search', ''));
        
        // If search provided, search by order number
        if ($search !== '') {
            return $orderController->search($search, $limit, $offset);
        }
        return $orderController->getAll($limit, $offset);
    });

    // Single order by ID
    $router->get('/orders/:id', function (Request $request, array $params): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Order ID required',
                'code'    => 400,
            ];
        }
        return $orderController->getById($id);
    });

    // Detailed/enriched order by ID
    $router->get('/orders/:id/enriched', function (Request $request, array $params): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Order ID required',
                'code'    => 400,
            ];
        }
        return $orderController->getDetailedOrderById($id);
    });

    // Order by order number
    $router->get('/orders/by-number/:order_number', function (Request $request, array $params): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        $orderNumber  = (string)($params['order_number'] ?? '');
        return $orderController->getByOrderNumber($orderNumber);
    });

    // Orders by user
    $router->get('/orders/by-user/:user_id', function (Request $request, array $params): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        $userId     = (int)($params['user_id'] ?? 0);
        $limit      = (int)$request->getQuery('limit', 50);
        $offset     = (int)$request->getQuery('offset', 0);
        return $orderController->getByUser($userId, $limit, $offset);
    });

    // Count orders
    $router->get('/orders/count', function (Request $request): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        AuthMiddleware::requireAdmin();
        return $orderController->count();
    });

    // Create order
    $router->post('/orders', function (Request $request): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('order_create', 5, 60);

        $body       = $request->getAllBody();
        return $orderController->create($body);
    });

    // Cancel order (action endpoint)
    $router->post('/orders/:id/cancel', function (Request $request, array $params): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('order_create', 5, 60);

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Order ID required for cancellation',
                'code'    => 400,
            ];
        }

        AuthMiddleware::requireAuth();
        return $orderController->cancel($id);
    });

    // Update order (admin)
    $router->put('/orders/:id', function (Request $request, array $params): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('order_update', 5, 60);

        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Order ID required',
                'code'    => 400,
            ];
        }

        return $orderController->update($id, $body);
    });

    // Delete order (admin)
    $router->delete('/orders/:id', function (Request $request, array $params): array {
        $orderController = $GLOBALS['container']->get(OrderController::class);
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('order_delete', 5, 60);

        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Order ID required',
                'code'    => 400,
            ];
        }

        return $orderController->delete($id);
    });
});

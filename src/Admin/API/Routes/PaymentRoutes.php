<?php
declare(strict_types=1);

namespace App\Admin\API\Routes;

use App\Core\Request;
use App\Core\Session;
use App\Admin\Controllers\PaymentController;
use App\Admin\Middleware\RateLimitMiddleware;
use App\Admin\Middleware\AuthMiddleware;
use App\Admin\Middleware\CsrfMiddleware;
use App\Core\Router;

/** @var Router $router */

$router->group('/api/v1', function (Router $router): void {
    // Search payments - MUST be before :id routes
    $router->get('/payments/search', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('payment_search', 30, 60);
        $controller = $GLOBALS['container']->get(PaymentController::class);
        $query  = (string)$request->getQuery('search', '');
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        return $controller->search($query, $limit, $offset);
    });

    // Admin list of payments
    $router->get('/payments', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        $controller = $GLOBALS['container']->get(PaymentController::class);
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        return $controller->getAll($limit, $offset);
    });

    // Get payment by ID
    $router->get('/payments/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        $controller = $GLOBALS['container']->get(PaymentController::class);
        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Payment ID required',
                'code'    => 400,
            ];
        }
        return $controller->getById($id);
    });

    // Get payments by order ID
    $router->get('/payments/order/:order_id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        $controller = $GLOBALS['container']->get(PaymentController::class);
        $orderId    = (int)($params['order_id'] ?? 0);
        return $controller->getByOrder($orderId);
    });

    // Count payments
    $router->get('/payments/count', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        $controller = $GLOBALS['container']->get(PaymentController::class);
        return $controller->count();
    });

    // Create payment
    $router->post('/payments', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('payment_create', 5, 60);

        $controller = $GLOBALS['container']->get(PaymentController::class);
        $body       = $request->getAllBody();
        return $controller->create($body);
    });

    // Update payment
    $router->put('/payments/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('payment_update', 5, 60);

        $controller = $GLOBALS['container']->get(PaymentController::class);
        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Payment ID required',
                'code'    => 400,
            ];
        }

        return $controller->update($id, $body);
    });

    // Delete / hard-delete payment
    $router->delete('/payments/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('payment_delete', 5, 60);

        $controller = $GLOBALS['container']->get(PaymentController::class);
        $id         = (int)($params['id'] ?? 0);
        $hard       = $request->getQuery('hard') === 'true';

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Payment ID required',
                'code'    => 400,
            ];
        }

        if ($hard) {
            RateLimitMiddleware::check('payment_hard_delete', 2, 60);
            return $controller->hardDelete($id);
        }

        return $controller->delete($id);
    });
});

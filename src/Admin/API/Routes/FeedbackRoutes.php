<?php
declare(strict_types=1);

namespace App\Admin\API\Routes;

use App\Core\Request;
use App\Core\Session;
use App\Admin\Controllers\FeedbackController;
use App\Admin\Middleware\RateLimitMiddleware;
use App\Admin\Middleware\AuthMiddleware;
use App\Admin\Middleware\CsrfMiddleware;
use App\Core\Router;

/** @var Router $router */

// Guard against direct access - must be loaded via router
if (!isset($router) || !$router instanceof Router) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed.']);
    exit;
}

$router->group('/api/v1', function (Router $router): void {
    // GET /api/v1/feedback (list all with enriched data)
    $router->get('/feedback', function (Request $request): array {
        $controller = $GLOBALS['container']->get(FeedbackController::class);

        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        $isActive = $request->getQuery('isActive') !== null ? $request->getQuery('isActive') === 'true' : null;

        // Always use paginated/enriched version for admin
        RateLimitMiddleware::check('feedback_getAll', 5, 60);
        return $controller->getAllPaginated($limit, $offset, $isActive);
    });

    // GET /api/v1/feedback/paginated
    $router->get('/feedback/paginated', function (Request $request): array {
        RateLimitMiddleware::check('feedback_getAllPaginated', 5, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $limit      = (int)$request->getQuery('limit', 50);
        $offset     = (int)$request->getQuery('offset', 0);
        $isActive   = $request->getQuery('isActive') !== null ? $request->getQuery('isActive') === 'true' : null;
        return $controller->getAllPaginated($limit, $offset, $isActive);
    });

    // GET /api/v1/feedback/:id
    $router->get('/feedback/:id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('feedback_getById', 10, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID required',
                'code'    => 400,
            ];
        }
        return $controller->getById($id);
    });

    // GET /api/v1/feedback/product/:product_id
    $router->get('/feedback/product/:product_id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('feedback_getByProductId', 10, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $productId  = (int)($params['product_id'] ?? 0);
        return $controller->getByProductId($productId);
    });

    // GET /api/v1/feedback/user/:user_id
    $router->get('/feedback/user/:user_id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('feedback_getByUserId', 10, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $userId     = (int)($params['user_id'] ?? 0);
        return $controller->getByUserId($userId);
    });

    // GET /api/v1/feedback/product/:product_id/avg-rating
    $router->get('/feedback/product/:product_id/avg-rating', function (Request $request, array $params): array {
        RateLimitMiddleware::check('feedback_getAvgRating', 10, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $productId  = (int)($params['product_id'] ?? 0);
        return $controller->getAverageRating($productId);
    });

    // POST /api/v1/feedback
    $router->post('/feedback', function (Request $request): array {
        RateLimitMiddleware::check('feedback_create', 5, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $body       = $request->getAllBody();
        $result     = $controller->create($body);
        $result['code'] = $result['code'] ?? 201;
        return $result;
    });

    // PUT /api/v1/feedback/:id
    $router->put('/feedback/:id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('feedback_update', 10, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));
        
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID required for update',
                'code'    => 400,
            ];
        }

        return $controller->update($id, $body);
    });

    // DELETE /api/v1/feedback/:id
    $router->delete('/feedback/:id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('feedback_delete', 5, 60);
        $controller = $GLOBALS['container']->get(FeedbackController::class);
        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));
        
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID required for delete',
                'code'    => 400,
            ];
        }

        $hard = $request->getQuery('hard') === 'true' || (!empty($body['hard']) && (bool)$body['hard'] === true);

        if ($hard) {
            return $controller->hardDelete($id);
        }

        return $controller->delete($id);
    });
});

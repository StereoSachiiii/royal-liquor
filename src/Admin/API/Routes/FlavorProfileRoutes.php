<?php
declare(strict_types=1);

namespace App\Admin\API\Routes;

use App\Core\Request;
use App\Core\Session;
use App\Admin\Controllers\FlavorProfileController;
use App\Admin\Middleware\RateLimitMiddleware;
use App\Admin\Middleware\AuthMiddleware;
use App\Admin\Middleware\CsrfMiddleware;
use App\Core\Router;

/** @var Router $router */

$router->group('/api/v1', function (Router $router): void {
    // List all flavor profiles (admin only)
    $router->get('/flavor-profiles', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        $controller = $GLOBALS['container']->get(FlavorProfileController::class);
        $limit      = (int)$request->getQuery('limit', 50);
        $offset     = (int)$request->getQuery('offset', 0);
        $search     = $request->getQuery('search');

        if ($search) {
            return $controller->search($search, $limit, $offset);
        }

        return $controller->getAll($limit, $offset);
    });

    // Get flavor profile by product ID
    $router->get('/flavor-profiles/product/:product_id', function (Request $request, array $params): array {
        $controller = $GLOBALS['container']->get(FlavorProfileController::class);
        $id         = (int)($params['product_id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Product ID required',
                'code'    => 400,
            ];
        }
        return $controller->getByProductId($id);
    });

    // Count flavor profiles (admin only)
    $router->get('/flavor-profiles/count', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        $controller = $GLOBALS['container']->get(FlavorProfileController::class);
        return $controller->count();
    });

    // Create flavor profile (admin only)
    $router->post('/flavor-profiles', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('flavor_profile_create', 5, 60);

        $controller = $GLOBALS['container']->get(FlavorProfileController::class);
        $body       = $request->getAllBody();
        return $controller->create($body);
    });

    // Update flavor profile by product ID (admin only)
    $router->put('/flavor-profiles/product/:product_id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('flavor_profile_update', 5, 60);

        $controller = $GLOBALS['container']->get(FlavorProfileController::class);
        $body       = $request->getAllBody();
        $id         = (int)($params['product_id'] ?? ($body['product_id'] ?? 0));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Product ID required',
                'code'    => 400,
            ];
        }

        return $controller->update($id, $body);
    });

    // Delete flavor profile by product ID (admin only)
    $router->delete('/flavor-profiles/product/:product_id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('flavor_profile_delete', 5, 60);

        $controller = $GLOBALS['container']->get(FlavorProfileController::class);
        $id         = (int)($params['product_id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Product ID required',
                'code'    => 400,
            ];
        }

        return $controller->delete($id);
    });
});

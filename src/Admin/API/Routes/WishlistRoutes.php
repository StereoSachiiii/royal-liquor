<?php
declare(strict_types=1);

namespace App\Admin\API\Routes;

use App\Core\Request;
use App\Core\Router;
use App\Admin\Controllers\WishlistController;
use App\Admin\Middleware\AuthMiddleware;
use App\Admin\Middleware\RateLimitMiddleware;

$router->group('/api/v1', function (Router $router): void {

    // GET /api/v1/wishlists
    $router->get('/wishlists', function (Request $request): void {
        AuthMiddleware::requireAuth();
        RateLimitMiddleware::check('wishlist_get', 60, 60);

        $controller = $GLOBALS['container']->get(WishlistController::class);
        $controller->getMine();
    });

    // POST /api/v1/wishlists
    $router->post('/wishlists', function (Request $request): void {
        AuthMiddleware::requireAuth();
        RateLimitMiddleware::check('wishlist_post', 30, 60);

        $controller = $GLOBALS['container']->get(WishlistController::class);
        $controller->add();
    });

    // DELETE /api/v1/wishlists/:product_id
    $router->delete('/wishlists/:product_id', function (Request $request, array $params): void {
        AuthMiddleware::requireAuth();
        RateLimitMiddleware::check('wishlist_delete', 30, 60);

        $productId = (int)($params['product_id'] ?? 0);
        $controller = $GLOBALS['container']->get(WishlistController::class);
        $controller->remove($productId);
    });

    // POST /api/v1/wishlists/sync
    $router->post('/wishlists/sync', function (Request $request): void {
        AuthMiddleware::requireAuth();
        RateLimitMiddleware::check('wishlist_post', 10, 60);

        $controller = $GLOBALS['container']->get(WishlistController::class);
        $controller->syncBulk();
    });

});

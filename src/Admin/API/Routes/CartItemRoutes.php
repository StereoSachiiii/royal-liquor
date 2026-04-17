<?php
declare(strict_types=1);

namespace App\Admin\API\Routes;

use App\Core\Request;
use App\Core\Session;
use App\Admin\Controllers\CartItemController;
use App\Admin\Middleware\RateLimitMiddleware;
use App\Admin\Middleware\AuthMiddleware;
use App\Admin\Middleware\CsrfMiddleware;
use App\Core\Router;

/** @var Router $router */

$router->group('/api/v1', function (Router $router): void {
    // Admin list of cart items
    $router->get('/cart-items', function (Request $request): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        AuthMiddleware::requireAdmin();
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        $search = $request->getQuery('search', '');
        
        if ($search) {
            return $cartItemController->search($search, $limit, $offset);
        }
        return $cartItemController->getAllEnriched($limit, $offset);
    });

    // Get cart item by ID (enriched with product + cart data)
    $router->get('/cart-items/:id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart item ID required',
                'code'    => 400,
            ];
        }
        return $cartItemController->getByIdEnriched($id);
    });

    // Get cart item by cart and product
    $router->get('/cart-items/cart/:cart_id/product/:product_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        $cartId     = (int)($params['cart_id'] ?? 0);
        $productId  = (int)($params['product_id'] ?? 0);
        return $cartItemController->getByCartProduct($cartId, $productId);
    });

    // Get all items for a cart
    $router->get('/cart-items/cart/:cart_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        $cartId     = (int)($params['cart_id'] ?? 0);
        return $cartItemController->getByCart($cartId);
    });

    // Count cart items
    $router->get('/cart-items/count', function (Request $request): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        AuthMiddleware::requireAdmin();
        return $cartItemController->count();
    });

    // Create cart item
    $router->post('/cart-items', function (Request $request): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_create', 20, 60);

        $body       = $request->getAllBody();
        return $cartItemController->create($body);
    });

    // Update cart item
    $router->put('/cart-items/:id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_update', 20, 60);

        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart item ID required',
                'code'    => 400,
            ];
        }

        return $cartItemController->update($id, $body);
    });

    // Update by cart + product
    $router->put('/cart-items/cart/:cart_id/product/:product_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_update', 20, 60);

        $body       = $request->getAllBody();
        $cartId     = (int)($params['cart_id'] ?? 0);
        $productId  = (int)($params['product_id'] ?? 0);

        return $cartItemController->updateByCartProduct($cartId, $productId, $body);
    });

    // Delete cart item
    $router->delete('/cart-items/:id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_delete', 10, 60);

        $id         = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart item ID required',
                'code'    => 400,
            ];
        }

        return $cartItemController->delete($id);
    });

    // Delete by cart + product
    $router->delete('/cart-items/cart/:cart_id/product/:product_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_delete', 10, 60);

        $cartId     = (int)($params['cart_id'] ?? 0);
        $productId  = (int)($params['product_id'] ?? 0);

        return $cartItemController->deleteByCartProduct($cartId, $productId);
    });
});

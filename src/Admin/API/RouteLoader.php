<?php
declare(strict_types=1);

namespace App\Admin\API;

use App\Core\Router;

/**
 * RouteLoader
 *
 * Automatically discovers and includes all Admin API route definition files.
 */
class RouteLoader
{
    /**
     * Load all modules into the provided router
     * 
     * @param Router $router The router instance to register routes on
     */
    public static function load(Router $router): void
    {
        $routesDir = __DIR__ . '/Routes';
        
        // Define the mapping from old filenames to new PascalCase names
        // This ensures the loader finds the renamed files
        $modules = [
            'UserRoutes.php',
            'ProductRoutes.php', 
            'CategoryRoutes.php',
            'OrderRoutes.php',
            'OrderItemRoutes.php',
            'StockRoutes.php',
            'WishlistRoutes.php',
            'RecommendationRoutes.php',
            'CartRoutes.php',
            'CartItemRoutes.php',
            'PaymentRoutes.php',
            'WarehouseRoutes.php',
            'SupplierRoutes.php',
            'AddressRoutes.php',
            'FlavorProfileRoutes.php',
            'FeedbackRoutes.php',
            'CocktailRecipeRoutes.php',
            'RecipeIngredientRoutes.php',
            'UserPreferenceRoutes.php',
            'AdminViewRoutes.php',
            'ImageRoutes.php'
        ];
        
        foreach ($modules as $file) {
            $path = $routesDir . '/' . $file;
            if (file_exists($path)) {
                // We use require instead of require_once to ensure the 
                // $router variable is available in the route file's scope
                require $path;
            }
        }
    }
}

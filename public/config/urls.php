<?php
/**
 * Centralized URL Configuration
 * All URLs should be defined here for easy maintenance
 */

// 1. Base URL - Detects environment (Docker vs Subfolder)
// Respects definition from bootstrap.php if included first
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

// 2. Asset URL - Root for images, JS, and CSS
if (!defined('ASSET_URL')) {
    define('ASSET_URL', BASE_URL . 'assets/');
}

// 3. API Base URL - Gateway for modern v1 routes
if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', BASE_URL . 'api/v1');
}

// API Endpoints - Mapping to the new Router-based endpoints
define('API_ENDPOINTS', [
    'products'       => API_BASE_URL . '/products',
    'categories'     => API_BASE_URL . '/categories',
    'cart'           => API_BASE_URL . '/cart',
    'cart_items'     => API_BASE_URL . '/cart/items',
    'orders'         => API_BASE_URL . '/orders',
    'order_items'    => API_BASE_URL . '/orders/items',
    'addresses'      => API_BASE_URL . '/addresses',
    'users'          => API_BASE_URL . '/users',
    'wishlist'       => API_BASE_URL . '/wishlist',
    'feedback'       => API_BASE_URL . '/feedback',
    'payments'       => API_BASE_URL . '/payments',
    'stock'          => API_BASE_URL . '/stock',
    'suppliers'      => API_BASE_URL . '/suppliers',
    'flavor_profile' => API_BASE_URL . '/flavor-profile',
]);

// Page URLs (for redirects and navigation)
define('PAGE_URLS', [
    // Main pages
    'home' => BASE_URL,
    'shop' => BASE_URL . 'shop.php',
    'product' => BASE_URL . 'product.php',
    'checkout' => BASE_URL . 'checkout.php',
    'search' => BASE_URL . 'search.php',
    'cart' => BASE_URL . 'cart.php',
    'category' => BASE_URL . 'category.php',
    'feedback' => BASE_URL . 'feedback.php',
    
    // Static pages
    'about' => BASE_URL . 'about.php',
    'contact' => BASE_URL . 'contact.php',
    'faq' => BASE_URL . 'faq.php',
    
    // MyAccount pages (clean .php routes)
    'account' => BASE_URL . 'myaccount/',
    'orders' => BASE_URL . 'myaccount/orders.php',
    'wishlist' => BASE_URL . 'myaccount/wishlist.php',
    'addresses' => BASE_URL . 'myaccount/addresses.php',
    'settings' => BASE_URL . 'myaccount/settings.php',
    'logout' => BASE_URL . 'myaccount/logout.php',
    
    // Auth
    'login' => BASE_URL . 'auth.php',
]);

/**
 * Helper function to get API endpoint URL
 * @param string $endpoint - Endpoint name from API_ENDPOINTS
 * @return string - Full API URL
 */
function getApiUrl($endpoint) {
    if (!isset(API_ENDPOINTS[$endpoint])) {
        throw new Exception("Unknown API endpoint: $endpoint");
    }
    return API_ENDPOINTS[$endpoint];
}

/**
 * Helper function to get page URL
 * @param string $page - Page name from PAGE_URLS
 * @return string - Full page URL
 */
function getPageUrl($page) {
    if (!isset(PAGE_URLS[$page])) {
        throw new Exception("Unknown page: $page");
    }
    return PAGE_URLS[$page];
}

<?php
/**
 * Page Assets Configuration
 * Defines which CSS and JS files each page needs
 * Optimizes loading by only including necessary assets
 */

// CSS files mapping for each page type
$PAGE_CSS = [
    'home' => ['header', 'footer', 'products', 'categories', 'popup'],
    'shop' => ['header', 'footer', 'products', 'details', 'popup', 'shop'],
    'cart' => ['header', 'footer', 'cart', 'popup'],
    'product' => ['header', 'footer', 'products', 'details', 'popup', 'product'],
    'category' => ['header', 'footer', 'categories', 'products', 'popup'],
    'about' => ['header', 'footer'],
    'contact' => ['header', 'footer'],
    'faq' => ['header', 'footer'],
    // MyAccount pages (layout handles header/footer)
    'account' => ['header', 'footer'],
    'orders' => ['header', 'footer'],
    'wishlist' => ['header', 'footer'],
    'addresses' => ['header', 'footer'],
    'settings' => ['header', 'footer'],
    'auth' => ['popup'],
    'feedback' => ['header', 'footer'],
];

// JS files mapping for each page type
$PAGE_JS = [
    'home' => ['header/header'],
    'shop' => ['header/header', 'pages/shop'],
    'cart' => ['header/header', 'pages/cart'],
    'product' => ['header/header', 'pages/product'],
    'category' => ['header/header', 'pages/category'],
    'account' => ['header/header'],
    'orders' => ['header/header'],
    'wishlist' => ['header/header'],
    'feedback' => ['header/header', 'pages/feedback'],
    'contact' => ['header/header', 'pages/contact'],
];

/**
 * Load CSS files for a specific page
 * @param string $pageName - Page identifier (e.g., 'home', 'shop', 'cart')
 */
function loadPageCSS($pageName = 'home') {
    // Load the official Compiled Tailwind CSS (Consolidated legacy manual files)
    echo "    <link rel=\"stylesheet\" href=\"" . BASE_URL . "css/main.css\">\n";
}


/**
 * Load JS files for a specific page
 * @param string $pageName - Page identifier
 */
function loadPageJS($pageName = 'home') {
    global $PAGE_JS;
    
    // Get JS files for this page
    $jsFiles = $PAGE_JS[$pageName] ?? $PAGE_JS['home'];
    
    // Output script tags
    foreach ($jsFiles as $file) {
        echo "    <script src=\"" . BASE_URL . "assets/js/{$file}.js\" type=\"module\"></script>\n";
    }
}

/**
 * Get page name from current URL
 * @return string - Page identifier
 */
function getCurrentPageName() {
    $script = basename($_SERVER['SCRIPT_NAME'], '.php');
    
    // Map script names to page identifiers
    $pageMap = [
        'index' => 'home',
        'shop' => 'shop',
        'cart' => 'cart',
        'category' => 'category',
        'product' => 'product',
        'profile' => 'profile',
        'auth' => 'auth',
        'feedback' => 'feedback',
    ];
    
    // Check if in myaccount directory
    if (strpos($_SERVER['SCRIPT_NAME'], 'myaccount') !== false) {
        if (strpos($_SERVER['SCRIPT_NAME'], 'orders') !== false) {
            return 'orders';
        }
        if (strpos($_SERVER['SCRIPT_NAME'], 'wishlist') !== false) {
            return 'wishlist';
        }
        if (strpos($_SERVER['SCRIPT_NAME'], 'profile') !== false) {
            return 'profile';
        }
        return 'account';
    }
    
    return $pageMap[$script] ?? 'home';
}

/**
 * Helper to load all page assets (CSS + JS)
 * @param string $pageName - Page identifier (optional, auto-detected if not provided)
 */
function loadPageAssets($pageName = null) {
    $pageName = $pageName ?? getCurrentPageName();
    loadPageCSS($pageName);
    loadPageJS($pageName);
}

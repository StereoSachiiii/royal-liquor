<?php 
require_once dirname(__DIR__) . '/config/urls.php';
require_once dirname(__DIR__, 2) . '/src/Core/bootstrap.php';
require_once dirname(__DIR__) . '/components/header.php';

$session = Session::getInstance();
$userId = $session->getUserId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, device-scale=1.0">
    <title>Shopping Cart | Royal Liquor</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/favicon.png">
    
</head>
<body data-user-id="<?= $userId ?? 'null' ?>">

<div class="max-w-[1400px] mx-auto px-8 md:px-16 py-12 font-sans">
    <!-- Header -->
    <div class="text-center mb-12 pb-8 border-b-2 border-gray-100">
        <h1 class="text-4xl md:text-5xl font-extrabold uppercase tracking-widest text-black mb-4 italic">
            Shopping Cart
            <span class="ml-3 px-3 py-1 bg-gray-100 rounded-full text-xs font-black text-gray-500 uppercase tracking-widest" id="cart-item-count">0 items</span>
        </h1>
        <p class="text-xs uppercase tracking-[0.3em] font-black text-gray-400 italic">Review your selection before checkout</p>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-16 items-start">
        <!-- Cart Items Section -->
        <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
            <div class="flex flex-col gap-6 cart-items-list">
                <!-- Items will be rendered here by JavaScript -->
            </div>
        </div>

        <!-- Summary Panel -->
        <div class="sticky top-8 bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
            <h2 class="text-xs uppercase tracking-[0.3em] font-black border-b border-black pb-4 mb-8">Order Summary</h2>
            
            <div class="flex justify-between mb-4 text-sm font-bold uppercase tracking-widest text-gray-400">
                <span>Subtotal:</span>
                <span class="text-black" id="summary-subtotal">$0.00</span>
            </div>
            
            <div class="flex justify-between mb-4 text-sm font-bold uppercase tracking-widest text-gray-400">
                <span>Tax (10%):</span>
                <span class="text-black" id="summary-tax">$0.00</span>
            </div>
            
            <div class="flex justify-between mb-8 text-sm font-bold uppercase tracking-widest text-gray-400">
                <span>Shipping:</span>
                <span class="text-black" id="summary-shipping">$0.00</span>
            </div>
            
            <div class="flex justify-between border-t border-gray-100 pt-6 mb-8 text-lg font-black uppercase tracking-[0.2em]">
                <span>Total:</span>
                <span id="summary-total">$0.00</span>
            </div>
            
            <button id="checkout-btn" class="w-full h-14 bg-black text-white text-[11px] font-black uppercase tracking-widest hover:bg-gray-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                Proceed to Checkout
            </button>
            
            <button id="clear-cart-btn" class="w-full h-12 mt-4 border border-gray-200 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-all text-red-600 hover:text-red-700 hover:border-red-200 cursor-pointer">
                Clear Cart
            </button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container"></div>

<!-- Load JavaScript -->
<script type="module" src="<?= BASE_URL ?>assets/js/pages/cart.js"></script>

<?php require_once dirname(__DIR__) . '/components/footer.php'; ?>

</body>
</html>

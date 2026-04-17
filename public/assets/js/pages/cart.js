/**
 * Improved Cart Page JavaScript
 * Extracted and refactored with better UX
 */

import { cart } from '../cart-service.js';

// DOM Elements
const cartItemsList = document.querySelector('.cart-items-list');
const cartSubtotal = document.getElementById('cart-subtotal');
const cartItemCount = document.getElementById('cart-item-count');
const checkoutBtn = document.getElementById('checkout-btn');
const clearCartBtn = document.getElementById('clear-cart-btn');
const toastContainer = document.querySelector('.toast-container');

// State
let currentCart = [];
let isLoading = false;

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <span>${message}</span>
    `;

    toastContainer.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Format price
 */
function formatPrice(cents) {
    return `Rs. ${(cents / 100).toFixed(2)}`;
}

/**
 * Render cart item HTML
 */
function renderCartItem(item) {
    const itemTotal = item.price_cents * item.quantity;

    return `
        <div class="grid grid-cols-[100px_1fr_auto] gap-6 p-6 bg-white border border-gray-100 rounded-2xl hover:border-gray-300 transition-all group" data-item-id="${item.id}">
            <img src="${item.image_url || '/placeholder.jpg'}" alt="${item.name}" class="w-24 h-24 object-contain bg-gray-50 rounded-lg p-2">
            
            <div class="flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-black mb-1">${item.name}</h3>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        ${formatPrice(item.price_cents)} <span class="italic opacity-60">each</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <button class="w-8 h-8 flex items-center justify-center border border-gray-200 bg-white rounded text-xs font-black hover:bg-black hover:text-white transition-all disabled:opacity-30 disabled:cursor-not-allowed decrease-btn" data-item-id="${item.id}" ${item.quantity <= 1 ? 'disabled' : ''}>
                        −
                    </button>
                    <span class="text-xs font-black w-6 text-center">${item.quantity}</span>
                    <button class="w-8 h-8 flex items-center justify-center border border-gray-200 bg-white rounded text-xs font-black hover:bg-black hover:text-white transition-all increase-btn" data-item-id="${item.id}">
                        +
                    </button>
                </div>
            </div>
            
            <div class="flex flex-col items-end justify-between">
                <div class="text-lg font-black tracking-tight text-black">${formatPrice(itemTotal)}</div>
                <button class="text-[9px] font-black uppercase tracking-[0.2em] text-red-400 hover:text-red-600 transition-colors remove-item-btn" data-item-id="${item.id}">
                    Remove
                </button>
            </div>
        </div>
    `;
}

/**
 * Render empty cart state
 */
function renderEmptyCart() {
    return `
        <div class="text-center py-20 bg-gray-50 rounded-2xl flex flex-col items-center">
            <div class="text-6xl mb-6 grayscale opacity-20">🛒</div>
            <h3 class="text-xs uppercase tracking-[0.3em] font-black mb-4">Your cart is empty</h3>
            <p class="text-sm italic text-gray-400 mb-8 font-light">Add some products to get started</p>
            <a href="shop.php" class="bg-black text-white px-12 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 transition-all">Continue Shopping</a>
        </div>
    `;
}

/**
 * Calculate cart totals
 */
function calculateTotals(cart) {
    const subtotal = cart.reduce((total, item) => total + (item.price_cents * item.quantity), 0);
    const tax = Math.round(subtotal * 0.1); // 10% tax
    const shipping = subtotal > 5000 ? 0 : 500; // Free shipping over Rs. 5000
    const total = subtotal + tax + shipping;

    return { subtotal, tax, shipping, total };
}

/**
 * Update summary panel
 */
function updateSummary(cart) {
    const { subtotal, tax, shipping, total } = calculateTotals(cart);

    document.getElementById('summary-subtotal').textContent = formatPrice(subtotal);
    document.getElementById('summary-tax').textContent = formatPrice(tax);
    document.getElementById('summary-shipping').textContent = shipping === 0 ? 'FREE' : formatPrice(shipping);
    document.getElementById('summary-total').textContent = formatPrice(total);

    // Update item count
    const itemCount = cart.reduce((count, item) => count + item.quantity, 0);
    if (cartItemCount) {
        cartItemCount.textContent = `${itemCount} item${itemCount !== 1 ? 's' : ''}`;
    }
}

/**
 * Render cart
 */
function renderCart() {
    const items = cart.getItems();
    currentCart = items;

    if (!items || items.length === 0) {
        cartItemsList.innerHTML = renderEmptyCart();
        updateSummary([]);
        checkoutBtn.disabled = true;
        return;
    }

    cartItemsList.innerHTML = items.map(renderCartItem).join('');
    updateSummary(items);
    checkoutBtn.disabled = false;

    // Attach event listeners
    attachItemEventListeners();
}

/**
 * Attach event listeners to cart items
 */
function attachItemEventListeners() {
    // Remove buttons
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', handleRemoveItem);
    });

    // Quantity buttons
    document.querySelectorAll('.decrease-btn').forEach(btn => {
        btn.addEventListener('click', handleDecreaseQuantity);
    });

    document.querySelectorAll('.increase-btn').forEach(btn => {
        btn.addEventListener('click', handleIncreaseQuantity);
    });
}

/**
 * Handle remove item
 */
async function handleRemoveItem(e) {
    const itemId = e.target.dataset.itemId;

    if (confirm('Remove this item from your cart?')) {
        cart.remove(itemId);
        renderCart();
        showToast('Item removed from cart', 'success');
    }
}

/**
 * Handle decrease quantity
 */
async function handleDecreaseQuantity(e) {
    const itemId = e.target.dataset.itemId;
    const item = currentCart.find(i => i.id === parseInt(itemId));

    if (item && item.quantity > 1) {
        cart.updateQuantity(itemId, item.quantity - 1);
        renderCart();
    }
}

/**
 * Handle increase quantity
 */
async function handleIncreaseQuantity(e) {
    const itemId = e.target.dataset.itemId;
    const item = currentCart.find(i => i.id === parseInt(itemId));

    if (item) {
        cart.updateQuantity(itemId, item.quantity + 1);
        renderCart();
    }
}

/**
 * Handle clear cart
 */
async function handleClearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        cart.clear();
        renderCart();
        showToast('Cart cleared', 'success');
    }
}

/**
 * Handle checkout
 */
async function handleCheckout() {
    const items = cart.getItems();

    if (!items || items.length === 0) {
        showToast('Your cart is empty', 'warning');
        return;
    }

    // Check if user is logged in
    const userId = document.body.dataset.userId;
    if (!userId || userId === 'null') {
        showToast('Please log in to checkout', 'warning');
        setTimeout(() => {
            window.location.href = 'auth/auth.php';
        }, 1500);
        return;
    }

    // Redirect to checkout page (to be implemented)
    showToast('Proceeding to checkout...', 'success');
    // TODO: Implement checkout flow
}

/**
 * Initialize cart page
 */
async function init() {
    // Show loading state
    cartItemsList.innerHTML = `
        <div class="cart-loading">
            <div class="loading-spinner"></div>
            <p>Loading your cart...</p>
        </div>
    `;

    // Small delay for better UX
    await new Promise(resolve => setTimeout(resolve, 300));

    // Render cart
    renderCart();

    // Attach global event listeners
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', handleCheckout);
    }

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', handleClearCart);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Export functions for external use
export { renderCart, showToast };

/**
 * Cart Slide-in Preview
 * Shows a slide-in panel when items are added to cart
 */

let slideInTimeout = null;

// DOM ready
const initCartSlideIn = () => {
    // Create slide-in container if not exists
    if (!document.getElementById('cartSlideIn')) {
        const slideIn = document.createElement('div');
        slideIn.id = 'cartSlideIn';
        slideIn.className = 'cart-slide-in';
        slideIn.innerHTML = `
            <div class="slide-in-header">
                <div class="slide-in-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Added to Cart</span>
                </div>
                <button class="slide-in-close" id="slideInClose">×</button>
            </div>
            <div class="slide-in-content" id="slideInContent"></div>
            <div class="slide-in-footer">
                <a href="${window.ROYAL_CONFIG?.BASE_URL || '/'}cart.php" class="btn-view-cart">View Cart</a>
                <a href="${window.ROYAL_CONFIG?.BASE_URL || '/'}checkout.php" class="btn-checkout-now">Checkout</a>
            </div>
        `;
        document.body.appendChild(slideIn);

        // Close button
        document.getElementById('slideInClose').addEventListener('click', hideCartSlideIn);
    }

    // Add styles
    if (!document.getElementById('cartSlideInStyles')) {
        const style = document.createElement('style');
        style.id = 'cartSlideInStyles';
        style.textContent = `
            .cart-slide-in {
                position: fixed;
                top: 100px;
                right: -400px;
                width: 380px;
                max-width: 90vw;
                background: var(--white, #fff);
                border-radius: var(--radius-xl, 16px) 0 0 var(--radius-xl, 16px);
                box-shadow: -10px 0 40px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                transition: right 1.3s ease;
            }

            .cart-slide-in.active {
                right: 0;
            }

            .slide-in-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: var(--space-lg, 20px);
                border-bottom: 1px solid var(--gray-200, #e5e5e5);
            }

            .slide-in-title {
                display: flex;
                align-items: center;
                gap: var(--space-sm, 10px);
                font-weight: 600;
                color: var(--success, #22c55e);
            }

            .slide-in-title svg {
                color: var(--success, #22c55e);
            }

            .slide-in-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--gray-500, #666);
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }

            .slide-in-close:hover {
                color: var(--black, #000);
            }

            .slide-in-content {
                padding: var(--space-lg, 20px);
            }

            .slide-in-item {
                display: flex;
                gap: var(--space-md, 15px);
            }

            .slide-in-item-image {
                width: 80px;
                height: 80px;
                border-radius: var(--radius-md, 8px);
                object-fit: cover;
                flex-shrink: 0;
            }

            .slide-in-item-info {
                flex: 1;
                min-width: 0;
            }

            .slide-in-item-name {
                font-family: var(--font-serif, Georgia, serif);
                font-size: 1rem;
                font-weight: 500;
                font-style: italic;
                color: var(--black, #000);
                margin-bottom: var(--space-xs, 5px);
            }

            .slide-in-item-meta {
                font-size: 0.85rem;
                color: var(--gray-500, #666);
                margin-bottom: var(--space-sm, 10px);
            }

            .slide-in-item-price {
                font-family: var(--font-serif, Georgia, serif);
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--gold, #d4af37);
            }

            .slide-in-footer {
                display: flex;
                gap: var(--space-sm, 10px);
                padding: var(--space-lg, 20px);
                border-top: 1px solid var(--gray-200, #e5e5e5);
            }

            .btn-view-cart,
            .btn-checkout-now {
                flex: 1;
                text-align: center;
                padding: var(--space-md, 12px);
                border-radius: var(--radius-lg, 12px);
                font-weight: 600;
                font-size: 0.9rem;
                text-decoration: none;
                transition: all 0.15s ease;
            }

            .btn-view-cart {
                background: var(--white, #fff);
                border: 1px solid var(--gray-300, #ccc);
                color: var(--black, #000);
            }

            .btn-view-cart:hover {
                border-color: var(--gold, #d4af37);
                color: var(--gold, #d4af37);
            }

            .btn-checkout-now {
                background: linear-gradient(135deg, var(--black, #000) 0%, #333 100%);
                border: none;
                color: var(--white, #fff);
            }

            .btn-checkout-now:hover {
                background: linear-gradient(135deg, var(--gold, #d4af37) 0%, var(--gold-light, #e5c158) 100%);
                color: var(--black, #000);
            }

            .slide-in-summary {
                display: flex;
                justify-content: space-between;
                padding-top: var(--space-md, 15px);
                margin-top: var(--space-md, 15px);
                border-top: 1px dashed var(--gray-200, #e5e5e5);
                font-size: 0.9rem;
                color: var(--gray-600, #555);
            }

            .slide-in-summary strong {
                color: var(--black, #000);
            }

            @media (max-width: 480px) {
                .cart-slide-in {
                    width: 100%;
                    right: -100%;
                    border-radius: 0;
                    top: 0;
                    height: 100vh;
                }
            }
        `;
        document.head.appendChild(style);
    }
};

/**
 * Show cart slide-in with product info
 * @param {Object} product - Product object with id, name, image_url, price_cents
 * @param {number} quantity - Quantity added
 * @param {number} cartTotal - Total cart value in cents
 * @param {number} cartCount - Total items in cart
 */
export const showCartSlideIn = (product, quantity, cartTotal, cartCount) => {
    initCartSlideIn();

    const slideIn = document.getElementById('cartSlideIn');
    const content = document.getElementById('slideInContent');

    if (!slideIn || !content) return;

    const price = ((product.price_cents || 0) / 100).toFixed(2);
    const total = ((cartTotal || 0) / 100).toFixed(2);

    content.innerHTML = `
        <div class="slide-in-item">
            <img src="${product.image_url ? (window.ROYAL_CONFIG.ASSET_URL + 'images/products/' + product.image_url.split('/').pop()) : (window.ROYAL_CONFIG.ASSET_URL + 'images/placeholder-product.png')}" alt="${product.name}" class="slide-in-item-image">
            <div class="slide-in-item-info">
                <div class="slide-in-item-name">${product.name}</div>
                <div class="slide-in-item-meta">Qty: ${quantity}</div>
                <div class="slide-in-item-price">$${price}</div>
            </div>
        </div>
        <div class="slide-in-summary">
            <span>Cart Total (${cartCount} item${cartCount !== 1 ? 's' : ''})</span>
            <strong>$${total}</strong>
        </div>
    `;

    // Show slide-in
    slideIn.classList.add('active');

    // Auto-hide after 5 seconds
    clearTimeout(slideInTimeout);
    slideInTimeout = setTimeout(hideCartSlideIn, 5000);
};

/**
 * Hide cart slide-in
 */
export const hideCartSlideIn = () => {
    const slideIn = document.getElementById('cartSlideIn');
    if (slideIn) {
        slideIn.classList.remove('active');
    }
    clearTimeout(slideInTimeout);
};

// Initialize on load
document.addEventListener('DOMContentLoaded', initCartSlideIn);

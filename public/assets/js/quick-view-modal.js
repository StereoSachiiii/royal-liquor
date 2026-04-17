/**
 * Quick View Modal Component
 * Shows product details in a modal without leaving the page
 */

class QuickViewModal {
    constructor() {
        this.modal = null;
        this.isOpen = false;
        this.currentProduct = null;
        this.init();
    }

    init() {
        if (document.getElementById('quick-view-modal')) {
            this.modal = document.getElementById('quick-view-modal');
            return;
        }

        this.createModal();
        this.attachEventListeners();
    }

    createModal() {
        const modalHTML = `
            <div class="quick-view-modal" id="quick-view-modal">
                <div class="qv-overlay"></div>
                <div class="qv-content">
                    <button class="qv-close" aria-label="Close">&times;</button>
                    
                    <div class="qv-layout">
                        <div class="qv-image-section">
                            <img src="" alt="" class="qv-main-image" id="qvMainImage">
                            <div class="qv-badges" id="qvBadges"></div>
                        </div>
                        
                        <div class="qv-info-section">
                            <span class="qv-category" id="qvCategory"></span>
                            <h2 class="qv-title" id="qvTitle"></h2>
                            
                            <div class="qv-rating" id="qvRating"></div>
                            
                            <div class="qv-price-section">
                                <span class="qv-price" id="qvPrice"></span>
                                <span class="qv-original-price" id="qvOriginalPrice"></span>
                            </div>
                            
                            <p class="qv-description" id="qvDescription"></p>
                            
                            <div class="qv-stock" id="qvStock"></div>
                            
                            <div class="qv-actions">
                                <div class="qv-quantity">
                                    <button class="qv-qty-btn minus" id="qvQtyMinus">−</button>
                                    <input type="number" id="qvQuantity" value="1" min="1">
                                    <button class="qv-qty-btn plus" id="qvQtyPlus">+</button>
                                </div>
                                <button class="btn btn-gold qv-add-cart" id="qvAddCart">Add to Cart</button>
                                <button class="btn btn-outline qv-wishlist" id="qvWishlist">♡</button>
                            </div>
                            
                            <a href="#" class="qv-view-full" id="qvViewFull">View Full Details →</a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const container = document.createElement('div');
        container.innerHTML = modalHTML;
        document.body.appendChild(container.firstElementChild);
        this.modal = document.getElementById('quick-view-modal');

        this.addStyles();
    }

    addStyles() {
        if (document.getElementById('quick-view-styles')) return;

        const style = document.createElement('style');
        style.id = 'quick-view-styles';
        style.textContent = `
            .quick-view-modal {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .quick-view-modal.active {
                display: flex;
            }
            
            .qv-overlay {
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(10px);
                animation: qvFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }
            
            .qv-content {
                position: relative;
                background: #fff;
                border-radius: 20px;
                max-width: 1000px;
                width: 100%;
                max-height: 90vh;
                overflow: auto;
                box-shadow: 0 40px 100px rgba(0, 0, 0, 0.1);
                animation: qvSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            }
            
            .qv-close {
                position: absolute;
                top: 16px;
                right: 16px;
                width: 40px;
                height: 40px;
                background: #f5f5f5;
                border: none;
                border-radius: 50%;
                font-size: 24px;
                cursor: pointer;
                z-index: 10;
                transition: all 0.2s;
            }
            
            .qv-close:hover {
                background: var(--black);
                color: var(--white);
            }
            
            .qv-layout {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0;
            }
            
            .qv-image-section {
                position: relative;
                background: #f5f5f7;
                aspect-ratio: 1/1.2;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            
            .qv-main-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .qv-badges {
                position: absolute;
                top: 16px;
                left: 16px;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .qv-badge {
                padding: 6px 12px;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                border-radius: 4px;
            }
            
            .qv-badge.premium {
                background: var(--black);
                color: var(--white);
            }
            
            .qv-badge.low-stock {
                background: #f59e0b;
                color: #fff;
            }
            
            .qv-info-section {
                padding: 32px;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            
            .qv-category {
                font-family: var(--font-heading);
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 2px;
                color: var(--gray-400);
            }
            
            .qv-title {
                font-family: var(--font-heading) !important;
                font-size: 2.25rem;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: var(--black);
                margin: 0;
                line-height: 1.1;
            }
            
            .qv-rating {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.9rem;
            }
            
            .qv-rating .stars {
                color: #d4af37;
            }
            
            .qv-rating .count {
                color: #888;
            }
            
            .qv-price-section {
                display: flex;
                align-items: baseline;
                gap: 12px;
            }
            
            .qv-price {
                font-family: var(--font-heading);
                font-size: 2rem;
                font-weight: 700;
                color: var(--black);
            }
            
            .qv-original-price {
                font-size: 1.1rem;
                color: #999;
                text-decoration: line-through;
            }
            
            .qv-description {
                color: var(--gray-600);
                line-height: 1.7;
                font-size: 1rem;
                margin: 0;
                display: -webkit-box;
                -webkit-line-clamp: 4;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            .qv-stock {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.9rem;
            }
            
            .qv-stock-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #22c55e;
            }
            
            .qv-stock.low-stock .qv-stock-dot {
                background: #f59e0b;
            }
            
            .qv-stock.out-of-stock .qv-stock-dot {
                background: #ef4444;
            }
            
            .qv-actions {
                display: flex;
                gap: 12px;
                margin-top: 8px;
            }
            
            .qv-quantity {
                display: flex;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
            }
            
            .qv-qty-btn {
                width: 40px;
                height: 44px;
                background: #f5f5f5;
                border: none;
                font-size: 1.25rem;
                cursor: pointer;
                transition: background 0.2s;
            }
            
            .qv-qty-btn:hover {
                background: var(--black);
                color: var(--white);
            }
            
            .qv-quantity input {
                width: 50px;
                text-align: center;
                border: none;
                font-size: 1rem;
                font-weight: 600;
            }
            
            .qv-add-cart {
                flex: 1;
                padding: 12px 24px;
                font-size: 1rem;
            }
            
            .qv-wishlist {
                width: 48px;
                height: 48px;
                padding: 0;
                font-size: 1.25rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--gray-200);
                border-radius: 8px;
                background: transparent;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .qv-wishlist.active {
                color: #d4af37;
                border-color: #d4af37;
            }
            
            .qv-view-full {
                color: var(--black);
                font-weight: 700;
                text-decoration: underline;
                font-size: 0.9rem;
                margin-top: auto;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .qv-view-full:hover {
                text-decoration: underline;
            }
            
            @keyframes qvFadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes qvSlideUp {
                from { opacity: 0; transform: translateY(30px) scale(0.95); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            
            @media (max-width: 768px) {
                .qv-layout {
                    grid-template-columns: 1fr;
                }
                
                .qv-image-section {
                    aspect-ratio: 4/3;
                }
                
                .qv-info-section {
                    padding: 24px;
                }
                
                .qv-title {
                    font-size: 1.5rem;
                }
                
                .qv-price {
                    font-size: 1.5rem;
                }
                
                .qv-actions {
                    flex-wrap: wrap;
                }
                
                .qv-add-cart {
                    width: 100%;
                    order: -1;
                }
            }
        `;
        document.head.appendChild(style);
    }

    attachEventListeners() {
        // Close on overlay click
        this.modal.querySelector('.qv-overlay').addEventListener('click', () => this.close());

        // Close button
        this.modal.querySelector('.qv-close').addEventListener('click', () => this.close());

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) this.close();
        });

        // Quantity controls
        this.modal.querySelector('#qvQtyMinus').addEventListener('click', () => {
            const input = this.modal.querySelector('#qvQuantity');
            if (input.value > 1) input.value = parseInt(input.value) - 1;
        });

        this.modal.querySelector('#qvQtyPlus').addEventListener('click', () => {
            const input = this.modal.querySelector('#qvQuantity');
            input.value = parseInt(input.value) + 1;
        });
    }

    open(product, callbacks = {}) {
        this.init();
        this.currentProduct = product;

        // Populate modal
        this.modal.querySelector('#qvMainImage').src = product.image_url || product.image || '/assets/images/placeholder.jpg';
        this.modal.querySelector('#qvMainImage').alt = product.name;
        this.modal.querySelector('#qvCategory').textContent = product.category_name || product.category || 'Premium Spirits';
        this.modal.querySelector('#qvTitle').textContent = product.name;
        this.modal.querySelector('#qvPrice').textContent = `$${parseFloat(product.price).toFixed(2)}`;
        this.modal.querySelector('#qvDescription').textContent = product.description || 'A premium selection from our collection.';

        // Original price
        const originalPrice = this.modal.querySelector('#qvOriginalPrice');
        if (product.original_price && product.original_price > product.price) {
            originalPrice.textContent = `$${parseFloat(product.original_price).toFixed(2)}`;
            originalPrice.style.display = 'inline';
        } else {
            originalPrice.style.display = 'none';
        }

        // Rating
        const rating = product.rating || 4.5;
        const ratingCount = product.rating_count || product.reviews_count || 0;
        this.modal.querySelector('#qvRating').innerHTML = `
            <span class="stars">${'★'.repeat(Math.floor(rating))}${'☆'.repeat(5 - Math.floor(rating))}</span>
            <span class="count">(${ratingCount} reviews)</span>
        `;

        // Stock status
        const stock = parseInt(product.stock_quantity || product.stock || 10);
        const stockEl = this.modal.querySelector('#qvStock');
        if (stock <= 0) {
            stockEl.className = 'qv-stock out-of-stock';
            stockEl.innerHTML = '<span class="qv-stock-dot"></span> Out of Stock';
        } else if (stock <= 5) {
            stockEl.className = 'qv-stock low-stock';
            stockEl.innerHTML = `<span class="qv-stock-dot"></span> Only ${stock} left in stock`;
        } else {
            stockEl.className = 'qv-stock';
            stockEl.innerHTML = '<span class="qv-stock-dot"></span> In Stock';
        }

        // Badges
        const badgesContainer = this.modal.querySelector('#qvBadges');
        badgesContainer.innerHTML = '';
        if (product.is_premium || parseFloat(product.price) > 100) {
            badgesContainer.innerHTML += '<span class="qv-badge premium">Premium</span>';
        }
        if (stock > 0 && stock <= 5) {
            badgesContainer.innerHTML += '<span class="qv-badge low-stock">Low Stock</span>';
        }

        // Reset quantity
        this.modal.querySelector('#qvQuantity').value = 1;

        // View full link
        this.modal.querySelector('#qvViewFull').href = `product.php?id=${product.id}`;

        // Add to cart handler
        const addCartBtn = this.modal.querySelector('#qvAddCart');
        addCartBtn.onclick = () => {
            const qty = parseInt(this.modal.querySelector('#qvQuantity').value);
            if (callbacks.onAddToCart) {
                callbacks.onAddToCart(product, qty);
            }
        };

        // Wishlist handler
        const wishlistBtn = this.modal.querySelector('#qvWishlist');
        if (callbacks.isInWishlist && callbacks.isInWishlist(product.id)) {
            wishlistBtn.classList.add('active');
            wishlistBtn.textContent = '♥';
        } else {
            wishlistBtn.classList.remove('active');
            wishlistBtn.textContent = '♡';
        }

        wishlistBtn.onclick = () => {
            if (callbacks.onToggleWishlist) {
                callbacks.onToggleWishlist(product);
                if (wishlistBtn.classList.contains('active')) {
                    wishlistBtn.classList.remove('active');
                    wishlistBtn.textContent = '♡';
                } else {
                    wishlistBtn.classList.add('active');
                    wishlistBtn.textContent = '♥';
                }
            }
        };

        // Show modal
        this.modal.classList.add('active');
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.modal.classList.remove('active');
        this.isOpen = false;
        this.currentProduct = null;
        document.body.style.overflow = '';
    }
}

// Create singleton instance
const quickViewModal = new QuickViewModal();

export { quickViewModal, QuickViewModal };
export default quickViewModal;

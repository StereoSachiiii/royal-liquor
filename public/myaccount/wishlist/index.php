<?php require_once __DIR__ . '/../../components/header.php'; ?>
<div class="wishlist-container">
    <div class="wishlist-header">
        <h1>My Wishlist</h1>
        <p class="wishlist-count"><span id="wishlist-count">0</span> items</p>
        <button id="add-all-to-cart-btn" class="wishlist-btn add-all-btn hidden">
            Add All To Cart
        </button>
    </div>

    <div id="wishlist-content">
        <div class="loading">Loading your wishlist...</div>
    </div>
</div>

<div id="toast-container"></div>
<div id="cart-modal" class="cart-modal">
    <div class="cart-modal-content">
        <p><span id="modal-item-count">X</span> items added to cart!</p>
        <a href="/cart" class="visit-cart-btn">Visit Cart</a>
    </div>
</div>

<script type="module">
    import { getWishlist, removeItemFromWishlist } from '../../assets/js/wishlist-storage.js';
    import { cart } from '../../assets/js/cart-service.js';
    import { updateCartCount } from '../../assets/js/header.js';

    function showToast(message, duration = 3000) {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    function showCartModal(count) {
        const modal = document.getElementById('cart-modal');
        document.getElementById('modal-item-count').textContent = count;
        modal.classList.add('show-modal');
        
        setTimeout(() => modal.classList.remove('show-modal'), 5000);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    // Helper function to get price from item (handles both price and price_cents)
    function getItemPrice(item) {
        if (item.price_cents !== undefined) {
            return (item.price_cents / 100).toFixed(2);
        }
        return parseFloat(item.price || 0).toFixed(2);
    }

    // Helper function to get image URL from item
    function getItemImage(item) {
        return item.image_url || item.image || '';
    }

    // Helper function to get date for grouping
    function getItemDate(item) {
        // Use created_at, updated_at, or added_date
        return item.created_at || item.updated_at || item.added_date || null;
    }

    async function loadWishlist() {
        const wishlistContent = document.getElementById('wishlist-content');
        const wishlistCountElement = document.getElementById('wishlist-count');
        const addAllBtn = document.getElementById('add-all-to-cart-btn');
        
        try {
            const wishlist = await getWishlist();
            
            wishlistCountElement.textContent = wishlist.length;

            if (wishlist.length === 0) {
                addAllBtn.classList.add('hidden');
                wishlistContent.innerHTML = `
                    <div class="empty-wishlist">
                        <h2>Your wishlist is empty</h2>
                        <p>Start adding items you love to your wishlist!</p>
                        <a href="<?= BASE_URL ?>" class="continue-shopping-btn">Continue Shopping</a>
                    </div>
                `;
                return;
            }

            addAllBtn.classList.remove('hidden');

            const groupedWishlist = wishlist.reduce((acc, item) => {
                const itemDate = getItemDate(item);
                const dateKey = itemDate ? formatDate(itemDate) : 'Unsorted Items';
                if (!acc[dateKey]) acc[dateKey] = [];
                acc[dateKey].push(item);
                return acc;
            }, {});

            const sortedDateKeys = Object.keys(groupedWishlist).sort((a, b) => {
                if (a === 'Unsorted Items') return 1;
                if (b === 'Unsorted Items') return -1;
                return new Date(b) - new Date(a);
            });

            const listHTML = sortedDateKeys.map(dateKey => {
                const items = groupedWishlist[dateKey];
                return `
                    <div class="wishlist-date-group">
                        <div class="date-header">Wishlist made on ${dateKey}</div>
                        <div class="wishlist-list">
                            ${items.map(item => `
                                <div class="wishlist-item" data-item-id="${item.id}">
                                    <div class="item-summary">
                                        <div class="wishlist-item-name">${item.name}</div>
                                        <div class="wishlist-item-price">Rs. ${getItemPrice(item)}</div>
                                    </div>
                                    <div class="item-details">
                                        <img src="${getItemImage(item)}" alt="${item.name}" class="wishlist-item-image">
                                        <div class="detail-actions">
                                            <div class="wishlist-item-quantity">
                                                <label for="qty-${item.id}">Qty:</label>
                                                <select id="qty-${item.id}" class="item-qty-select" data-item-id="${item.id}">
                                                    ${Array.from({length: 10}, (_, i) => i + 1).map(qty => `
                                                        <option value="${qty}">${qty}</option>
                                                    `).join('')}
                                                </select>
                                            </div>
                                            <button class="wishlist-btn add-to-cart-btn" data-item-id="${item.id}">
                                                Add to Cart
                                            </button>
                                            <button class="wishlist-btn remove-btn" data-item-id="${item.id}">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }).join('');

            wishlistContent.innerHTML = listHTML;

            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const button = e.target;
                    const itemId = parseInt(button.dataset.itemId);
                    const itemElement = button.closest('.wishlist-item');
                    const qtySelect = itemElement.querySelector(`.item-qty-select[data-item-id="${itemId}"]`);
                    const quantity = parseInt(qtySelect.value);
                    const itemName = itemElement.querySelector('.wishlist-item-name').textContent;
                    
                    button.classList.add('loading');
                    
                    try {
                        await cart.add(itemId, quantity);
                        showToast(`"${itemName}" added to cart!`);
                        await removeFromWishlist(itemId);
                    } catch (error) {
                        console.error('Failed to add to cart:', error);
                        button.classList.remove('loading');
                        button.classList.add('error');
                        
                        setTimeout(() => {
                            button.classList.remove('error');
                        }, 2000);
                    }
                });
            });

            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const itemId = parseInt(e.target.dataset.itemId);
                    await removeFromWishlist(itemId);
                    showToast('Item removed from wishlist.', 2000);
                });
            });
            
            addAllBtn.addEventListener('click', async () => {
                addAllBtn.classList.add('processing');
                
                let itemsAdded = 0;
                let newWishlist = [];
                const currentWishlist = await getWishlist();
                
                for (const item of currentWishlist) {
                    try {
                        await cart.add(item.id, 1, false);
                        await removeItemFromWishlist(item.id);
                        itemsAdded++;
                    } catch (error) {
                        console.error(`Failed to add item ${item.id} to cart:`, error);
                    }
                }

                await loadWishlist();
                
                addAllBtn.classList.remove('processing');

                if (itemsAdded > 0) {
                    showCartModal(itemsAdded);
                } else {
                    showToast('No items were added to the cart.', 3000);
                }
            }, { once: true });

        } catch (error) {
            console.error('Failed to load wishlist:', error);
            wishlistContent.innerHTML = `
                <div class="empty-wishlist">
                    <h2>Error loading wishlist</h2>
                    <p>Please try again later.</p>
                </div>
            `;
        }
    }

    async function removeFromWishlist(itemId) {
        try {
            const success = removeItemFromWishlist(itemId);
            if (success) {
                await loadWishlist();
            }
        } catch (error) {
            console.error('Failed to remove from wishlist:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', loadWishlist);
</script>

</body>
</html>

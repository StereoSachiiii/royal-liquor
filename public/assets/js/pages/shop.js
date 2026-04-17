/**
 * Shop Page JavaScript - Extracted and refactored to use unified API handler
 * API CALLS DISABLED FOR UI TESTING
 */

// import api from '../api-mock.js';  // DISABLED - No API calls for now
import { cart } from '../cart-service.js';
import { addItemToWishlist, isInWishlist } from '../wishlist-storage.js';

// State management
let productsData = [];
let currentOffset = 0;
const limit = 24;
let loading = false;
let hasMore = true;

/**
 * Fetch categories using unified API handler
 */
const fetchCategories = async () => {
    try {
        const response = await api.get('categories', { enriched: true, limit: 100 });

        if (response.success && response.data) {
            const select = document.getElementById('categorySelect');
            response.data.forEach(cat => {
                const opt = new Option(cat.name, cat.id);
                select.appendChild(opt);
            });
        }
    } catch (error) {
        console.error('Failed to load categories', error);
    }
};

/**
 * Fetch products using unified API handler
 */
const fetchProducts = async (reset = false) => {
    if (loading || (!hasMore && !reset)) return;
    loading = true;

    if (reset) {
        currentOffset = 0;
        hasMore = true;
        productsData = [];
    }

    const params = {
        enriched: true,
        limit,
        offset: currentOffset,
        search: document.getElementById('searchInput').value.trim(),
        sort: document.getElementById('sortSelect').value
    };

    const catId = document.getElementById('categorySelect').value;
    if (catId) params.category_id = catId;

    try {
        const response = await api.get('products', params);

        if (!response.success) {
            throw new Error(response.message);
        }

        const products = response.data || [];
        const container = document.getElementById('productsGrid');

        if (reset) container.innerHTML = '';

        if (products.length === 0) {
            container.innerHTML = reset ? '<div class="empty">No products found</div>' : '';
            hasMore = false;
        } else {
            container.innerHTML += products.map(renderProductCard).join('');
            productsData.push(...products);
            currentOffset += products.length;
            hasMore = products.length === limit;
            document.getElementById('loadMore').style.display = hasMore ? 'block' : 'none';
        }
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        loading = false;
    }
};

/**
 * Render product card HTML
 */
const renderProductCard = (p) => {
    const price = (p.price_cents / 100).toFixed(2);
    const available = p.available_stock > 0;

    let tags = '';
    try {
        const flavor = JSON.parse(p.flavor_profile);
        tags = (flavor.tags || []).slice(0, 3).map(t => `<span class="tag">${t}</span>`).join('');
    } catch (e) {
        tags = '';
    }

    const inWish = isInWishlist(p.id);

    return `
        <div class="product-card" data-id="${p.id}">
            <img src="${p.image_url}" alt="${p.name}" loading="lazy">
            <div class="info">
                <h3>${p.name}</h3>
                <p class="category">${p.category_name || '—'}</p>
                <div class="price">$${price}</div>
                ${tags ? `<div class="tags">${tags}</div>` : ''}
                <div class="actions">
                    <button class="wishlist-btn ${inWish ? 'active' : ''}" data-id="${p.id}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="${inWish ? '#111' : 'none'}" stroke="#111" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <button class="view-btn" data-id="${p.id}">View Details</button>
                    ${available
            ? `<button class="add-btn" data-id="${p.id}">Add to Cart</button>`
            : '<span class="out">Out of Stock</span>'
        }
                </div>
            </div>
        </div>
    `;
};

/**
 * Generate star rating HTML
 */
const generateStars = (rating) => {
    if (!rating) return '<span class="no-rating">No ratings</span>';

    let stars = '';
    for (let i = 0; i < 5; i++) {
        if (i < Math.floor(rating)) {
            stars += '★';
        } else if (i === Math.floor(rating) && rating % 1 >= 0.5) {
            stars += '★';
        } else {
            stars += '★';
        }
    }
    return `<div class="stars">${stars}</div><span class="rating-value">${rating}</span>`;
};

/**
 * Open product detail modal
 */
const openProductModal = async (id) => {
    const product = productsData.find(p => p.id === parseInt(id));
    if (!product) return;

    const available = product.available_stock > 0;
    const price = (product.price_cents / 100).toFixed(2);

    // Set basic info
    document.getElementById('modalImage').src = product.image_url;
    document.getElementById('modalName').textContent = product.name;
    document.getElementById('modalPrice').textContent = `$${price}`;
    document.getElementById('modalDescription').textContent = product.description || 'No description.';
    document.getElementById('modalCategory').textContent = product.category_name || '—';
    document.getElementById('modalSupplier').textContent = product.supplier_name || '—';
    document.getElementById('modalUnitsSold').textContent = product.units_sold || 0;
    document.getElementById('modalStock').textContent = available ? `${product.available_stock} units` : 'Out of Stock';

    // Set badge
    const badge = document.getElementById('modalBadge');
    badge.className = 'modal-badge ' + (available ? (product.available_stock < 50 ? 'low-stock' : 'in-stock') : 'out-of-stock');
    badge.textContent = available ? (product.available_stock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock';

    // Set rating
    document.getElementById('modalRating').innerHTML = generateStars(parseFloat(product.avg_rating));

    // Flavor profile
    try {
        const flavor = JSON.parse(product.flavor_profile);
        const bars = document.getElementById('flavorBars');
        const tags = document.getElementById('flavorTags');

        if (flavor.sweetness != null) {
            const attrs = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
            bars.innerHTML = attrs.map(attr => {
                const value = flavor[attr] ?? 5;
                return `<div class="flavor-bar-item">
                    <span class="flavor-name">${attr.charAt(0).toUpperCase() + attr.slice(1)}</span>
                    <div class="flavor-bar"><div class="flavor-fill" style="width:${(value / 10) * 100}%"></div></div>
                    <span>${value}/10</span>
                </div>`;
            }).join('');
            tags.innerHTML = (flavor.tags || []).map(t => `<span class="flavor-tag">${t}</span>`).join('') || '<span style="opacity:.6">No notes</span>';
        } else {
            throw new Error('No flavor profile');
        }
    } catch {
        document.getElementById('flavorBars').innerHTML = '<p>No flavor profile</p>';
        document.getElementById('flavorTags').innerHTML = '';
    }

    // Set button states
    const qtyInput = document.getElementById('modalQuantity');
    const addBtn = document.getElementById('modalAddToCart');
    const wishBtn = document.getElementById('modalWishlistBtn');

    qtyInput.disabled = !available;
    qtyInput.value = 1;
    addBtn.disabled = !available;
    addBtn.dataset.id = product.id;
    wishBtn.classList.toggle('active', isInWishlist(product.id));
    wishBtn.dataset.id = product.id;

    // Show modal
    document.getElementById('detailModal').classList.add('active');
    document.body.style.overflow = 'hidden';
};

/**
 * Close product modal
 */
const closeModal = () => {
    document.getElementById('detailModal').classList.remove('active');
    document.body.style.overflow = '';
};

/**
 * Initialize event listeners
 */
const initEventListeners = () => {
    // Filter events
    document.getElementById('searchInput').addEventListener('input', () => fetchProducts(true));
    document.getElementById('categorySelect').addEventListener('change', () => fetchProducts(true));
    document.getElementById('sortSelect').addEventListener('change', () => fetchProducts(true));
    document.getElementById('loadMore').addEventListener('click', () => fetchProducts());

    // Click delegation for buttons
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        // View details
        if (btn.classList.contains('view-btn')) {
            openProductModal(btn.dataset.id);
        }

        // Add to cart
        if (btn.classList.contains('add-btn')) {
            await cart.add(btn.dataset.id, 1);
        }

        // Wishlist
        if (btn.classList.contains('wishlist-btn') || btn.id === 'modalWishlistBtn') {
            const id = btn.dataset.id;
            await addItemToWishlist(id);
            btn.classList.toggle('active', isInWishlist(id));
            btn.querySelector('svg')?.setAttribute('fill', isInWishlist(id) ? '#111' : 'none');
        }

        // Modal add to cart
        if (btn.id === 'modalAddToCart' && !btn.disabled) {
            const qty = parseInt(document.getElementById('modalQuantity').value) || 1;
            await cart.add(btn.dataset.id, qty);
            closeModal();
        }

        // Close modal
        if (btn.id === 'detailCloseBtn' || btn.classList.contains('detail-modal-overlay')) {
            closeModal();
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
};

/**
 * Initialize page
 */
const init = async () => {
    await fetchCategories();
    fetchProducts(true);
    initEventListeners();
};

// Start when DOM is ready
init();

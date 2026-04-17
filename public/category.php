<?php
$pageName = 'category';
$pageTitle = 'Category - Royal Liquor';
require_once __DIR__ . "/components/header.php";

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<main class="category-page">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb flex justify-center items-center py-10 gap-4 text-[10px] uppercase font-black tracking-[.3em] text-gray-400">
            <a href="<?= BASE_URL ?>" class="hover:text-gold transition-colors">Home</a>
            <span>/</span>
            <a href="<?= BASE_URL ?>shop.php" class="hover:text-gold transition-colors">Shop</a>
            <span>/</span>
            <span class="text-black italic" id="breadcrumbCategory">Loading...</span>
        </nav>

        <!-- Category Hero -->
        <section class="category-hero relative py-24 mb-20 overflow-hidden bg-black text-white" id="categoryHero">
            <div class="relative z-10 max-w-4xl mx-auto flex flex-col items-center text-center px-8">
                <div class="inline-block px-6 py-2 bg-white text-black text-[10px] font-black uppercase tracking-[0.4em] mb-10">Collection</div>
                <h1 class="text-5xl md:text-7xl font-heading font-extrabold uppercase tracking-widest leading-none mb-8 italic" id="heroTitle">Loading...</h1>
                <p class="text-gray-400 font-light italic text-lg leading-relaxed mb-12 max-w-2xl" id="heroDescription"></p>
                <div class="flex items-center justify-center gap-16 py-8 border-y border-white/10 w-full max-w-xl">
                    <div class="flex flex-col">
                        <span class="text-3xl font-heading font-black mb-1" id="productCount">0</span>
                        <span class="text-[9px] uppercase tracking-widest text-gray-500 font-black">Vintages</span>
                    </div>
                    <div class="w-px h-10 bg-white/10"></div>
                    <div class="flex flex-col">
                        <span class="text-3xl font-heading font-black mb-1" id="priceRange">$0 - $0</span>
                        <span class="text-[9px] uppercase tracking-widest text-gray-500 font-black">Price Range</span>
                    </div>
                    <div class="w-px h-10 bg-white/10"></div>
                    <div class="flex flex-col">
                        <span class="text-3xl font-heading font-black mb-1" id="avgRating">—</span>
                        <span class="text-[9px] uppercase tracking-widest text-gray-500 font-black">Rating</span>
                    </div>
                </div>
            </div>
            
            <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/10 to-transparent"></div>
        </section>

        <!-- Flavor Summary -->
        <section class="flavor-summary" id="flavorSummary" style="display: none;">
            <h3>Flavor Profile</h3>
            <div class="flavor-bars" id="flavorBars"></div>
            <div class="flavor-tags" id="flavorTags"></div>
        </section>

        <!-- Products Section -->
        <section class="category-products">
            <div class="section-header">
                <h2 class="section-title">Products in this Collection</h2>
                <div class="section-controls">
                    <select id="sortSelect" class="sort-select">
                        <option value="newest">Newest</option>
                        <option value="price_asc">Price ↑</option>
                        <option value="price_desc">Price ↓</option>
                        <option value="name_asc">A-Z</option>
                        <option value="rating">Top Rated</option>
                    </select>
                </div>
            </div>

            <div class="products-grid" id="productsGrid">
                <div class="skeleton-loader">
                    <div class="skeleton-card"></div>
                    <div class="skeleton-card"></div>
                    <div class="skeleton-card"></div>
                    <div class="skeleton-card"></div>
                </div>
            </div>

            <div class="empty-state" id="emptyState" style="display: none;">
                <div class="empty-icon">🍾</div>
                <h3>No products in this category yet</h3>
                <p>Check back soon for new arrivals</p>
                <a href="<?= BASE_URL ?>shop.php" class="btn btn-black">Browse All Products</a>
            </div>
        </section>

        <!-- Related Categories -->
        <section class="related-categories" id="relatedCategories">
            <h2 class="section-title">Explore Other Collections</h2>
            <div class="categories-grid" id="categoriesGrid"></div>
        </section>
    </div>
</main>

<style>
.category-page {
    background: var(--white);
    min-height: 100vh;
    padding-bottom: var(--space-3xl);
}

.breadcrumb {
    padding: var(--space-lg) 0;
    font-size: 0.9rem;
    color: var(--gray-500);
}

.breadcrumb a {
    color: var(--gray-500);
    text-decoration: none;
}

.breadcrumb a:hover {
    color: var(--black);
}

.breadcrumb .separator {
    margin: 0 var(--space-sm);
}

.breadcrumb .current {
    color: var(--black);
}

/* Category Hero */
.category-hero {
    position: relative;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
    border-radius: var(--radius-xl);
    padding: var(--space-3xl);
    margin-bottom: var(--space-2xl);
    overflow: hidden;
    min-height: 350px;
    display: flex;
    align-items: center;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 600px;
}

.hero-badge {
    display: inline-block;
    padding: var(--space-xs) var(--space-md);
    background: var(--black);
    color: var(--white);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    border-radius: var(--radius-sm);
    margin-bottom: var(--space-lg);
}

.hero-title {
    font-family: var(--font-serif);
    font-size: 3.5rem;
    font-weight: 300;
    font-style: italic;
    color: var(--white);
    margin-bottom: var(--space-md);
    line-height: 1.1;
}

.hero-description {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.7;
    margin-bottom: var(--space-xl);
}

.hero-stats {
    display: flex;
    align-items: center;
    gap: var(--space-xl);
}

.stat {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-family: var(--font-serif);
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--black);
}

.stat-label {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-divider {
    width: 1px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
}

.hero-visual {
    position: absolute;
    right: 0;
    top: 0;
    width: 50%;
    height: 100%;
    z-index: 1;
}

.hero-pattern {
    position: absolute;
    right: -100px;
    top: 50%;
    transform: translateY(-50%);
    width: 400px;
    height: 400px;
    background: radial-gradient(circle at center, rgba(0, 0, 0, 0.45) 0%, transparent 60%);
    border-radius: 50%;
}

.hero-pattern::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

/* Flavor Summary */
.flavor-summary {
    background: var(--gray-50);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    margin-bottom: var(--space-2xl);
}

.flavor-summary h3 {
    font-family: var(--font-serif);
    font-size: 1.25rem;
    font-style: italic;
    margin-bottom: var(--space-lg);
}

.flavor-bars {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
    margin-bottom: var(--space-lg);
}

.flavor-bar-item {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
}

.flavor-bar-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
}

.flavor-bar-label span:first-child {
    color: var(--gray-600);
}

.flavor-bar-label span:last-child {
    font-weight: 600;
    color: var(--black);
}

.flavor-bar {
    height: 6px;
    background: var(--gray-200);
    border-radius: 3px;
    overflow: hidden;
}

.flavor-bar-fill {
    height: 100%;
    background: var(--black);
    border-radius: 3px;
    transition: width 0.8s ease;
}

.flavor-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-sm);
}

.flavor-tag {
    padding: var(--space-xs) var(--space-md);
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-full);
    font-size: 0.85rem;
    color: var(--gray-700);
}

/* Products Section */
.category-products {
    margin-bottom: var(--space-3xl);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-xl);
}

.section-title {
    font-family: var(--font-serif);
    font-size: 1.75rem;
    font-weight: 400;
    font-style: italic;
}

.sort-select {
    padding: var(--space-sm) var(--space-lg) var(--space-sm) var(--space-md);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    background: var(--white);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-xl);
}

/* Skeleton Loader */
.skeleton-loader {
    display: contents;
}

.skeleton-card {
    background: var(--gray-100);
    border-radius: var(--radius-lg);
    aspect-ratio: 3/4;
    animation: skeleton-pulse 1.5s ease-in-out infinite;
}

@keyframes skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Product Card */
.product-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--duration-normal);
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    border-color: var(--black);
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.product-card-image {
    aspect-ratio: 1/1;
    overflow: hidden;
    position: relative;
}

.product-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-slow);
}

.product-card:hover .product-card-image img {
    transform: scale(1.08);
}

.product-badge {
    position: absolute;
    top: var(--space-md);
    left: var(--space-md);
    padding: var(--space-xs) var(--space-sm);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    border-radius: var(--radius-sm);
    background: var(--black);
    color: var(--white);
}

.product-card-info {
    padding: var(--space-lg);
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-card-name {
    font-family: var(--font-serif);
    font-size: 1.1rem;
    font-weight: 500;
    font-style: italic;
    color: var(--black);
    margin-bottom: var(--space-sm);
    line-height: 1.3;
}

.product-card-rating {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    margin-bottom: var(--space-sm);
    font-size: 0.85rem;
}

.product-card-rating .stars {
    color: var(--gold);
}

.product-card-rating .count {
    color: var(--gray-500);
}

.product-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: var(--space-md);
    border-top: 1px solid var(--gray-100);
}

.product-card-price {
    font-family: var(--font-serif);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gold);
}

.btn-add-cart {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--black);
    color: var(--white);
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    transition: all var(--duration-fast);
}

.btn-add-cart:hover {
    background: var(--gold);
    color: var(--black);
    transform: scale(1.1);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--space-3xl);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--space-lg);
}

.empty-state h3 {
    font-family: var(--font-serif);
    font-style: italic;
    margin-bottom: var(--space-sm);
}

.empty-state p {
    color: var(--gray-500);
    margin-bottom: var(--space-xl);
}

/* Related Categories */
.related-categories {
    border-top: 1px solid var(--gray-200);
    padding-top: var(--space-2xl);
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-lg);
    margin-top: var(--space-xl);
}

.category-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--space-xl);
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    text-decoration: none;
    color: inherit;
    transition: all var(--duration-fast);
}

.category-card:hover {
    background: var(--black);
    color: var(--white);
}

.category-card:hover .category-card-name {
    color: var(--gold);
}

.category-card-icon {
    font-size: 2.5rem;
    margin-bottom: var(--space-md);
}

.category-card-name {
    font-family: var(--font-serif);
    font-size: 1.1rem;
    font-style: italic;
    transition: color var(--duration-fast);
}

.category-card-count {
    font-size: 0.8rem;
    color: var(--gray-500);
    margin-top: var(--space-xs);
}

.category-card:hover .category-card-count {
    color: rgba(255, 255, 255, 0.6);
}

/* Responsive */
@media (max-width: 1024px) {
    .products-grid,
    .categories-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-visual {
        width: 40%;
    }
}

@media (max-width: 768px) {
    .products-grid,
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .flavor-bars {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .category-hero {
        padding: var(--space-xl);
        min-height: 280px;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-stats {
        flex-wrap: wrap;
        gap: var(--space-md);
    }
    
    .stat-divider {
        display: none;
    }
    
    .hero-visual {
        display: none;
    }
}

@media (max-width: 480px) {
    .products-grid,
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .flavor-bars {
        grid-template-columns: 1fr;
    }
}
</style>

<script type="module">
import { API } from '<?= BASE_URL ?>assets/js/api-helper.js';
import { cart } from '<?= BASE_URL ?>assets/js/cart-service.js';
import { toast } from '<?= BASE_URL ?>assets/js/toast.js';

const categoryId = <?= $categoryId ?>;
let productsData = [];
let categoryData = null;
let allCategories = [];

// Initialize
const init = async () => {
    
    if (!categoryId) {
        showError('Invalid category');
        return;
    }
    
    await loadData();
    renderCategory();
    renderProducts();
    renderRelatedCategories();
    setupEventListeners();
};

// Load data
const loadData = async () => {
    try {
        const [productsRes, categoriesRes] = await Promise.all([
            API.request('/products/enriched/all' + API.buildQuery({ limit: 100 })),
            API.categories.list()
        ]);

        allCategories = categoriesRes.success ? categoriesRes.data : [];
        categoryData = allCategories.find(c => c.id === categoryId);
        
        const allProducts = productsRes.success ? productsRes.data : [];
        productsData = allProducts.filter(p => p.category_id === categoryId);
    } catch (error) {
        console.error('[Category] Failed to load data:', error);
        showError('Failed to load category');
    }
};

// Render category hero
const renderCategory = () => {
    if (!categoryData) {
        showError('Category not found');
        return;
    }

    // Update breadcrumb
    document.getElementById('breadcrumbCategory').textContent = categoryData.name;
    
    // Update hero
    document.getElementById('heroTitle').textContent = categoryData.name;
    document.getElementById('heroDescription').textContent = 
        categoryData.description || `Explore our collection of premium ${categoryData.name.toLowerCase()}.`;
    
    // Calculate stats
    document.getElementById('productCount').textContent = productsData.length;
    
    if (productsData.length > 0) {
        const prices = productsData.map(p => p.price_cents / 100);
        const minPrice = Math.min(...prices).toFixed(0);
        const maxPrice = Math.max(...prices).toFixed(0);
        document.getElementById('priceRange').textContent = `$${minPrice} - $${maxPrice}`;
        
        const ratings = productsData.map(p => parseFloat(p.avg_rating) || 0).filter(r => r > 0);
        if (ratings.length > 0) {
            const avgRating = (ratings.reduce((a, b) => a + b, 0) / ratings.length).toFixed(1);
            document.getElementById('avgRating').textContent = `${avgRating} ★`;
        }
        
        // Flavor summary
        renderFlavorSummary();
    }
    
    // Update page title
    document.title = `${categoryData.name} - Royal Liquor`;
};

// Render flavor summary
const renderFlavorSummary = () => {
    // Calculate average flavors across all products in category
    const flavorAttrs = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
    const flavorSums = {};
    const tagCounts = {};
    let validProducts = 0;
    
    productsData.forEach(p => {
        try {
            const flavor = typeof p.flavor_profile === 'string' ? JSON.parse(p.flavor_profile) : p.flavor_profile;
            if (flavor.sweetness != null) {
                validProducts++;
                flavorAttrs.forEach(attr => {
                    flavorSums[attr] = (flavorSums[attr] || 0) + (flavor[attr] || 0);
                });
                (flavor.tags || []).forEach(tag => {
                    tagCounts[tag] = (tagCounts[tag] || 0) + 1;
                });
            }
        } catch (e) {}
    });
    
    if (validProducts === 0) return;
    
    // Show flavor summary
    const container = document.getElementById('flavorSummary');
    container.style.display = 'block';
    
    // Render bars
    const barsHtml = flavorAttrs.map(attr => {
        const avg = (flavorSums[attr] / validProducts).toFixed(1);
        const percent = (avg / 10) * 100;
        return `
            <div class="flavor-bar-item">
                <div class="flavor-bar-label">
                    <span>${attr.charAt(0).toUpperCase() + attr.slice(1)}</span>
                    <span>${avg}/10</span>
                </div>
                <div class="flavor-bar">
                    <div class="flavor-bar-fill" style="width: ${percent}%"></div>
                </div>
            </div>
        `;
    }).join('');
    document.getElementById('flavorBars').innerHTML = barsHtml;
    
    // Render top tags
    const topTags = Object.entries(tagCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 6);
    
    if (topTags.length > 0) {
        document.getElementById('flavorTags').innerHTML = 
            topTags.map(([tag]) => `<span class="flavor-tag">${tag}</span>`).join('');
    }
};

// Render products
const renderProducts = () => {
    const grid = document.getElementById('productsGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (productsData.length === 0) {
        grid.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    grid.style.display = 'grid';
    emptyState.style.display = 'none';
    grid.innerHTML = productsData.map(renderProductCard).join('');
};

// Render product card
const renderProductCard = (p) => {
    const price = (p.price_cents / 100).toFixed(2);
    const rating = parseFloat(p.avg_rating) || 0;
    const stars = '★'.repeat(Math.floor(rating)) + '☆'.repeat(5 - Math.floor(rating));
    const inStock = p.available_stock > 0;
    const isBestseller = (p.units_sold || 0) > 100;

    return `
        <article class="product-card">
            <a href="product.php?id=${p.id}" class="product-card-image">
                <img src="${p.image_url}" alt="${p.name}" loading="lazy">
                ${isBestseller ? '<span class="product-badge">Bestseller</span>' : ''}
            </a>
            <div class="product-card-info">
                <h3 class="product-card-name">${p.name}</h3>
                <div class="product-card-rating">
                    <span class="stars">${stars}</span>
                    <span class="count">(${rating.toFixed(1)})</span>
                </div>
                <div class="product-card-footer">
                    <span class="product-card-price">$${price}</span>
                    <button class="btn-add-cart" data-id="${p.id}" ${!inStock ? 'disabled' : ''} title="${inStock ? 'Add to Cart' : 'Out of Stock'}">
                        +
                    </button>
                </div>
            </div>
        </article>
    `;
};

// Render related categories
const renderRelatedCategories = () => {
    const otherCategories = allCategories.filter(c => c.id !== categoryId).slice(0, 4);
    
    if (otherCategories.length === 0) {
        document.getElementById('relatedCategories').style.display = 'none';
        return;
    }

    const icons = ['🥃', '🍷', '🍺', '🥂', '🍸', '🍹'];
    
    document.getElementById('categoriesGrid').innerHTML = otherCategories.map((c, i) => `
        <a href="category.php?id=${c.id}" class="category-card">
            <div class="category-card-icon">${icons[i % icons.length]}</div>
            <span class="category-card-name">${c.name}</span>
            <span class="category-card-count">${c.product_count || 0} products</span>
        </a>
    `).join('');
};

// Show error
const showError = (message) => {
    document.getElementById('categoryHero').innerHTML = `
        <div class="hero-content">
            <h1 class="hero-title" style="color: var(--error);">${message}</h1>
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-gold" style="margin-top: 20px;">Browse All Products</a>
        </div>
    `;
};

// Setup event listeners
const setupEventListeners = () => {
    // Sort
    document.getElementById('sortSelect').addEventListener('change', (e) => {
        sortProducts(e.target.value);
        renderProducts();
    });
    
    // Add to cart
    document.getElementById('productsGrid').addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-add-cart');
        if (btn && !btn.disabled) {
            const productId = btn.dataset.id;
            const product = productsData.find(p => p.id === parseInt(productId));
            
            await cart.add(productId, 1);
            
            // Visual feedback
            const originalText = btn.textContent;
            btn.textContent = '✓';
            toast.success(product ? `${product.name} added to cart!` : 'Added to cart!');
            
            setTimeout(() => {
                btn.textContent = originalText;
            }, 1500);
        }
    });
};

// Sort products
const sortProducts = (sortBy) => {
    productsData.sort((a, b) => {
        switch (sortBy) {
            case 'price_asc': return a.price_cents - b.price_cents;
            case 'price_desc': return b.price_cents - a.price_cents;
            case 'name_asc': return a.name.localeCompare(b.name);
            case 'rating': return (parseFloat(b.avg_rating) || 0) - (parseFloat(a.avg_rating) || 0);
            default: return 0;
        }
    });
};

// Initialize
document.addEventListener('DOMContentLoaded', init);
</script>

<?php require_once __DIR__ . "/components/footer.php"; ?>

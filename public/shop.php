<?php
$pageName = 'shop';
$pageTitle = 'Shop All - Royal Liquor';
require_once __DIR__ . "/components/header.php";
?>

<main class="min-h-screen bg-white">
    <!-- Breadcrumb & Header -->
    <div class="px-8 md:px-16 pt-12 pb-8 text-center flex flex-col items-center">
        <nav class="flex items-center justify-center gap-4 text-[10px] uppercase tracking-[0.3em] font-black text-gray-400 mb-12">
            <a href="<?= BASE_URL ?>" class="hover:text-black transition-colors">Home</a>
            <span class="text-gray-200">/</span>
            <span class="text-black italic">The Shop</span>
        </nav>
        
        <div class="max-w-4xl mx-auto space-y-8 pb-12">
            <div>
                <span class="text-xs uppercase tracking-[0.4em] text-black font-extrabold mb-4 block italic">Purveyors of Excellence</span>
                <h1 class="text-4xl md:text-6xl font-heading font-extrabold uppercase tracking-widest text-black leading-none">Shop All <br>Spirits</h1>
            </div>
            <p class="text-gray-400 text-base max-w-2xl mx-auto italic font-light leading-relaxed">
                Discover our meticulously curated collection of world-class spirits, rare releases, and artisanal craft beverages.
            </p>
        </div>
    </div>

    <div class="px-8 md:px-16 pb-32">
        <div class="flex flex-col lg:flex-row gap-16">
            <!-- Sidebar: Filters -->
            <aside class="w-full lg:w-80 shrink-0 space-y-12">
                <div class="flex items-center justify-between border-b border-black pb-4">
                    <h2 class="text-xs uppercase tracking-[0.3em] font-black">Filters</h2>
                    <button id="clearFilters" class="text-[9px] uppercase tracking-widest font-black text-gray-400 hover:text-red-600 transition-colors">Reset All</button>
                </div>

                <!-- Search Internal -->
                <div class="space-y-4">
                    <h3 class="text-[10px] uppercase tracking-widest font-black text-gray-400">Search Within</h3>
                    <div class="relative group">
                        <input type="text" id="internalSearch" placeholder="Type name..." class="w-full h-12 bg-gray-50 border-none outline-none px-4 text-xs font-bold uppercase tracking-widest placeholder:text-gray-300 focus:bg-white transition-colors">
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="space-y-6">
                    <h3 class="text-[10px] uppercase tracking-widest font-black text-gray-400">Collections</h3>
                    <div class="flex flex-col gap-2" id="categoryFilters">
                        <!-- Populated via JS -->
                    </div>
                </div>

                <!-- Price Range -->
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-[10px] uppercase tracking-widest font-black text-gray-400">Price Ceiling</h3>
                        <span id="maxPriceLabel" class="text-xs font-bold font-heading text-black">Rs. 50,000+</span>
                    </div>
                    <input type="range" id="priceSlider" min="0" max="50000" step="500" value="50000" class="w-full h-1.5 bg-gray-100 appearance-none cursor-pointer accent-black">
                    <div class="flex justify-between text-[9px] uppercase tracking-widest text-gray-300 font-bold">
                        <span>Rs. 0</span>
                        <span>Rs. 50,000+</span>
                    </div>
                </div>

                <!-- Rating -->
                <div class="space-y-6">
                    <h3 class="text-[10px] uppercase tracking-widest font-black text-gray-400">Minimum Rating</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="4" class="hidden peer">
                            <div class="peer-checked:bg-black peer-checked:text-white bg-gray-50 py-3 text-center text-[10px] font-black uppercase tracking-widest transition-all">4+ ★</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="3" class="hidden peer">
                            <div class="peer-checked:bg-black peer-checked:text-white bg-gray-50 py-3 text-center text-[10px] font-black uppercase tracking-widest transition-all">3+ ★</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="0" checked class="hidden peer">
                            <div class="peer-checked:bg-black peer-checked:text-white bg-gray-50 py-3 text-center text-[10px] font-black uppercase tracking-widest transition-all col-span-2">Any</div>
                        </label>
                    </div>
                </div>

                <!-- Availability -->
                <div class="space-y-6 pt-6 border-t border-gray-100">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" id="inStockOnly" class="w-4 h-4 accent-black">
                        <span class="text-[10px] uppercase tracking-widest font-bold group-hover:text-black transition-colors">In Stock Only</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" id="premiumOnly" class="w-4 h-4 accent-black">
                        <span class="text-[10px] uppercase tracking-widest font-bold group-hover:text-black transition-colors">Vintage Reserve</span>
                    </label>
                </div>
            </aside>

            <!-- Main Listing Area -->
            <div class="flex-grow">
                <!-- Toolbar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 py-6 border-b border-gray-50">
                    <div id="resultsCount" class="text-[10px] uppercase tracking-[0.3em] font-black text-gray-400 italic">Searching collection...</div>
                    
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3">
                            <span class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">Priority:</span>
                            <select id="sortSelect" class="bg-transparent border-none outline-none text-[10px] uppercase font-black tracking-widest cursor-pointer text-black hover:text-gray-500 transition-colors">
                                <option value="newest">Newest Arrival</option>
                                <option value="price_asc">Price Low-High</option>
                                <option value="price_desc">Price High-Low</option>
                                <option value="rating">Highest Rated</option>
                                <option value="popularity">Most Coveted</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Product Grid -->
                <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-16">
                    <!-- Products loaded via JS -->
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="hidden py-32 text-center flex-col items-center">
                    <div class="text-4xl mb-6">∅</div>
                    <h2 class="text-xs uppercase tracking-[0.3em] font-black mb-4">No Vintages Found</h2>
                    <p class="text-gray-400 text-sm italic font-light mb-8">Refine your search parameters to discover other spirits.</p>
                    <button id="resetFiltersBtn" class="btn-premium px-12">Reset Filters</button>
                </div>

                <!-- Load More -->
                <div id="loadMore" class="mt-20 flex justify-center hidden">
                    <button id="loadMoreBtn" class="btn-premium-outline px-16 h-14">Load More Selections</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="module">
import { API } from '<?= BASE_URL ?>assets/js/api-helper.js';
import { cart } from '<?= BASE_URL ?>assets/js/cart-service.js';
import { toast } from '<?= BASE_URL ?>assets/js/toast.js';

let productsData = [];
let categoriesData = [];
let filteredProducts = [];
const filters = {
    category: null,
    minPrice: null,
    maxPrice: null,
    rating: 0,
    inStockOnly: false,
    premiumOnly: false,
    sortBy: 'newest',
    searchQuery: ''
};

const productsGrid = document.getElementById('productsGrid');
const resultsCount = document.getElementById('resultsCount');
const emptyState = document.getElementById('emptyState');
const categoryFilters = document.getElementById('categoryFilters');
const sortSelect = document.getElementById('sortSelect');

// Helper to fix image paths (as in products.php)
const fixImagePath = (url) => {
    if (!url) return '<?= BASE_URL ?>assets/images/placeholder-product.png';
    if (url.includes('products/')) {
        const filename = url.split('/').pop();
        return '<?= BASE_URL ?>assets/images/' + filename;
    }
    return '<?= BASE_URL ?>assets/images/' + url.split('/').pop();
};

const init = async () => {
    await loadData();
    populateCategoryFilters();
    applyFilters();
    setupEventListeners();
};

const loadData = async () => {
    try {
        const [productsRes, categoriesRes] = await Promise.all([
            API.request('/products/enriched/all' + API.buildQuery({ limit: 200 })),
            API.categories.list()
        ]);
        productsData = productsRes.success ? (productsRes.data.items || productsRes.data) : [];
        categoriesData = categoriesRes.success ? (categoriesRes.data.items || categoriesRes.data) : [];
    } catch (error) {
        console.error('[Shop] Data Fetch Failed:', error);
    }
};

const populateCategoryFilters = () => {
    categoryFilters.innerHTML = `
        <label class="group flex items-center justify-between cursor-pointer">
            <input type="radio" name="category" value="" checked class="hidden peer">
            <span class="text-[10px] uppercase font-bold tracking-widest peer-checked:text-black transition-colors">Select All</span>
            <span class="text-[9px] text-gray-300 font-bold">(${productsData.length})</span>
        </label>
        ${categoriesData.map(c => `
            <label class="group flex items-center justify-between cursor-pointer">
                <input type="radio" name="category" value="${c.id}" class="hidden peer">
                <span class="text-[10px] uppercase font-bold tracking-widest peer-checked:text-black transition-colors">${c.name}</span>
                <span class="text-[9px] text-gray-300 font-bold">(${productsData.filter(p => p.category_id === c.id).length})</span>
            </label>
        `).join('')}
    `;
};

const renderProductCard = (p) => {
    const price = (p.price_cents / 100).toFixed(2);
    const rating = parseFloat(p.avg_rating) || 0;
    const inStock = p.available_stock > 0;
    const isPremium = p.price_cents >= 10000;
    
    return `
        <article class="group relative ${!inStock ? 'opacity-40 grayscale' : ''}">
            <a href="product.php?id=${p.id}" class="block overflow-hidden bg-gray-50 flex items-center justify-center p-8 h-[400px]">
                <img src="${fixImagePath(p.image_url)}" alt="${p.name}" class="w-full h-full object-contain transition-transform duration-1000 group-hover:scale-105" loading="lazy" onerror="this.src='<?= BASE_URL ?>assets/images/placeholder-product.png'">
                ${isPremium ? `<div class="absolute top-6 left-6 px-3 py-1 bg-black text-white text-[8px] font-black uppercase tracking-widest">Vintage</div>` : ''}
            </a>
            <div class="mt-8 text-center px-4">
                <span class="text-[9px] uppercase tracking-[0.3em] text-black font-extrabold mb-2 block">${p.category_name || 'Spirit'}</span>
                <h3 class="text-sm font-extrabold uppercase tracking-widest mb-3 line-clamp-1">${p.name}</h3>
                <div class="flex items-center justify-center gap-4 mb-6">
                    <span class="text-lg font-bold tracking-tight">Rs. ${price}</span>
                    <div class="w-1 h-1 bg-gray-200 rounded-full"></div>
                    <div class="flex text-black text-[10px] items-center gap-1 font-black">
                        ★ <span>${rating.toFixed(1)}</span>
                    </div>
                </div>
                <div class="flex gap-2 invisible group-hover:visible transition-all">
                    <a href="product.php?id=${p.id}" class="btn-premium-outline flex-grow h-12 text-[9px] flex items-center justify-center">View Specs</a>
                    <button class="btn-premium w-12 h-12 flex items-center justify-center btn-add-cart" data-id="${p.id}" ${!inStock ? 'disabled' : ''}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>
            </div>
        </article>
    `;
};

const applyFilters = () => {
    filteredProducts = productsData.filter(p => {
        if (filters.category && p.category_id !== parseInt(filters.category)) return false;
        const price = p.price_cents / 100;
        if (filters.maxPrice && price > filters.maxPrice) return false;
        if (filters.rating > 0 && (parseFloat(p.avg_rating) || 0) < filters.rating) return false;
        if (filters.inStockOnly && p.available_stock <= 0) return false;
        if (filters.premiumOnly && p.price_cents < 10000) return false;
        if (filters.searchQuery && !p.name.toLowerCase().includes(filters.searchQuery.toLowerCase())) return false;
        return true;
    });

    sortProducts();
    renderProducts();
};

const sortProducts = () => {
    filteredProducts.sort((a, b) => {
        switch (filters.sortBy) {
            case 'price_asc': return a.price_cents - b.price_cents;
            case 'price_desc': return b.price_cents - a.price_cents;
            case 'rating': return (parseFloat(b.avg_rating) || 0) - (parseFloat(a.avg_rating) || 0);
            case 'popularity': return (b.units_sold || 0) - (a.units_sold || 0);
            default: return new Date(b.created_at || 0) - new Date(a.created_at || 0);
        }
    });
};

const renderProducts = () => {
    if (filteredProducts.length === 0) {
        productsGrid.classList.add('hidden');
        emptyState.classList.remove('hidden');
        resultsCount.textContent = 'No items found';
        return;
    }
    productsGrid.classList.remove('hidden');
    emptyState.classList.add('hidden');
    resultsCount.textContent = `${filteredProducts.length} items found`;
    productsGrid.innerHTML = filteredProducts.map(renderProductCard).join('');
};

const setupEventListeners = () => {
    categoryFilters.addEventListener('change', (e) => {
        if (e.target.name === 'category') {
            filters.category = e.target.value || null;
            applyFilters();
        }
    });

    document.getElementById('priceSlider').addEventListener('input', (e) => {
        filters.maxPrice = parseInt(e.target.value);
        const displayValue = parseInt(e.target.value).toLocaleString();
        document.getElementById('maxPriceLabel').textContent = `Rs. ${displayValue}${e.target.value == 50000 ? '+' : ''}`;
        applyFilters();
    });

    document.querySelectorAll('input[name="rating"]').forEach(input => {
        input.addEventListener('change', (e) => {
            filters.rating = parseInt(e.target.value);
            applyFilters();
        });
    });

    document.getElementById('inStockOnly').addEventListener('change', (e) => { filters.inStockOnly = e.target.checked; applyFilters(); });
    document.getElementById('premiumOnly').addEventListener('change', (e) => { filters.premiumOnly = e.target.checked; applyFilters(); });
    document.getElementById('internalSearch').addEventListener('input', (e) => { filters.searchQuery = e.target.value; applyFilters(); });
    sortSelect.addEventListener('change', (e) => { filters.sortBy = e.target.value; applyFilters(); });

    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    document.getElementById('resetFiltersBtn').addEventListener('click', clearFilters);

    productsGrid.addEventListener('click', async (e) => {
        const addCartBtn = e.target.closest('.btn-add-cart');
        if (addCartBtn && !addCartBtn.disabled) {
            const productId = addCartBtn.dataset.id;
            await cart.add(productId, 1);
            toast.success('Added to Cart');
        }
    });
};

const clearFilters = () => {
    filters.category = null;
    filters.maxPrice = null;
    filters.rating = 0;
    filters.inStockOnly = false;
    filters.premiumOnly = false;
    filters.searchQuery = '';
    
    document.querySelectorAll('input[name="category"]')[0].checked = true;
    document.getElementById('priceSlider').value = 50000;
    document.getElementById('maxPriceLabel').textContent = 'Rs. 50,000+';
    document.querySelectorAll('input[name="rating"]').forEach(r => r.checked = r.value === '0');
    document.getElementById('inStockOnly').checked = false;
    document.getElementById('premiumOnly').checked = false;
    document.getElementById('internalSearch').value = '';
    applyFilters();
};

document.addEventListener('DOMContentLoaded', init);
</script>

<?php require_once __DIR__ . "/components/footer.php"; ?>

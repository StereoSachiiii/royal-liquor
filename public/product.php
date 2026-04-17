<?php 
$pageName = 'product';
$pageTitle = 'Product Details - Royal Liquor';
require_once __DIR__ . "/components/header.php"; 
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
?>

<main class="min-h-screen bg-white">
    <!-- Breadcrumb -->
    <div class="px-8 md:px-16 pt-12 pb-6 flex justify-center">
        <nav class="flex items-center gap-4 text-[10px] uppercase font-black tracking-[0.3em] text-gray-400 text-center">
            <a href="<?= BASE_URL ?>" class="hover:text-black transition-colors">Home</a>
            <span>/</span>
            <a href="shop.php" class="hover:text-black transition-colors">Spirits</a>
            <span>/</span>
            <span class="text-black italic" id="breadcrumbProduct">Loading...</span>
        </nav>
    </div>

    <!-- Product Showcase -->
    <section class="px-8 md:px-16 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 xl:gap-24 items-center">
            <!-- Left: High-Density Imagery -->
            <div class="relative bg-gray-50 flex items-center justify-center p-12 lg:p-24 h-[600px] lg:h-[800px] group overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-1000 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/20 via-transparent to-transparent"></div>
                <img id="mainImage" src="" alt="" class="w-full h-full object-contain transition-transform duration-1000 group-hover:scale-105 z-10" onerror="this.src='<?= BASE_URL ?>assets/images/placeholder-product.png'">
                
                <div id="productBadge" class="absolute top-12 left-12 z-20 px-6 py-2 bg-black text-white text-[10px] font-black uppercase tracking-[0.4em] shadow-2xl"></div>
            </div>

            <!-- Right: Architectural Specs -->
            <div class="flex flex-col">
                <span id="categoryTag" class="text-xs uppercase tracking-[0.4em] text-black font-black mb-6"></span>
                <h1 id="productTitle" class="text-4xl md:text-5xl font-heading font-extrabold uppercase tracking-widest leading-tight mb-8"></h1>
                
                <div class="flex items-center gap-8 mb-12 pb-12 border-b border-gray-100">
                    <div id="productPrice" class="text-4xl font-bold tracking-tight"></div>
                    <div class="h-6 w-[1px] bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <div id="ratingStars" class="flex text-black text-lg"></div>
                        <span id="ratingCount" class="text-[10px] font-black uppercase tracking-widest ml-2 text-gray-400"></span>
                    </div>
                </div>

                <div id="productDescription" class="prose prose-black text-gray-500 font-light italic leading-relaxed mb-12 max-w-xl">
                </div>

                <!-- Flavor Profile: High Contrast Radar Chart -->
                <div id="flavorSection" class="hidden mb-16 p-10 bg-gray-50/50 border border-gray-100/50">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-[10px] uppercase tracking-[0.4em] font-black italic">Tasting Archetype</h3>
                        <a id="findSimilarBtn" href="#" class="text-[9px] uppercase tracking-widest font-extrabold text-black hover:text-gray-500 transition-colors">Similar Profiles →</a>
                    </div>
                    <div class="relative h-[300px] w-full flex items-center justify-center">
                        <canvas id="flavorChart"></canvas>
                    </div>
                </div>

                <!-- Purchase Section -->
                <div class="flex flex-col gap-8">
                    <div class="flex items-center gap-6">
                        <div class="flex items-center border border-gray-200 h-16 px-6 bg-white">
                            <span class="text-[10px] uppercase font-black tracking-widest mr-8">Quantity</span>
                            <button id="qtyMinus" class="hover:text-black transition-colors font-bold text-xl px-2">−</button>
                            <input type="number" id="quantity" value="1" min="1" class="bg-transparent border-none outline-none text-center font-bold w-12 appearance-none">
                            <button id="qtyPlus" class="hover:text-black transition-colors font-bold text-xl px-2">+</button>
                        </div>
                        <button id="addToCartBtn" class="btn-premium flex-grow h-16 flex items-center justify-center gap-4 group">
                            Add to Cart
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-6 border border-gray-50 flex flex-col gap-1">
                            <span class="text-[9px] uppercase font-black tracking-widest text-gray-400">Inventory Status</span>
                            <span id="stockStatus" class="text-xs font-bold uppercase transition-colors"></span>
                        </div>
                        <div class="p-6 border border-gray-50 flex flex-col gap-1">
                            <span class="text-[9px] uppercase font-black tracking-widest text-gray-400">Provenance</span>
                            <span id="detailSupplier" class="text-xs font-bold uppercase"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Ledger Section -->
    <section class="bg-black text-white px-8 md:px-16 py-32 mt-20 text-center">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-xs uppercase tracking-[0.5em] text-white opacity-60 font-black mb-16">Technical Specifications</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-24 gap-y-12 border-t border-white/10 pt-16">
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest font-black text-gray-500">Logistics</h4>
                    <p class="text-sm font-light leading-relaxed">Secure shipping in temperature-controlled aesthetic packaging. Insured transit global-wide.</p>
                </div>
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest font-black text-gray-500">Product ID</h4>
                    <p id="detailId" class="text-sm font-mono tracking-widest text-white"></p>
                </div>
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest font-black text-gray-500">Circulation</h4>
                    <p id="detailSold" class="text-sm font-light">Units Distributed: 0</p>
                </div>
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest font-black text-gray-500">Authenticity</h4>
                    <p class="text-sm font-light leading-relaxed text-green-500">Certified Original. Guaranteed source provenance by Royal Liquor.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Experimental Pairings (Related Products) -->
    <section class="px-8 md:px-16 py-32 bg-gray-50">
        <div class="flex flex-col items-center mb-20 text-center">
            <span class="text-[10px] uppercase tracking-[0.4em] text-black font-black mb-4">Curated Pairings</span>
            <h2 class="text-3xl font-heading font-extrabold uppercase tracking-widest mb-6">Refined Alternatives</h2>
            <p class="text-gray-400 text-sm font-light italic max-w-lg">Based on technical flavor similarity and curated data.</p>
        </div>
        
        <div id="relatedProducts" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Related products injected here -->
        </div>
    </section>
</main>

<script type="module">
import { API } from '<?= BASE_URL ?>assets/js/api-helper.js';
import { cart } from '<?= BASE_URL ?>assets/js/cart-service.js';
import { toast } from '<?= BASE_URL ?>assets/js/toast.js';

const urlParams = new URLSearchParams(window.location.search);
const productId = parseInt(urlParams.get('id')) || 1;

let currentProduct = null;
let allProducts = [];

const fixImagePath = (url) => {
    if (!url) return '<?= BASE_URL ?>assets/images/placeholder-product.png';
    if (url.includes('products/')) {
        const filename = url.split('/').pop();
        return '<?= BASE_URL ?>assets/images/' + filename;
    }
    return '<?= BASE_URL ?>assets/images/' + url.split('/').pop();
};

let flavorChartInstance = null;
const renderFlavorRadar = (data) => {
    const ctx = document.getElementById('flavorChart').getContext('2d');
    const labels = ['Sweet', 'Bitter', 'Strength', 'Smoke', 'Fruit', 'Spice'];
    const values = [data.sweetness, data.bitterness, data.strength, data.smokiness, data.fruitiness, data.spiciness];

    if (flavorChartInstance) flavorChartInstance.destroy();

    flavorChartInstance = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Flavor Profile',
                data: values.map(v => v || 0),
                fill: true,
                backgroundColor: 'rgba(0, 0, 0, 0.05)',
                borderColor: 'rgb(0, 0, 0)',
                pointBackgroundColor: 'rgb(0, 0, 0)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(0, 0, 0)',
                borderWidth: 1.5,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { display: true, color: 'rgba(0, 0, 0, 0.05)' },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    suggestedMin: 0,
                    suggestedMax: 10,
                    ticks: { display: false },
                    pointLabels: {
                        font: { family: 'Inter', size: 9, weight: '900' },
                        color: '#000',
                        padding: 15
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#000',
                    titleFont: { size: 10 },
                    bodyFont: { size: 10 },
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.raw}/10`
                    }
                }
            }
        }
    });
};

const init = async () => {
    try {
        const response = await API.request('/products/enriched/all' + API.buildQuery({ limit: 100 }));
        if (response.success && response.data) {
            allProducts = response.data.items || response.data;
            currentProduct = allProducts.find(p => p.id === productId);
            
            if (currentProduct) {
                renderProduct(currentProduct);
                loadRelated(currentProduct);
            }
        }
    } catch (error) {
        console.error('[Product] Technical Consultant Error:', error);
    }
};

const renderProduct = (p) => {
    const price = (p.price_cents / 100).toFixed(2);
    const inStock = p.is_available && p.available_stock > 0;
    
    document.title = `${p.name} | Royal Liquor`;
    document.getElementById('breadcrumbProduct').textContent = p.name;
    document.getElementById('mainImage').src = fixImagePath(p.image_url);
    document.getElementById('productBadge').textContent = p.price_cents > 10000 ? 'Reserve' : (inStock ? 'Available' : 'Out of Stock');
    
    document.getElementById('categoryTag').textContent = p.category_name || 'Spirit';
    document.getElementById('productTitle').textContent = p.name;
    document.getElementById('productPrice').textContent = `Rs. ${price}`;
    document.getElementById('productDescription').innerHTML = `<p>${p.description}</p>`;
    
    const rating = parseFloat(p.avg_rating) || 0;
    document.getElementById('ratingStars').innerHTML = Array.from({length: 5}, (_, i) => 
        `<span class="${i < Math.floor(rating) ? 'text-black' : 'text-gray-200'}">★</span>`
    ).join('');
    document.getElementById('ratingCount').textContent = `${rating.toFixed(1)} / Customer Rating`;
    
    const stockEl = document.getElementById('stockStatus');
    stockEl.textContent = inStock ? (p.available_stock < 20 ? `Low Inventory: ${p.available_stock}` : 'In Stock') : 'Out of Stock';
    stockEl.className = `text-xs font-bold uppercase ${inStock ? (p.available_stock < 20 ? 'text-orange-600' : 'text-green-600') : 'text-red-600'}`;
    
    document.getElementById('detailSupplier').textContent = p.supplier_name || 'Traditional Craft';
    document.getElementById('detailId').textContent = `RL-${p.id.toString().padStart(6, '0')}`;
    document.getElementById('detailSold').textContent = `Volume Distributed: ${p.units_sold || 0} Units`;

    // Flavor Profile Grid
    try {
        const flavor = typeof p.flavor_profile === 'string' ? JSON.parse(p.flavor_profile) : p.flavor_profile;
        if (flavor && flavor.sweetness !== null) {
            renderFlavorRadar(flavor);
            document.getElementById('flavorSection').classList.remove('hidden');
        }
    } catch (e) {}
};

const loadRelated = (product) => {
    const related = allProducts
        .filter(p => p.id !== product.id)
        .slice(0, 4);
    
    document.getElementById('relatedProducts').innerHTML = related.map(p => {
        const price = (p.price_cents / 100).toFixed(2);
        return `
            <a href="product.php?id=${p.id}" class="group block bg-white p-8">
                <div class="h-64 mb-8 flex items-center justify-center bg-gray-50/50 p-4 relative overflow-hidden">
                    <img src="${fixImagePath(p.image_url)}" alt="${p.name}" class="w-full h-full object-contain transition-transform duration-1000 group-hover:scale-105">
                </div>
                <div class="text-center">
                    <span class="text-[8px] uppercase tracking-[0.4em] text-black font-black mb-2 block">${p.category_name}</span>
                    <h4 class="text-xs uppercase font-black tracking-widest mb-2 line-clamp-1">${p.name}</h4>
                    <span class="text-sm font-bold">Rs. ${price}</span>
                </div>
            </a>
        `;
    }).join('');
};

document.addEventListener('DOMContentLoaded', async () => {
    await init();
    
    const qtyInput = document.getElementById('quantity');
    document.getElementById('qtyMinus').onclick = () => { if (qtyInput.value > 1) qtyInput.value--; };
    document.getElementById('qtyPlus').onclick = () => { qtyInput.value++; };
    
    document.getElementById('addToCartBtn').onclick = async () => {
        if (!currentProduct) return;
        await cart.add(currentProduct.id, parseInt(qtyInput.value));
        toast.success('Added to Cart');
    };
    
    document.getElementById('findSimilarBtn').onclick = (e) => {
        e.preventDefault();
        try {
            const flavor = typeof currentProduct.flavor_profile === 'string' ? JSON.parse(currentProduct.flavor_profile) : currentProduct.flavor_profile;
            const params = new URLSearchParams();
            ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'].forEach(a => { if(flavor[a]) params.set(a, flavor[a]); });
            window.location.href = `shop.php?${params.toString()}`;
        } catch (e) {}
    };
});
</script>

<?php require_once __DIR__ . "/components/footer.php"; ?>

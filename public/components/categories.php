<?php
require_once __DIR__ . "/../config/urls.php";
?>

<section class="section max-w-[1440px] mx-auto px-8">
    <div class="flex flex-col items-center mb-16">
        <span class="text-xs uppercase tracking-[0.4em] text-black font-extrabold mb-4 text-center">Discovery</span>
        <h2 class="text-3xl font-heading text-center uppercase tracking-widest font-extrabold">Browse Collections</h2>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 justify-center justify-items-center categories-container">
        <!-- Categories injected here -->
    </div>
</section>

<!-- Category Detail Modal -->
</section>

<!-- Category Detail Modal -->
<div class="fixed inset-0 flex items-center justify-center z-[9999] opacity-0 invisible transition-all duration-500 bg-black/90 backdrop-blur-sm p-4 md:p-12" id="detailModalCategory">
    <div class="bg-white w-full max-w-[1000px] h-auto max-h-[90vh] flex flex-col md:flex-row relative shadow-[0_0_50px_rgba(0,0,0,0.5)] scale-95 transition-transform duration-500 overflow-hidden" id="detailModalCategoryContent">
        <button class="absolute top-4 right-4 p-2 bg-black text-white hover:bg-gray-800 z-[100] transition-colors" id="closeModalCategory">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div id="modalBodyCategory" class="flex flex-col md:flex-row w-full">
            <!-- Content injected here -->
        </div>
    </div>
</div>

<script type="module">
    import { API } from '<?= BASE_URL ?>assets/js/api-helper.js';

    let categoriesData = [];

    const fetchCategories = async () => {
        try {
            const response = await API.categories.list({ enriched: true });
            if (response.success && response.data) {
                return response.data.items || response.data || [];
            }
            return [];
        } catch (e) {
            console.error('[Categories] Failed to load categories:', e);
            return [];
        }
    };

    const renderCard = (cat) => {
        const productCount = parseInt(cat.product_count) || 0;
        return `
            <div class="card-premium group relative overflow-hidden" data-id="${cat.id}">
                <div class="card-premium-image-wrapper !bg-gray-100 overflow-hidden">
                    <img src="${cat.image_url}" 
                         alt="${cat.name}" 
                         class="card-premium-image !object-cover h-[400px] w-full transition-transform duration-1000 group-hover:scale-110" 
                         loading="lazy"
                         onerror="this.src='<?= BASE_URL ?>assets/images/placeholder-product.png'; this.onerror=null;">
                    
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                        <div class="text-white text-[10px] font-black uppercase tracking-[0.3em] border border-white/30 px-6 py-3 backdrop-blur-sm">View Series</div>
                    </div>
                </div>
                
                <div class="p-8 text-center bg-white">
                    <div class="text-[9px] uppercase tracking-[0.4em] text-black font-bold mb-3">${productCount} Varieties</div>
                    <h3 class="text-xl font-heading font-extrabold uppercase tracking-widest mb-6">${cat.name}</h3>
                    
                    <div class="flex items-stretch gap-2">
                        <a href="<?= BASE_URL ?>category.php?id=${cat.id}" class="btn-premium flex-grow text-[9px]">Browse</a>
                        <button class="btn-premium-outline w-12 flex items-center justify-center btn-details-category" data-id="${cat.id}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    };

    const renderDetail = (cat) => {
        if (!cat) return `<div class="p-20 text-center uppercase tracking-widest text-red-500">Resource Unreachable</div>`;
        return `
            <div class="w-full md:w-1/2 bg-[#f4f4f4] flex items-center justify-center p-8 md:p-12 h-[300px] md:h-auto">
                <img src="${cat.image_url}" alt="${cat.name}" class="w-full h-full object-contain max-h-[400px]" onerror="this.src='<?= BASE_URL ?>assets/images/placeholder-product.png'">
            </div>
            <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-white">
                <span class="text-[10px] font-bold uppercase tracking-[0.4em] text-gray-400 mb-2">Series Overview</span>
                <h2 class="text-2xl md:text-3xl font-serif font-bold uppercase tracking-widest mb-4 text-black border-b border-gray-100 pb-4">${cat.name}</h2>
                <p class="text-gray-500 text-sm leading-relaxed italic font-light mb-8">${cat.description || 'Our master distillers have curated this collection with uncompromising standards.'}</p>
                
                <div class="grid grid-cols-2 gap-6 mb-8 py-6 border-y border-gray-50">
                    <div>
                        <span class="block text-[9px] uppercase font-bold tracking-widest text-gray-400 mb-1">Stock Count</span>
                        <span class="text-[11px] font-bold uppercase">${cat.product_count || 0} Registered</span>
                    </div>
                    <div>
                        <span class="block text-[9px] uppercase font-bold tracking-widest text-gray-400 mb-1">Status</span>
                        <span class="text-[11px] font-bold text-green-600 uppercase">Active</span>
                    </div>
                </div>
                
                <a href="<?= BASE_URL ?>category.php?id=${cat.id}" class="bg-black text-white w-full h-12 flex items-center justify-center text-[10px] uppercase font-bold tracking-widest hover:bg-gray-800 transition-colors gap-4 group">
                    Enter Collection
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        `;
    };

    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.querySelector('.categories-container');
        categoriesData = await fetchCategories();
        if (categoriesData.length === 0) {
            container.innerHTML = '<div class="col-span-full py-32 text-center uppercase tracking-widest text-gray-400 font-bold">No collections found.</div>';
            return;
        }
        container.innerHTML = categoriesData.map(renderCard).join('');
    });

    document.addEventListener('click', async (e) => {
        const modal = document.getElementById('detailModalCategory');
        const modalContent = document.getElementById('detailModalCategoryContent');
        const body = document.getElementById('modalBodyCategory');
        const btn = e.target.closest('.btn-details-category');
        
        if (btn) {
            modal.classList.remove('opacity-0', 'invisible');
            modal.classList.add('opacity-100', 'visible');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
            document.body.style.overflow = 'hidden';

            body.innerHTML = '<div class="w-full p-20 text-center uppercase tracking-widest text-[10px] font-black animate-pulse">Loading Collection...</div>';
            const cat = categoriesData.find(c => c.id === parseInt(btn.dataset.id));
            setTimeout(() => { body.innerHTML = renderDetail(cat); }, 400);
        }

        if (e.target.closest('#closeModalCategory') || (e.target === modal)) {
            modal.classList.add('opacity-0', 'invisible');
            modal.classList.remove('opacity-100', 'visible');
            modalContent.classList.add('scale-95');
            modalContent.classList.remove('scale-100');
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('keydown', (e) => { 
        if (e.key === 'Escape') {
            const modal = document.getElementById('detailModalCategory');
            const modalContent = document.getElementById('detailModalCategoryContent');
            modal.classList.add('opacity-0', 'invisible');
            modal.classList.remove('opacity-100', 'visible');
            modalContent.classList.add('scale-95');
            modalContent.classList.remove('scale-100');
            document.body.style.overflow = '';
        }
    });
</script>

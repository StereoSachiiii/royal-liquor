<?php
/**
 * MyAccount Wishlist
 * Saved interests gallery
 */
$pageName = 'account';
$pageTitle = 'Wishlist - Royal Liquor';
require_once __DIR__ . "/_layout.php";
?>

<div class="space-y-16">
    <!-- Header -->
    <header>
        <span class="text-xs uppercase tracking-[0.4em] text-gold font-extrabold mb-4 block italic">Curated Interests</span>
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-widest leading-none">Your <br>Wishlist</h1>
    </header>

    <!-- Wishlist Gallery Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="wishlistGrid">
        <!-- Populated via JS -->
        <div class="col-span-full py-32 text-center bg-white border border-gray-100 flex flex-col items-center justify-center">
            <div class="w-20 h-20 border border-gray-50 flex items-center justify-center mb-10 opacity-10">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
            <p class="text-[11px] uppercase tracking-[0.3em] text-gray-400 font-black">Your wishlist is currently empty</p>
            <a href="<?= getPageUrl('shop') ?>" class="btn-premium mt-12 px-16">Browse Collection</a>
        </div>
    </section>
</div>

<script type="module">
import { getWishlist, removeItemFromWishlist } from '<?= BASE_URL ?>assets/js/wishlist-storage.js';
import { cart } from '<?= BASE_URL ?>assets/js/cart-service.js';
import { toast } from '<?= BASE_URL ?>assets/js/toast.js';

const fixImagePath = (url) => {
    if (!url) return '<?= BASE_URL ?>assets/images/placeholder-product.png';
    if (url.includes('products/')) {
        const filename = url.split('/').pop();
        return '<?= BASE_URL ?>assets/images/' + filename;
    }
    return '<?= BASE_URL ?>assets/images/' + url.split('/').pop();
};

function renderWishlist() {
    const wishlist = getWishlist();
    const container = document.getElementById('wishlistGrid');
    
    if (wishlist.length === 0) return;
    
    container.innerHTML = wishlist.map(item => `
        <div class="bg-white border border-gray-100 group transition-all duration-500 hover:border-gold hover:shadow-2xl relative" data-id="${item.id}">
            <!-- Visual -->
            <div class="aspect-[4/5] bg-[#fafafa] p-10 flex items-center justify-center overflow-hidden relative">
                <img src="${fixImagePath(item.image_url)}" alt="${item.name}" class="w-full h-full object-contain transition-transform duration-1000 group-hover:scale-110">
                
                <!-- Quick Removal -->
                <button class="remove-btn absolute top-6 right-6 w-10 h-10 bg-white border border-gray-100 flex items-center justify-center text-gray-300 hover:text-red-500 hover:border-red-500 transition-all duration-300 opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0" data-id="${item.id}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Meta -->
            <div class="p-8 text-center bg-white border-t border-gray-50">
                <span class="text-[9px] uppercase font-black tracking-[0.4em] text-gold mb-2 block italic">${item.category_name || 'Spirit'}</span>
                <h3 class="text-sm font-black uppercase tracking-widest mb-4 truncate">${item.name}</h3>
                <div class="flex items-center justify-center gap-6">
                    <span class="text-lg font-black tracking-tighter">$${(item.price_cents / 100).toFixed(2)}</span>
                    <a href="<?= BASE_URL ?>product.php?id=${item.id}" class="text-[9px] uppercase font-black tracking-widest text-gray-300 hover:text-black transition-colors border-b border-gray-100 pb-1">View Entry</a>
                </div>
            </div>

            <!-- Action Area -->
            <div class="p-6 border-t border-gray-50 flex items-center justify-center">
                <button class="add-to-cart w-full h-12 flex items-center justify-center gap-3 text-[10px] uppercase font-black tracking-widest hover:bg-black hover:text-white transition-all duration-500 group-active:scale-95">
                    Add to Cart
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </div>
    `).join('');

    // Re-attach listeners
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            removeItemFromWishlist(btn.dataset.id);
            renderWishlist();
            toast.success('Removed from wishlist');
        });
    });

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', async () => {
            const card = btn.closest('[data-id]');
            const id = card.dataset.id;
            const item = getWishlist().find(i => Number(i.id) === Number(id));
            
            if (item) {
                await cart.add(item.id, 1);
                toast.success(`Added ${item.name} to cart`);
                
                // Optionally remove from wishlist after adding to cart
                removeItemFromWishlist(id);
                renderWishlist();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', renderWishlist);
</script>

<?php require_once __DIR__ . "/_layout_end.php"; ?>

/**
 * Wishlist Storage Utility
 * Handles wishlist operations in localStorage only
 * Single Responsibility: Local wishlist storage management
 */

import { fetchProduct } from './products.js';
import API from './api-helper.js';

const WISHLIST_STORAGE_KEY = 'wishlist';
const WISHLIST_EXPIRATION_MS = 6 * 30 * 24 * 60 * 60 * 1000; // 6 months

/**
 * Get wishlist from localStorage
 * @returns {Array} - Wishlist items array
 */
export function getWishlist() {
    try {
        const stored = localStorage.getItem(WISHLIST_STORAGE_KEY);

        if (!stored) {
            return [];
        }

        const data = JSON.parse(stored);

        // Check expiration
        if (data.expiresAt && Date.now() > data.expiresAt) {
            localStorage.removeItem(WISHLIST_STORAGE_KEY);
            return [];
        }

        return data.items || [];
    } catch (error) {
        console.error('Error reading wishlist from storage:', error);
        return [];
    }
}

/**
 * Save wishlist to localStorage
 * @param {Array} items - Wishlist items to save
 */
function saveWishlist(items) {
    try {
        const data = {
            items,
            expiresAt: Date.now() + WISHLIST_EXPIRATION_MS
        };
        localStorage.setItem(WISHLIST_STORAGE_KEY, JSON.stringify(data));
    } catch (error) {
        console.error('Error saving wishlist to storage:', error);
    }
}

/**
 * Initializes and synchronizes the wishlist with the backend if the user is authenticated.
 */
export async function initWishlistSync() {
    try {
        const localWishlist = getWishlist();
        const localProductIds = localWishlist.map(item => Number(item.id));

        // Attempt a background sync with the backend
        // If the user isn't logged in, this route will just throw a 401 Unauthenticated error and fail gracefully
        const response = await API.wishlist.sync(localProductIds);
        
        if (response && response.success && response.data) {
            // The backend responds with the canonical, fully merged wishlist.
            // Overwrite local storage entirely with the server's record.
            saveWishlist(response.data);
            console.log('[Wishlist] Successfully synced with server.');
        }
    } catch (error) {
        // Safe to ignore: user is likely offline or logged out (401)
        console.log('[Wishlist] Running locally without sync.');
    }
}

// Automatically trigger initialization when the script is loaded
initWishlistSync();

/**
 * Add item to wishlist
 * @param {number|string} productId - Product ID
 * @returns {Promise<boolean>} - Success status
 */
export async function addItemToWishlist(productId) {
    try {
        const wishlist = getWishlist();
        const numId = Number(productId);

        // Check if already in wishlist
        if (wishlist.some(item => Number(item.id) === numId)) {
            console.log(`Product ${productId} already in wishlist`);
            return true;
        }

        // Fetch product details
        const product = await fetchProduct(numId);

        if (!product) {
            console.error(`Product ${productId} not found`);
            return false;
        }

        // Add to local wishlist
        wishlist.push(product);
        saveWishlist(wishlist);

        // Attempt to sync upwards
        try { await API.wishlist.add({ product_id: numId }); } catch(e) {}

        return true;
    } catch (error) {
        console.error('Error adding item to wishlist:', error);
        return false;
    }
}

/**
 * Remove item from wishlist
 * @param {number|string} productId - Product ID
 * @returns {boolean} - Success status
 */
export function removeItemFromWishlist(productId) {
    try {
        const wishlist = getWishlist();
        const numId = Number(productId);
        const updatedWishlist = wishlist.filter(item => Number(item.id) !== numId);

        saveWishlist(updatedWishlist);
        
        // Attempt to sync upwards
        API.wishlist.remove(numId).catch(() => {});
        
        return true;
    } catch (error) {
        console.error('Error removing item from wishlist:', error);
        return false;
    }
}

/**
 * Toggle item in wishlist (add if not present, remove if present)
 * @param {number|string} productId - Product ID
 * @returns {Promise<boolean>} - True if added, false if removed
 */
export async function toggleWishlistItem(productId) {
    if (isInWishlist(productId)) {
        removeItemFromWishlist(productId);
        return false;
    } else {
        await addItemToWishlist(productId);
        return true;
    }
}

/**
 * Clear entire wishlist
 * @returns {boolean}
 */
export function clearWishlist() {
    try {
        localStorage.removeItem(WISHLIST_STORAGE_KEY);
        return true;
    } catch (error) {
        console.error('Error clearing wishlist:', error);
        return false;
    }
}

/**
 * Check if product is in wishlist
 * @param {number|string} productId - Product ID
 * @returns {boolean} - True if in wishlist
 */
export function isInWishlist(productId) {
    const wishlist = getWishlist();
    const numId = Number(productId);
    return wishlist.some(item => Number(item.id) === numId);
}

/**
 * Get wishlist item count
 * @returns {number} - Number of items in wishlist
 */
export function getWishlistItemCount() {
    const wishlist = getWishlist();
    return wishlist.length;
}

/**
 * Move all wishlist items to cart
 * @returns {Promise<number>} - Number of items moved
 */
export async function moveWishlistToCart() {
    try {
        const wishlist = getWishlist();
        const { cart } = await import('./cart-service.js');

        let movedCount = 0;
        for (const item of wishlist) {
            await cart.add(item.id, 1, false); // Add without triggering slide-in for bulk
            movedCount++;
        }

        if (movedCount > 0) {
            clearWishlist();
        }

        return movedCount;
    } catch (error) {
        console.error('Error moving wishlist to cart:', error);
        return 0;
    }
}

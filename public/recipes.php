<?php
$pageName = 'recipes';
$pageTitle = 'Cocktail Recipes - Royal Liquor';
require_once __DIR__ . "/components/header.php";
?>

<main class="recipes-page">
    <div class="container">
        <!-- Page Header -->
        <div class="recipes-header">
            <h1 class="page-title">Cocktail Recipes</h1>
            <p class="page-subtitle">Discover delicious cocktails you can make at home with our premium spirits</p>
        </div>

        <!-- Filters -->
        <div class="recipes-filters">
            <div class="filter-group">
                <label>Difficulty</label>
                <select id="difficultyFilter">
                    <option value="">All</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="expert">Expert</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Time</label>
                <select id="timeFilter">
                    <option value="">Any Time</option>
                    <option value="5">Under 5 min</option>
                    <option value="10">Under 10 min</option>
                    <option value="15">Under 15 min</option>
                </select>
            </div>
            <div class="filter-group filter-toggle">
                <label class="toggle-label">
                    <input type="checkbox" id="canMakeFilter">
                    <span class="toggle-switch"></span>
                    <span>I Can Make</span>
                </label>
                <span class="filter-hint">Based on your purchases</span>
            </div>
        </div>

        <!-- Results Count -->
        <div class="results-bar">
            <span id="resultsCount">Loading recipes...</span>
        </div>

        <!-- Recipe Grid -->
        <div class="recipes-grid" id="recipesGrid">
            <!-- Recipes will be loaded here -->
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">🍸</div>
            <h2>No Recipes Found</h2>
            <p>Try adjusting your filters to find more cocktails</p>
            <button class="btn btn-gold" id="resetFiltersBtn">Reset Filters</button>
        </div>
    </div>
</main>

<!-- Recipe Quick View Modal -->
<div class="recipe-modal" id="recipeModal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <button class="modal-close" id="modalClose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="modal-body">
            <div class="modal-image">
                <img id="modalImage" src="" alt="">
            </div>
            <div class="modal-info">
                <div class="modal-badges" id="modalBadges"></div>
                <h2 id="modalName" class="modal-name"></h2>
                <p id="modalDescription" class="modal-description"></p>
                
                <div class="modal-ingredients">
                    <h3>Ingredients</h3>
                    <ul id="modalIngredients" class="ingredients-list"></ul>
                </div>
                
                <div class="modal-instructions">
                    <h3>Instructions</h3>
                    <div id="modalInstructions" class="instructions-text"></div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-gold btn-lg" id="addAllToCart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add All Ingredients to Cart
                    </button>
                    <a href="#" id="viewFullRecipe" class="btn btn-outline">View Full Recipe</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Recipes Page - Premium Monochrome Redesign */
:root {
    --glass-white: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(0, 0, 0, 0.05);
}

.recipes-page {
    padding: var(--space-xl) 0 var(--space-3xl);
    min-height: 100vh;
    background-color: var(--color-gray-50);
}

.recipes-header {
    text-align: center;
    margin-bottom: var(--space-3xl);
    position: relative;
}

.page-title {
    font-size: 4rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-black);
    margin-bottom: var(--space-sm);
    line-height: 1;
}

.page-subtitle {
    color: var(--color-gray-400);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.4em;
    font-weight: 800;
}

/* Premium Filter Bar */
.recipes-filters {
    display: flex;
    gap: var(--space-lg);
    align-items: flex-end;
    flex-wrap: wrap;
    padding: var(--space-xl);
    background: var(--glass-white);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-none);
    margin-bottom: var(--space-2xl);
    box-shadow: var(--shadow-sm);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
}

.filter-group label {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: var(--color-gray-400);
}

.filter-group select {
    appearance: none;
    padding: var(--space-md) var(--space-xl) var(--space-md) var(--space-md);
    border: 1px solid var(--color-gray-100);
    border-radius: var(--radius-none);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    min-width: 180px;
    background: var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 12px center;
    cursor: pointer;
    transition: all var(--transition-normal);
}

.filter-group select:hover {
    border-color: var(--color-black);
}

.filter-toggle {
    margin-left: auto;
    border-left: 1px solid var(--color-gray-100);
    padding-left: var(--space-xl);
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    cursor: pointer;
    font-weight: 900;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-black);
}

.toggle-label input {
    display: none;
}

.toggle-switch {
    width: 40px;
    height: 20px;
    background: var(--color-gray-200);
    position: relative;
    transition: background var(--transition-fast);
}

.toggle-switch::after {
    content: '';
    position: absolute;
    width: 14px;
    height: 14px;
    background: var(--white);
    top: 3px;
    left: 3px;
    transition: transform var(--transition-fast);
}

.toggle-label input:checked + .toggle-switch {
    background: var(--color-black);
}

.toggle-label input:checked + .toggle-switch::after {
    transform: translateX(20px);
}

/* Recipe Grid */
.recipes-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-xl);
}

/* Premium Card */
.recipe-card {
    background: var(--white);
    position: relative;
    transition: all var(--transition-slow);
    cursor: pointer;
}

.recipe-card:hover {
    transform: translateY(-8px);
}

.recipe-card-image {
    aspect-ratio: 4/5;
    overflow: hidden;
    position: relative;
    background: var(--color-gray-100);
}

.recipe-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(100%);
    transition: transform var(--transition-slow), filter var(--transition-slow);
}

.recipe-card:hover .recipe-card-image img {
    transform: scale(1.05);
    filter: grayscale(0%);
}

.recipe-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent 50%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: var(--space-xl);
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.recipe-card:hover .recipe-card-overlay {
    opacity: 1;
}

.recipe-badges {
    position: absolute;
    top: var(--space-md);
    left: var(--space-md);
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
    z-index: 5;
}

.recipe-badge {
    padding: 6px 12px;
    font-size: 8px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    background: var(--white);
    color: var(--color-black);
}

.recipe-badge.can-make {
    background: var(--color-black);
    color: var(--white);
}

.recipe-badge.time {
    background: var(--color-gray-100);
}

.recipe-card-content {
    padding: var(--space-lg) 0;
}

.recipe-card-name {
    font-size: 1.25rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-black);
    margin-bottom: var(--space-xs);
    line-height: 1.2;
}

.recipe-card-description {
    font-size: 11px;
    color: var(--color-gray-400);
    line-height: 1.6;
    margin-bottom: var(--space-md);
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.recipe-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: var(--color-gray-300);
    border-top: 1px solid var(--color-gray-50);
    padding-top: var(--space-md);
}

.recipe-card-meta b {
    color: var(--color-black);
}

/* Modal Redesign */
.recipe-modal {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
}

.recipe-modal.active {
    display: flex;
}

.modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(10px);
}

.modal-content {
    position: relative;
    width: 95%;
    max-width: 1200px;
    height: 80vh;
    background: var(--white);
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    overflow: hidden;
    animation: modalIn var(--transition-slow);
}

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.modal-image {
    background: var(--color-gray-100);
}

.modal-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modal-info {
    padding: var(--space-3xl);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.modal-close {
    position: absolute;
    top: var(--space-xl);
    right: var(--space-xl);
    background: none;
    border: none;
    color: var(--color-black);
    cursor: pointer;
    z-index: 10;
    padding: 10px;
    transition: transform var(--transition-normal);
}

.modal-close:hover {
    transform: rotate(90deg);
}

.modal-name {
    font-size: 3rem;
    font-weight: 900;
    text-transform: uppercase;
    line-height: 1;
    margin-bottom: var(--space-sm);
}

.modal-description {
    font-size: 14px;
    line-height: 1.8;
    color: var(--color-gray-500);
    margin-bottom: var(--space-2xl);
    max-width: 500px;
}

.modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-2xl);
    margin-bottom: var(--space-3xl);
}

.modal-grid h3 {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.3em;
    color: var(--color-gray-400);
    margin-bottom: var(--space-lg);
    border-bottom: 2px solid var(--color-black);
    padding-bottom: var(--space-xs);
    display: inline-block;
}

.ingredients-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.ingredients-list li {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.ingredient-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.ingredient-qty {
    color: var(--color-gray-400);
}

.instructions-text {
    font-size: 13px;
    line-height: 2;
    color: var(--color-gray-600);
}

.modal-actions {
    margin-top: auto;
}

.results-bar {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.3em;
    color: var(--color-gray-300);
    margin-bottom: var(--space-md);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--space-3xl);
    background: var(--white);
    border: 1px solid var(--color-gray-100);
}

/* Responsive */
@media (max-width: 1024px) {
    .recipes-grid { grid-template-columns: repeat(2, 1fr); }
    .modal-content { grid-template-columns: 1fr; height: 95vh; }
    .modal-image { display: none; }
    .page-title { font-size: 2.5rem; }
}

@media (max-width: 640px) {
    .recipes-grid { grid-template-columns: 1fr; }
    .recipes-filters { flex-direction: column; align-items: stretch; }
    .filter-toggle { border-left: none; border-top: 1px solid var(--color-gray-100); padding-left: 0; padding-top: var(--space-xl); }
}
</style>

<script type="module">
import { API } from '<?= ASSET_URL ?>js/api-helper.js';
import { cart } from '<?= BASE_URL ?>assets/js/cart-service.js';

let allRecipes = [];
let filteredRecipes = [];
let currentRecipe = null;

const recipesGrid = document.getElementById('recipesGrid');
const resultsCount = document.getElementById('resultsCount');
const emptyState = document.getElementById('emptyState');
const difficultyFilter = document.getElementById('difficultyFilter');
const timeFilter = document.getElementById('timeFilter');
const canMakeFilter = document.getElementById('canMakeFilter');
const recipeModal = document.getElementById('recipeModal');

const init = async () => {
    await loadRecipes();
    applyFilters();
    setupEventListeners();
};

const loadRecipes = async () => {
    try {
        const response = await API.recipes.list({ limit: 50 });
        if (response.success && response.data) {
            allRecipes = response.data.items || [];
        }
    } catch (error) {
        console.error('[Recipes] Failed to load:', error);
    }
};

const applyFilters = () => {
    filteredRecipes = allRecipes.filter(recipe => {
        if (difficultyFilter.value && recipe.difficulty !== difficultyFilter.value) return false;
        if (timeFilter.value && recipe.preparation_time > parseInt(timeFilter.value)) return false;
        return true;
    });
    renderRecipes();
};

const renderRecipes = () => {
    if (filteredRecipes.length === 0) {
        recipesGrid.style.display = 'none';
        emptyState.style.display = 'block';
        resultsCount.textContent = '0 COLLECTION VINTAGES';
        return;
    }
    
    recipesGrid.style.display = 'grid';
    emptyState.style.display = 'none';
    resultsCount.textContent = `${filteredRecipes.length} COLLECTION VINTAGES`;
    
    recipesGrid.innerHTML = filteredRecipes.map(recipe => {
        const ingredients = recipe.ingredients || [];
        const image = recipe.image_url || '<?= ASSET_URL ?>images/placeholder-spirit.png';
        
        return `
            <article class="recipe-card" data-id="${recipe.id}">
                <div class="recipe-card-image">
                    <img src="${image}" alt="${recipe.name}" loading="lazy" onerror="this.src='<?= ASSET_URL ?>images/placeholder-spirit.png'">
                    <div class="recipe-badges">
                        <span class="recipe-badge">${recipe.difficulty}</span>
                        <span class="recipe-badge time">${recipe.preparation_time || 0} MIN</span>
                    </div>
                    <div class="recipe-card-overlay">
                        <span class="text-white text-[10px] font-black uppercase tracking-widest">View Details</span>
                    </div>
                </div>
                <div class="recipe-card-content">
                    <h3 class="recipe-card-name">${recipe.name}</h3>
                    <p class="recipe-card-description">${recipe.description}</p>
                    <div class="recipe-card-meta">
                        <span>Serves <b>${recipe.serves}</b></span>
                        <span><b>${ingredients.length}</b> Ingredients</span>
                    </div>
                </div>
            </article>
        `;
    }).join('');
};

const openModal = (recipeId) => {
    currentRecipe = allRecipes.find(r => r.id === recipeId);
    if (!currentRecipe) return;
    
    const image = currentRecipe.image_url || '<?= ASSET_URL ?>images/placeholder-spirit.png';
    document.getElementById('modalImage').innerHTML = `<img src="${image}" alt="${currentRecipe.name}" onerror="this.src='<?= ASSET_URL ?>images/placeholder-spirit.png'">`;
    document.getElementById('modalName').textContent = currentRecipe.name;
    document.getElementById('modalDescription').textContent = currentRecipe.description;
    document.getElementById('modalInstructions').textContent = currentRecipe.instructions;
    
    document.getElementById('modalBadges').innerHTML = `
        <span class="recipe-badge">${currentRecipe.difficulty}</span>
        <span class="recipe-badge time">${currentRecipe.preparation_time || 0} MIN</span>
    `;
    
    const ingredientsList = document.getElementById('modalIngredients');
    const ingredients = currentRecipe.ingredients || [];
    ingredientsList.innerHTML = ingredients.map(ing => `
        <li>
            <span class="ingredient-name">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                ${ing.product_name}
            </span>
            <span class="ingredient-qty">${ing.quantity} ${ing.unit}</span>
        </li>
    `).join('');
    
    recipeModal.classList.add('active');
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    recipeModal.classList.remove('active');
    document.body.style.overflow = '';
    currentRecipe = null;
};

const addAllIngredients = async () => {
    if (!currentRecipe) return;
    const productsToAdd = (currentRecipe.ingredients || [])
        .filter(ing => ing.product_id !== null)
        .map(ing => ing.product_id);
    
    if (productsToAdd.length === 0) return;
    
    for (const id of productsToAdd) {
        cart.add(id, 1, false);
    }
    
    const btn = document.getElementById('addAllToCart');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'COCKTAIL READY ✓';
    btn.classList.add('bg-green-600', 'text-white');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('bg-green-600', 'text-white');
    }, 2000);
};

const setupEventListeners = () => {
    difficultyFilter.addEventListener('change', applyFilters);
    timeFilter.addEventListener('change', applyFilters);
    canMakeFilter.addEventListener('change', applyFilters);
    
    document.getElementById('resetFiltersBtn').addEventListener('click', () => {
        difficultyFilter.value = '';
        timeFilter.value = '';
        canMakeFilter.checked = false;
        applyFilters();
    });
    
    recipesGrid.addEventListener('click', (e) => {
        const card = e.target.closest('.recipe-card');
        if (card) openModal(parseInt(card.dataset.id));
    });
    
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.querySelector('.modal-overlay').addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
    document.getElementById('addAllToCart').addEventListener('click', addAllIngredients);
};

document.addEventListener('DOMContentLoaded', init);
</script>

<?php require_once __DIR__ . "/components/footer.php"; ?>

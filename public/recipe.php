<?php
$pageName = 'recipe';
$pageTitle = 'Recipe Details - Royal Liquor';
require_once __DIR__ . "/components/header.php";

$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
?>

<main class="recipe-detail-page">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a>
            <span class="separator">›</span>
            <a href="<?= BASE_URL ?>recipes.php">Recipes</a>
            <span class="separator">›</span>
            <span class="current" id="breadcrumbRecipe">Loading...</span>
        </nav>

        <div class="recipe-grid" id="recipeContent">
            <!-- Content will be loaded via JS -->
            <div class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading recipe...</p>
            </div>
        </div>
    </div>
</main>

<style>
.recipe-detail-page {
    padding: var(--space-xl) 0 var(--space-3xl);
    min-height: calc(100vh - 200px);
}

.breadcrumb {
    padding: var(--space-lg) 0;
    font-size: 0.9rem;
    color: var(--gray-500);
}

.breadcrumb a {
    color: var(--gray-600);
    text-decoration: none;
}

.breadcrumb a:hover {
    color: var(--gold);
}

.breadcrumb .separator {
    margin: 0 var(--space-sm);
    color: var(--gray-300);
}

.breadcrumb .current {
    color: var(--black);
    font-weight: 500;
}

.recipe-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3xl);
    margin-top: var(--space-xl);
}

.loading-state {
    grid-column: 1/-1;
    text-align: center;
    padding: var(--space-3xl);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--gray-200);
    border-top-color: var(--gold);
    border-radius: 50%;
    margin: 0 auto var(--space-md);
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Recipe Image */
.recipe-image-section {
    position: sticky;
    top: 100px;
}

.recipe-main-image {
    border-radius: var(--radius-xl);
    overflow: hidden;
    aspect-ratio: 4/3;
}

.recipe-main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Recipe Info */
.recipe-info-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-xl);
}

.recipe-header {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.recipe-badges {
    display: flex;
    gap: var(--space-sm);
}

.recipe-badge {
    padding: var(--space-xs) var(--space-sm);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    border-radius: var(--radius-sm);
}

.recipe-badge.easy { background: #22c55e; color: #fff; }
.recipe-badge.medium { background: var(--gold); color: var(--black); }
.recipe-badge.expert { background: #ef4444; color: #fff; }
.recipe-badge.time { background: var(--gray-100); color: var(--gray-600); }

.recipe-title {
    font-family: var(--font-serif);
    font-size: 2.5rem;
    font-weight: 300;
    font-style: italic;
    line-height: 1.2;
}

.recipe-description {
    color: var(--gray-600);
    line-height: 1.7;
    font-size: 1.1rem;
}

/* Ingredients Section */
.ingredients-section {
    background: rgba(212, 175, 55, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.15);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
}

.section-title {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--gold);
    margin-bottom: var(--space-lg);
}

.ingredients-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.ingredient-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-md) 0;
    border-bottom: 1px dashed var(--gray-200);
}

.ingredient-row:last-child {
    border-bottom: none;
}

.ingredient-info {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.ingredient-link {
    color: var(--gold);
    font-weight: 600;
    text-decoration: none;
}

.ingredient-link:hover {
    text-decoration: underline;
}

.ingredient-optional {
    font-size: 0.8rem;
    color: var(--gray-400);
    margin-left: var(--space-xs);
}

.ingredient-qty {
    color: var(--gray-500);
    font-size: 0.95rem;
}

.ingredients-footer {
    margin-top: var(--space-xl);
    padding-top: var(--space-lg);
    border-top: 1px solid rgba(212, 175, 55, 0.2);
}

.btn-add-all {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
}

/* Instructions Section */
.instructions-section {
    padding: var(--space-xl);
    background: var(--gray-50);
    border-radius: var(--radius-lg);
}

.instruction-steps {
    counter-reset: step;
}

.instruction-step {
    display: flex;
    gap: var(--space-lg);
    margin-bottom: var(--space-lg);
}

.instruction-step:last-child {
    margin-bottom: 0;
}

.step-number {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    background: var(--gold);
    color: var(--black);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
}

.step-text {
    color: var(--gray-700);
    line-height: 1.7;
    padding-top: 4px;
}

/* Responsive */
@media (max-width: 1024px) {
    .recipe-grid {
        grid-template-columns: 1fr;
    }
    
    .recipe-image-section {
        position: static;
    }
}

@media (max-width: 768px) {
    .recipe-title {
        font-size: 1.75rem;
    }
}
</style>

<script type="module">
import { API } from '<?= BASE_URL ?>assets/js/api-helper.js';
import { cart } from '<?= BASE_URL ?>assets/js/cart-service.js';

const recipeId = <?= $recipeId ?>;
let currentRecipe = null;

// Initialize
const init = async () => {
    await loadRecipe();
};

// Load recipe data
const loadRecipe = async () => {
    try {
        const response = await API.recipes.get(recipeId);
        if (response.success && response.data) {
            currentRecipe = response.data;
            renderRecipe();
        } else {
            showError('Recipe not found');
        }
    } catch (error) {
        console.error('[Recipe] Failed to load:', error);
        showError('Failed to load recipe');
    }
};

// Render recipe content
const renderRecipe = () => {
    if (!currentRecipe) return;
    
    document.title = `${currentRecipe.name} - Royal Liquor`;
    document.getElementById('breadcrumbRecipe').textContent = currentRecipe.name;
    
    // Parse instructions into steps
    const steps = currentRecipe.instructions.split('\n').filter(s => s.trim());
    
    const content = document.getElementById('recipeContent');
    content.innerHTML = `
        <div class="recipe-image-section">
            <div class="recipe-main-image">
                <img src="${currentRecipe.image_url}" alt="${currentRecipe.name}">
            </div>
        </div>
        
        <div class="recipe-info-section">
            <div class="recipe-header">
                <div class="recipe-badges">
                    <span class="recipe-badge ${currentRecipe.difficulty}">${currentRecipe.difficulty}</span>
                    <span class="recipe-badge time">${currentRecipe.preparation_time} min</span>
                    <span class="recipe-badge">Serves ${currentRecipe.serves}</span>
                </div>
                <h1 class="recipe-title">${currentRecipe.name}</h1>
                <p class="recipe-description">${currentRecipe.description}</p>
            </div>
            
            <div class="ingredients-section">
                <h2 class="section-title">Ingredients</h2>
                <ul class="ingredients-list">
                    ${(currentRecipe.ingredients || []).map(ing => {
                        const hasProduct = ing.product_id !== null;
                        return `
                            <li class="ingredient-row">
                                <span class="ingredient-info">
                                    ${hasProduct 
                                        ? `<a href="product.php?id=${ing.product_id}" class="ingredient-link">${ing.product_name}</a>`
                                        : `<span>${ing.product_name}</span>`
                                    }
                                    ${ing.is_optional ? '<span class="ingredient-optional">(optional)</span>' : ''}
                                </span>
                                <span class="ingredient-qty">${ing.quantity} ${ing.unit}</span>
                            </li>
                        `;
                    }).join('')}
                </ul>
                <div class="ingredients-footer">
                    <button class="btn btn-gold btn-lg btn-add-all" id="addAllBtn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add All Ingredients to Cart
                    </button>
                </div>
            </div>
            
            <div class="instructions-section">
                <h2 class="section-title">Instructions</h2>
                <div class="instruction-steps">
                    ${steps.map((step, i) => {
                        // Remove leading numbers if present
                        const cleanStep = step.replace(/^\d+[\.\)]\s*/, '');
                        return `
                            <div class="instruction-step">
                                <span class="step-number">${i + 1}</span>
                                <p class="step-text">${cleanStep}</p>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        </div>
    `;
    
    // Add event listener for add all button
    document.getElementById('addAllBtn').addEventListener('click', addAllIngredients);
};

// Add all ingredients to cart
const addAllIngredients = async () => {
    if (!currentRecipe) return;
    
    const ingredients = currentRecipe.ingredients || [];
    const productsToAdd = ingredients
        .filter(ing => ing.product_id !== null)
        .map(ing => ing.product_id);
    
    if (productsToAdd.length === 0) {
        alert('No purchasable ingredients in this recipe!');
        return;
    }
    
    for (const productId of productsToAdd) {
        cart.add(productId, 1, false); // Add without slide-in for multiple items
    }
    
    const btn = document.getElementById('addAllBtn');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '✓ Added to Cart!';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }, 2000);
};

// Show error state
const showError = (message) => {
    document.getElementById('recipeContent').innerHTML = `
        <div class="error-state" style="grid-column: 1/-1; text-align: center; padding: var(--space-3xl);">
            <div style="font-size: 4rem; margin-bottom: var(--space-lg);">🍹</div>
            <h2>${message}</h2>
            <p style="color: var(--gray-500); margin: var(--space-md) 0 var(--space-xl);">
                The recipe you're looking for couldn't be found.
            </p>
            <a href="recipes.php" class="btn btn-gold">Browse All Recipes</a>
        </div>
    `;
};

document.addEventListener('DOMContentLoaded', init);
</script>

<?php require_once __DIR__ . "/components/footer.php"; ?>

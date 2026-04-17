/**
 * RecipeIngredients.js — Modernized Recipe Ingredients domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:ring:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchIngredients(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.RECIPE_INGREDIENTS.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch ingredients');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[RecipeIngredients] Fetch failed', err);
        return [];
    }
}

async function fetchIngredient(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('recipe_ingredients', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch ingredient details');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchProducts() {
    try { const res = await apiRequest(API_ROUTES.PRODUCTS.LIST + '?limit=200'); return res.success ? (res.data || []) : []; }
    catch (err) { return []; }
}

async function fetchRecipes() {
    try { 
        const res = await apiRequest(API_ROUTES.COCKTAIL_RECIPES.LIST + '?limit=200'); 
        if (!res.success) return [];
        return Array.isArray(res.data) ? res.data : (res.data?.items || []);
    } catch (err) { return []; }
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(i) {
    const typeBadge = i.is_optional 
        ? `<span class="badge badge-warning" style="font-size:10px;">Optional</span>` 
        : `<span class="badge badge-active" style="font-size:10px;">Required</span>`;

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(i.id))}</td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(i.recipe_name || 'Individual Recipe')}</div>
            <div class="text-slate-500 font-mono" style="font-size:10px;">Map ID: #${i.recipe_id}</div>
        </td>
        <td class="td">
            <div class="font-semibold text-black" style="font-size:12px;">${escapeHtml(i.product_name || 'Individual Product')}</div>
            <div class="text-slate-400 font-mono" style="font-size:10px;">PID: #${i.product_id}</div>
        </td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">${i.quantity} <span class="text-slate-400 font-normal uppercase" style="font-size:9px;">${escapeHtml(i.unit || '')}</span></div>
        </td>
        <td class="td">${typeBadge}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${i.id}" title="View Details">👁</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${i.id}" title="Edit Mapping">✏️</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${i.id}" title="Unlink"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="6" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(i) {
    const cost = ((i.product_price_cents || 0) / 100).toFixed(2);
    return `
        <div class="flex flex-col" style="gap:24px;">
            <!-- Header Section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div class="flex items-center" style="gap:16px;">
                    <div class="thumb-lg rounded-2xl bg-slate-50 border flex items-center justify-center text-2xl shadow-inner">🧪</div>
                    <div>
                         <h3 class="font-bold text-black" style="font-size:20px;letter-spacing:-0.02em;">Atomic Mapping Forensic #${i.id}</h3>
                         <p class="text-sm text-slate-500 font-mono">Registered ${formatDate(i.created_at)}</p>
                    </div>
                </div>
                <div class="flex flex-col items-end" style="gap:6px;">
                    <span class="badge ${i.is_optional ? 'badge-warning' : 'badge-active'} uppercase" style="font-size:11px;padding:4px 12px;">${i.is_optional ? 'OPTIONAL' : 'ESSENTIAL'}</span>
                </div>
            </div>

            <div class="flex" style="gap:24px;">
                <!-- Mapping Identity -->
                <div style="flex:1.5;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:20px;background:var(--slate-50);">
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:16px;">Allocation Properties</h4>
                         <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            <div class="google-card" style="padding:20px;background:white;">
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Allocated Quantity</div>
                                <div class="font-black text-black" style="font-size:26px;font-family:monospace;">${i.quantity} <span class="text-slate-300 font-normal uppercase" style="font-size:12px;">${escapeHtml(i.unit)}</span></div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Per Single Yield</div>
                            </div>
                            <div class="google-card" style="padding:16px;background:white;">
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Market Context</div>
                                <div class="font-bold text-black" style="font-size:18px;">Rs ${cost}</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">UNIT COST REF</div>
                            </div>
                         </div>
                    </div>

                    <div class="google-card" style="padding:24px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:6px;">Architecture Trace</h4>
                        <div class="grid grid-cols-2 gap-12">
                             <div>
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Parent Recipe</div>
                                <div class="font-bold text-black" style="font-size:14px;">${escapeHtml(i.recipe_name)}</div>
                                <div class="text-xs text-slate-500 font-mono">RID: #${i.recipe_id}</div>
                             </div>
                             <div>
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Mapped Product</div>
                                <div class="font-bold text-black" style="font-size:14px;">${escapeHtml(i.product_name)}</div>
                                <div class="text-xs text-slate-500 font-mono">PID: #${i.product_id}</div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Guidance -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 flex flex-col gap-3">
                        <h4 class="text-indigo-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;">Atomic Integration</h4>
                        <p class="text-[12px] text-indigo-900 leading-relaxed">
                            This mapping establishes a strict dependency between a cocktail profile and its required supply node. 
                            Quantity adjustments directly impact inventory drift projections and production costs.
                        </p>
                    </div>
                    
                    <div class="google-card" style="padding:16px;background:var(--slate-50);">
                         <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:8px;">Protocol Meta</div>
                         <div class="flex justify-between" style="font-size:12px;"><span class="text-slate-500">Uniqueness ID</span><span class="font-mono text-slate-800">#${i.id}</span></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${i.id}" style="padding:0 32px;">✏️ Modify Alignment</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id = null) {
    const isEdit = id !== null;
    let i = {};
    let recipes = [];
    let products = [];
    if (isEdit) {
        [i, products] = await Promise.all([fetchIngredient(id), fetchProducts()]);
    } else {
        [recipes, products] = await Promise.all([fetchRecipes(), fetchProducts()]);
    }

    const frag = getTemplate('tpl-recipe-ingredient-form', {
        recipe_name:            escapeHtml(i.recipe_name || ''),
        context_display:        isEdit ? 'block' : 'none',
        recipe_select_display:  isEdit ? 'hidden' : 'block',
        quantity:               i.quantity || 1,
        optional_checked:       i.is_optional ? 'checked' : '',
        delete_display:         isEdit ? 'block' : 'none',
        submit_text:            isEdit ? 'Save Changes' : 'Add Ingredient'
    });

    const pSel = frag.querySelector('#ring-product-select');
    if (pSel) pSel.innerHTML = '<option value="" disabled>Search Supply Node...</option>' + products.map(p => `<option value="${p.id}" ${p.id == i.product_id ? 'selected' : ''}>${escapeHtml(p.name)} (${p.sku || 'No SKU'})</option>`).join('');

    if (!isEdit) {
        const rSel = frag.querySelector('#ring-recipe-select');
        if (rSel) rSel.innerHTML = '<option value="" disabled selected>Identify Strategy...</option>' + recipes.map(r => `<option value="${r.id}">${escapeHtml(r.name)}</option>`).join('');
    }

    const uSel = frag.querySelector('#ring-unit-select');
    if (uSel && i.unit) uSel.value = i.unit;

    if (isEdit) {
        const footer = frag.querySelector('.flex.justify-end.gap-3.pt-6');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button'; del.className = 'btn btn-outline text-danger'; del.style.marginRight = 'auto';
            del.id = 'ring-delete-btn'; del.dataset.id = id; del.innerHTML = '🗑️ Sever Mapping';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const isEdit = id !== null;
    const form   = modalRoot.querySelector('#ring-form');
    const cancel = modalRoot.querySelector('#ring-cancel');
    const delBtn = modalRoot.querySelector('#ring-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Severance?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Sever Mapping'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Severing…';
            try {
                await apiRequest(API_ROUTES.RECIPE_INGREDIENTS.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Sever Mapping'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Syncing…' : 'Executing…';
        try {
            const data = getFormData(form);
            const payload = { product_id: parseInt(data.product_id), quantity: parseFloat(data.quantity), unit: data.unit, is_optional: data.is_optional !== undefined };
            if (!isEdit) payload.recipe_id = parseInt(data.recipe_id);
            const url = isEdit ? API_ROUTES.RECIPE_INGREDIENTS.UPDATE(id) : API_ROUTES.RECIPE_INGREDIENTS.CREATE;
            await apiRequest(url, { method: isEdit ? 'PUT' : 'POST', body: payload });
            closeModal(); onSuccess?.();
        } catch (err) {
            showFormError(form, err.message);
            submit.disabled = false; submit.innerHTML = orig;
        }
    });
}

function showFormError(form, msg) {
    let el = form.querySelector('.form-error-banner');
    if (!el) { el = Object.assign(document.createElement('div'), { className: 'form-error-banner' }); form.prepend(el); }
    el.textContent = `Protocol Exception: ${msg}`; el.style.display = 'block';
}

// ─── Reload / Redraw ──────────────────────────────────────────────────────────

async function reloadIngredients(container) {
    const html = await RecipeIngredients();
    container.innerHTML = html;
    await initRecipeIngredients(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No recipe ingredients found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:180px;">Recipe Architecture Trace</th>
    <th class="th" style="min-width:180px;">Supply Node Target</th>
    <th class="th" style="width:120px;">Allocation</th>
    <th class="th" style="width:100px;">Dependency</th>
    <th class="th" style="width:160px;">Actions</th>
</tr>`;

export async function RecipeIngredients() {
    _offset = 0;
    const data = await fetchIngredients(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No recipe ingredients found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Recipe Ingredients',
        'entity-subtitle': 'Link products to cocktail recipes and manage quantities',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by product or recipe name…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ Add Ingredient';
    frag.querySelector('#entity-thead').innerHTML = THEAD;
    frag.querySelector('#entity-tbody').innerHTML = rows;

    const lmc = frag.querySelector('#entity-load-more-container');
    if (_lastResults.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    }

    return frag.firstElementChild.outerHTML;
}

// ─── Init ─────────────────────────────────────────────────────────────────────

export function initRecipeIngredients(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:ring:query', _query); _offset = 0;
        const data = await fetchIngredients(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View logic
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const it = await fetchIngredient(btn.dataset.id);
            openStandardModal({ title: 'Ingredient Details', bodyHtml: renderViewModal(it), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Ingredient', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadIngredients(container));
                    }, 200);
                }
            });
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Edit (direct)
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-edit');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const f = await renderFormModal(btn.dataset.id);
            openStandardModal({ title: 'Edit Ingredient', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadIngredients(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Delete (inline)
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-delete');
        if (!btn) return;
        const id = btn.dataset.id;
        if (!btn.dataset.confirmed) {
            btn.dataset.confirmed = '1'; btn.innerHTML = '⚠️'; btn.style.background = '#fef9c3';
            setTimeout(() => { if (btn.isConnected) { delete btn.dataset.confirmed; btn.innerHTML = '🗑'; btn.style.background = ''; }}, 3000);
            return;
        }
        btn.disabled = true; btn.innerHTML = '…';
        try {
            await apiRequest(API_ROUTES.RECIPE_INGREDIENTS.DELETE(id), { method: 'DELETE' });
            reloadIngredients(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Severance failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Add Ingredient', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadIngredients(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchIngredients(DEFAULT_LIMIT, _offset, _query);
        const list = Array.isArray(data) ? data : [];
        if (!list.length) { btn.closest('#entity-load-more-container').style.display = 'none'; return; }
        _lastResults = [..._lastResults, ...list];
        container.querySelector('#entity-tbody').insertAdjacentHTML('beforeend', list.map(renderRow).join(''));
        if (list.length < DEFAULT_LIMIT) { btn.closest('#entity-load-more-container').style.display = 'none'; }
        else { btn.disabled = false; btn.textContent = 'Load More'; }
    }, { signal });

    // Refresh
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-refresh-btn') return;
        e.target.innerHTML = '⌛'; e.target.disabled = true;
        await reloadIngredients(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
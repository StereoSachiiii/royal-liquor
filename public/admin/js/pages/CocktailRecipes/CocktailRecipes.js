/**
 * CocktailRecipes.js — Modernized Cocktail Recipes domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';
import { initImageUpload } from '../../FormHelpers.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:crec:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchRecipes(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.COCKTAIL_RECIPES.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch recipes');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[CocktailRecipes] Fetch failed', err);
        return [];
    }
}

async function fetchRecipe(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('cocktail_recipes', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch recipe details');
        return res.data;
    } catch (err) { throw err; }
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(r) {
    const diffClass = r.difficulty === 'hard' ? 'badge-danger' : r.difficulty === 'medium' ? 'badge-warning' : 'badge-active';
    const statusBadge = `<span class="badge ${r.is_active ? 'badge-active' : 'badge-inactive'} js-toggle-status cursor-pointer hover:opacity-80 transition-all" data-id="${r.id}" data-active="${r.is_active}">${r.is_active ? 'Active' : 'Draft'}</span>`;

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(r.id))}</td>
        <td class="td">
            <div class="flex items-center" style="gap:10px;">
                ${r.image_url ? `<img src="${r.image_url}" class="thumb-sm rounded-lg border shadow-xs" style="width:32px;height:32px;object-fit:cover;">` : `<div class="thumb-sm rounded-lg bg-slate-100 flex items-center justify-center text-lg border" style="width:32px;height:32px;">🍸</div>`}
                <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(r.name || 'Untitled')}</div>
            </div>
        </td>
        <td class="td">
            <div class="flex items-center" style="gap:6px;">
                <span class="badge ${diffClass}" style="font-size:10px;padding:2px 8px;">${escapeHtml(r.difficulty || 'easy')}</span>
                <span class="text-slate-500 font-mono" style="font-size:11px;">${r.preparation_time || 0}m</span>
            </div>
        </td>
        <td class="td text-slate-700 font-medium" style="font-size:12px;">Serves ${r.serves || 1}</td>
        <td class="td">${statusBadge}</td>
        <td class="td text-center">
            <span class="font-bold text-indigo-600 font-mono" style="font-size:13px;">${r.ingredient_count || 0}</span>
        </td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${r.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${r.id}" title="Edit Recipe">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${r.id}" title="Delete"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="7" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(r) {
    const ingredients = r.ingredients || [];
    const cost = (r.estimated_cost_cents || 0) / 100;
    const diffClass = r.difficulty === 'hard' ? 'badge-danger' : r.difficulty === 'medium' ? 'badge-warning' : 'badge-active';

    const ingredientsHtml = ingredients.map(ing => `
        <div class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl shadow-xs">
            <div style="display:flex;flex-direction:column;">
                <span class="text-sm font-bold text-black">${escapeHtml(ing.product_name)}</span>
                ${ing.is_optional ? '<span style="font-size:9px;" class="text-orange-500 font-bold uppercase tracking-widest">Optional</span>' : ''}
            </div>
            <span class="bg-slate-50 px-3 py-1 rounded-lg text-xs font-mono font-bold text-slate-600 border border-slate-100">${ing.quantity} ${escapeHtml(ing.unit)}</span>
        </div>
    `).join('');

    return `
        <div class="flex flex-col" style="gap:24px;">
            <!-- Header -->
            <div class="flex items-start justify-between" style="padding-bottom:20px;border-bottom:1px solid var(--slate-100);">
                <div class="flex" style="gap:20px;">
                    ${r.image_url ? `<img src="${r.image_url}" class="thumb-xl rounded-2xl border shadow-sm" style="width:120px;height:120px;object-fit:cover;">` : `<div class="thumb-xl rounded-2xl bg-slate-50 flex items-center justify-center text-5xl border border-dashed" style="width:120px;height:120px;">🍸</div>`}
                    <div>
                        <h3 class="font-bold text-black" style="font-size:28px;letter-spacing:-0.03em;line-height:1.1;">${escapeHtml(r.name)}</h3>
                        <div class="flex items-center" style="gap:10px;margin-top:8px;">
                            <span class="badge ${diffClass} uppercase" style="font-size:11px;padding:4px 12px;">${r.difficulty || 'easy'}</span>
                            <span class="text-slate-500" style="font-size:14px;">• ${r.preparation_time || 0} min prep • Yield: ${r.serves || 1} serves</span>
                        </div>
                        <p class="text-slate-600" style="font-size:14px;margin-top:12px;line-height:1.5;max-width:500px;">${escapeHtml(r.description || 'No description provided.')}</p>
                    </div>
                </div>
                <div class="flex flex-col items-end" style="gap:12px;">
                     <span class="badge ${r.is_active ? 'badge-active' : 'badge-inactive'}" style="padding:6px 16px;font-size:11px;">${r.is_active ? 'PUBLISHED' : 'DRAFT'}</span>
                     <div class="text-right">
                        <div class="font-black text-black" style="font-size:24px;font-family:monospace;">Rs ${cost.toFixed(2)}</div>
                        <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Est. Production Cost</div>
                     </div>
                </div>
            </div>

            <div class="flex" style="gap:24px;">
                <!-- Instructions -->
                <div style="flex:1.4;display:flex;flex-direction:column;gap:16px;">
                    <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">How to make it</h4>
                    <div class="google-card" style="padding:24px;background:var(--slate-50);font-size:14px;line-height:1.7;color:var(--slate-800);white-space:pre-wrap;font-weight:500;">${escapeHtml(r.instructions || 'No instructions added yet.')}</div>
                </div>

                <!-- Ingredients -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Ingredients (${ingredients.length})</h4>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        ${ingredientsHtml || '<div class="text-center py-12 text-slate-400 italic text-sm">No components mapped to this protocol.</div>'}
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center" style="padding-top:16px;border-top:1px solid var(--slate-100);">
                <div class="text-[10px] text-slate-400 font-mono italic">Trace ID: #${r.id} • Registered: ${formatDate(r.created_at)}</div>
                <button class="btn btn-primary js-edit" data-id="${r.id}" style="padding:0 32px;">✏️ Edit Recipe</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id = null) {
    const isEdit = id !== null;
    let r = {};
    if (isEdit) r = await fetchRecipe(id);

    const frag = getTemplate('tpl-cocktail-recipe-form', {
        id:               isEdit ? id : '',
        name:             escapeHtml(r.name || ''),
        description:      escapeHtml(r.description || ''),
        instructions:     escapeHtml(r.instructions || ''),
        preparation_time: r.preparation_time || 5,
        serves:           r.serves || 1,
        active_checked:   r.is_active !== false ? 'checked' : '',
        image_url:        r.image_url || '',
        preview_url:      r.image_url || '',
        preview_display:  r.image_url ? 'block' : 'hidden',
        delete_display:   isEdit ? 'block' : 'none',
        submit_text:      isEdit ? 'Save Changes' : 'Create Recipe'
    });

    const diffSel = frag.querySelector('#crec-difficulty');
    if (diffSel && isEdit) diffSel.value = r.difficulty || 'easy';

    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const isEdit = id !== null;
    const form   = modalRoot.querySelector('#crec-form');
    const cancel = modalRoot.querySelector('#crec-cancel');
    const delBtn = modalRoot.querySelector('#crec-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    initImageUpload(modalRoot, 'cocktail-recipes', 'crec-file-input', (url) => {
        modalRoot.querySelector('#crec-image-url').value = url;
        const preview = modalRoot.querySelector('#crec-preview');
        preview.src = url; preview.classList.remove('hidden');
    });

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Deletion?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Delete Recipe'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Deleting…';
            try {
                await apiRequest(API_ROUTES.COCKTAIL_RECIPES.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Delete Recipe'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Syncing…' : 'Executing…';
        try {
            const data = getFormData(form);
            const payload = { name: data.name, description: data.description || null, instructions: data.instructions || null, difficulty: data.difficulty, preparation_time: parseInt(data.preparation_time) || 0, serves: parseInt(data.serves) || 1, image_url: data.image_url || null, is_active: data.is_active !== undefined };
            const url = isEdit ? API_ROUTES.COCKTAIL_RECIPES.UPDATE(id) : API_ROUTES.COCKTAIL_RECIPES.CREATE;
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
    el.textContent = msg; el.style.display = 'block';
}

// ─── Reload / Redraw ──────────────────────────────────────────────────────────

async function reloadRecipes(container) {
    const html = await CocktailRecipes();
    container.innerHTML = html;
    await initCocktailRecipes(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No recipes found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:200px;">Recipe Name</th>
    <th class="th" style="width:140px;">Difficulty / Time</th>
    <th class="th" style="width:100px;">Serves</th>
    <th class="th" style="width:100px;">Status</th>
    <th class="th" style="width:80px;text-align:center;">Ingredients</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function CocktailRecipes() {
    _offset = 0;
    const data = await fetchRecipes(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No recipes yet. Create your first cocktail recipe.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Cocktail Recipes',
        'entity-subtitle': 'Manage your signature cocktail library',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by recipe name…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = 'Add Recipe';
    frag.querySelector('#entity-thead').innerHTML = THEAD;
    frag.querySelector('#entity-tbody').innerHTML = rows;

    const lmc = frag.querySelector('#entity-load-more-container');
    if (_lastResults.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load more</button>`;
    }

    return frag.firstElementChild.outerHTML;
}

// ─── Init ─────────────────────────────────────────────────────────────────────

export function initCocktailRecipes(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:crec:query', _query); _offset = 0;
        const data = await fetchRecipes(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const r = await fetchRecipe(btn.dataset.id);
            openStandardModal({ title: 'Cocktail Recipe Details', bodyHtml: renderViewModal(r), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Recipe', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadRecipes(container));
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
            openStandardModal({ title: 'Edit Recipe', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadRecipes(container));
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
            await apiRequest(API_ROUTES.COCKTAIL_RECIPES.DELETE(id), { method: 'DELETE' });
            reloadRecipes(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Deletion failed: ' + err.message); }
    }, { signal });

    // Toggle Status (one-click)
    container.addEventListener('click', async (e) => {
        const badge = e.target.closest('.js-toggle-status');
        if (!badge) return;
        const id = badge.dataset.id;
        const current = badge.dataset.active === 'true' || badge.dataset.active === '1';
        badge.innerHTML = '…'; badge.style.opacity = '0.5';
        try {
            await apiRequest(API_ROUTES.COCKTAIL_RECIPES.UPDATE(id), { method: 'PUT', body: { is_active: !current } });
            reloadRecipes(container);
        } catch (err) { reloadRecipes(container); alert('Status update failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'New Cocktail Recipe', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadRecipes(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchRecipes(DEFAULT_LIMIT, _offset, _query);
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
        await reloadRecipes(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
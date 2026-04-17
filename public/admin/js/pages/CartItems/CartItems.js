/**
 * CartItems.js — Modernized Cart Items domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:cart_items:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchCartItems(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.CART_ITEMS.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch cart items');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[CartItems] Fetch failed', err);
        return [];
    }
}

async function fetchCartItem(id) {
    try {
        const url = API_ROUTES.CART_ITEMS.GET(id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch cart item details');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchDependencies() {
    try {
        const [cRes, pRes] = await Promise.all([
            apiRequest(API_ROUTES.CARTS.LIST + '?limit=200'),
            apiRequest(API_ROUTES.PRODUCTS.LIST + '?limit=200')
        ]);
        return {
            carts: cRes.data?.items || cRes.data || [],
            products: pRes.data?.items || pRes.data || []
        };
    } catch (err) {
        console.error('[CartItems] Dependency fetch failed', err);
        return { carts: [], products: [] };
    }
}

// ─── Utils ───────────────────────────────────────────────────────────────────

function getStatusClass(status) {
    const s = (status || 'active').toLowerCase();
    switch (s) {
        case 'active':    return 'badge-active';
        case 'converted': return 'badge-info';
        case 'abandoned': return 'badge-warning';
        case 'expired':   return 'badge-inactive';
        default:         return 'badge-secondary';
    }
}

function formatCurrency(cents) {
    return (cents / 100).toFixed(2);
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(it) {
    const subtotal = (it.price_at_add_cents || 0) * (it.quantity || 0);
    const created = it.created_at ? formatDate(it.created_at) : '—';

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(it.id))}</td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(it.product_name || 'Individual Item')}</div>
            <div class="text-slate-500 font-mono" style="font-size:11px;">Cart Mapping: #${it.cart_id}</div>
        </td>
        <td class="td">
            <div class="font-semibold text-black" style="font-size:12px;">${escapeHtml(it.user_name || 'Guest Trace')}</div>
        </td>
        <td class="td text-center font-mono font-bold" style="font-size:13px;">x${it.quantity || 0}</td>
        <td class="td font-mono text-slate-500" style="font-size:12px;">Rs ${formatCurrency(it.price_at_add_cents || 0)}</td>
        <td class="td font-bold font-mono text-black" style="font-size:12px;">Rs ${formatCurrency(subtotal)}</td>
        <td class="td text-slate-500" style="font-size:11px;white-space:nowrap;">${created}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${it.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${it.id}" title="Adjust Fragment">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${it.id}" title="Remove"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="8" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(it) {
    const priceDiff = (it.current_price_cents || 0) - (it.price_at_add_cents || 0);

    return `
        <div class="flex flex-col" style="gap:20px;">
            <!-- Header Section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div>
                     <h3 class="font-bold text-black" style="font-size:20px;letter-spacing:-0.02em;">Interest Fragment Analyser #${it.id}</h3>
                     <p class="text-sm text-slate-500">Trace established on ${formatDate(it.created_at)}</p>
                </div>
                <div class="flex flex-col items-end" style="gap:4px;">
                    <span class="badge ${getStatusClass(it.cart_status)} uppercase" style="font-size:10px;padding:4px 10px;">Cart: ${it.cart_status || 'UNKNOWN'}</span>
                    <span class="text-slate-400 font-mono" style="font-size:11px;">Parent Cart: #${it.cart_id}</span>
                </div>
            </div>

            <div class="flex" style="gap:20px;">
                <!-- Left: Product Identity -->
                <div style="flex:1.2;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:20px;background:var(--slate-50);">
                         <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:12px;">Product Signature</div>
                         <div class="flex items-center" style="gap:16px;">
                             ${it.product_image ? `<img src="${it.product_image}" class="thumb-xl rounded-2xl border shadow-sm bg-white" alt="${escapeHtml(it.product_name)}">` : `<div class="thumb-xl rounded-2xl bg-white border flex items-center justify-center text-3xl shadow-sm">📦</div>`}
                             <div>
                                <div class="font-bold text-black" style="font-size:18px;line-height:1.2;">${escapeHtml(it.product_name)}</div>
                                <div class="text-xs text-slate-500 font-mono mt-1">Ref ID: #${it.product_id}</div>
                             </div>
                         </div>
                    </div>

                    <div class="google-card" style="padding:16px;">
                         <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:10px;">Event Actor</div>
                         <div class="flex flex-col">
                            <div class="font-bold text-black" style="font-size:14px;">${escapeHtml(it.user_name || 'Guest Actor')}</div>
                            <div class="text-xs text-slate-500">${escapeHtml(it.user_email || 'guest-session@trace.local')}</div>
                         </div>
                    </div>
                </div>

                <!-- Right: Pricing Forensic -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div style="background:var(--black);color:white;padding:24px;border-radius:16px;box-shadow:0 10px 30px -10px rgba(0,0,0,0.3);">
                        <div class="text-slate-500 font-bold uppercase text-center" style="font-size:9px;letter-spacing:0.1em;margin-bottom:16px;">Pricing Forensic Analysis</div>
                        <div class="flex justify-between" style="font-size:12px;margin-bottom:10px;"><span class="text-slate-400">Entry Point Price</span><span class="font-mono">Rs ${formatCurrency(it.price_at_add_cents)}</span></div>
                        <div class="flex justify-between" style="font-size:12px;margin-bottom:10px;"><span class="text-slate-400">Market Current</span><span class="font-mono">Rs ${formatCurrency(it.current_price_cents)}</span></div>
                        <div class="flex justify-between" style="font-size:12px;border-top:1px solid #334155;padding-top:10px;margin-bottom:16px;">
                            <span class="text-slate-400 font-black uppercase" style="font-size:10px;">Acquisition Delta</span>
                            <span class="font-mono font-bold ${priceDiff > 0 ? 'text-green-400' : (priceDiff < 0 ? 'text-red-400' : 'text-slate-500')}">
                                ${priceDiff > 0 ? '+' : ''}Rs ${formatCurrency(priceDiff)}
                            </span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-slate-800">
                             <span class="text-indigo-400 font-black uppercase" style="font-size:11px;">Total Projection</span>
                             <span class="font-mono font-black" style="font-size:26px;">Rs ${formatCurrency(it.price_at_add_cents * it.quantity)}</span>
                        </div>
                    </div>

                    <div style="padding:14px;background:var(--slate-50);border:1px solid var(--slate-200);border-radius:12px;font-style:italic;font-size:11px;color:var(--slate-500);line-height:1.5;">
                        Note: Line items represent intent points. Final settlement figures are locked upon conversion to Order Protocol.
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${it.id}" style="padding:0 32px;">✏️ Adjust Fragment</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id = null) {
    const isEdit = id !== null;
    const [deps, it] = await Promise.all([
        isEdit ? Promise.resolve({ carts: [], products: [] }) : fetchDependencies(),
        isEdit ? fetchCartItem(id) : Promise.resolve({})
    ]);

    const frag = getTemplate('tpl-cart-item-form', {
        id:              isEdit ? id : '',
        cart_id:         it.cart_id || '',
        product_name:    escapeHtml(it.product_name || ''),
        quantity:        it.quantity || 1,
        unit_price:      it.price_at_add_cents ? formatCurrency(it.price_at_add_cents) : '0.00',
        subtotal:        it.price_at_add_cents ? formatCurrency(it.price_at_add_cents * it.quantity) : '0.00',
        create_only:     isEdit ? 'hidden' : '',
        edit_only:       isEdit ? '' : 'hidden',
        create_required: isEdit ? '' : 'required'
    });

    if (!isEdit) {
        const cSel = frag.querySelector('#cit-cart-select');
        const pSel = frag.querySelector('#cit-product-select');
        
        if (cSel) {
            cSel.innerHTML = '<option value="">Select Target Cart...</option>' + 
                deps.carts.map(c => `<option value="${c.id}">Cart #${c.id} (${escapeHtml(c.user_name || 'Guest')})</option>`).join('');
        }
        if (pSel) {
            pSel.innerHTML = '<option value="">Select Product Trace...</option>' + 
                deps.products.map(p => `<option value="${p.id}">${escapeHtml(p.name)} (Rs ${formatCurrency(p.price_cents)})</option>`).join('');
        }
    }

    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const form   = modalRoot.querySelector('#cit-form');
    const cancel = modalRoot.querySelector('#cit-cancel');
    const delBtn = modalRoot.querySelector('#cit-delete-btn');
    const submit = form?.querySelector('button[type="submit"]');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
             if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Removal';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Remove Fragment'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Removing…';
            try {
                await apiRequest(API_ROUTES.CART_ITEMS.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Remove Fragment'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const isEdit = id !== null;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Saving…' : 'Adding Item…';
        try {
            const data = getFormData(form);
            const payload = { 
                quantity: parseInt(data.quantity),
                ...(isEdit ? {} : { 
                    cart_id: parseInt(data.cart_id),
                    product_id: parseInt(data.product_id)
                })
            };
            const url = isEdit ? API_ROUTES.CART_ITEMS.UPDATE(id) : API_ROUTES.CART_ITEMS.CREATE;
            await apiRequest(url, { method: isEdit ? 'PUT' : 'POST', body: payload });
            closeModal(); onSuccess?.();
        } catch (err) {
            showFormError(form, err.message);
            submit.disabled = false; submit.innerHTML = isEdit ? 'Commit Changes' : 'Execute Creation';
        }
    });
}

function showFormError(form, msg) {
    let el = form.querySelector('.form-error-banner');
    if (!el) { el = Object.assign(document.createElement('div'), { className: 'form-error-banner' }); form.prepend(el); }
    el.textContent = `Protocol Exception: ${msg}`; el.style.display = 'block';
}

// ─── Reload / Redraw ──────────────────────────────────────────────────────────

async function reloadCartItems(container) {
    const html = await CartItems();
    container.innerHTML = html;
    await initCartItems(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No cart items found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:180px;">Entity Mapping / Trace</th>
    <th class="th" style="min-width:150px;">Observed Actor</th>
    <th class="th" style="width:80px;text-align:center;">Allocated</th>
    <th class="th" style="width:110px;">Entry Unit</th>
    <th class="th" style="width:130px;">Projected Sub</th>
    <th class="th" style="width:140px;">Serialized At</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function CartItems() {
    _offset = 0;
    const data = await fetchCartItems(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No cart items found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Cart Items',
        'entity-subtitle': 'View individual products inside customer carts',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by product or cart ID…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ Add Item';
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

export function initCartItems(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:cart_items:query', _query); _offset = 0;
        const data = await fetchCartItems(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View logic
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const item = await fetchCartItem(btn.dataset.id);
            openStandardModal({ title: 'Item Selection Detail', bodyHtml: renderViewModal(item), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Cart Item', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadCartItems(container));
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
            openStandardModal({ title: 'Modify Allocation Protocol', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadCartItems(container));
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
            await apiRequest(API_ROUTES.CART_ITEMS.DELETE(id), { method: 'DELETE' });
            reloadCartItems(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Removal failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Draft Interest Fragment', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadCartItems(container));
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchCartItems(DEFAULT_LIMIT, _offset, _query);
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
        await reloadCartItems(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}

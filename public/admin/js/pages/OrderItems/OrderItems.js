/**
 * OrderItems.js — Modernized Order Items domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:order_items:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchOrderItems(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.ORDER_ITEMS.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch order items');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[OrderItems] Fetch failed', err);
        return [];
    }
}

async function fetchOrderItem(id) {
    try {
        const url = API_ROUTES.ORDER_ITEMS.GET(id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch order item details');
        return res.data;
    } catch (err) { throw err; }
}

// ─── Utils ───────────────────────────────────────────────────────────────────

function getStatusClass(status) {
    const s = (status || 'pending').toLowerCase();
    switch (s) {
        case 'delivered': case 'completed': case 'paid': return 'badge-active';
        case 'processing': case 'shipped':                return 'badge-info';
        case 'cancelled': case 'returned':                return 'badge-inactive';
        case 'pending':                                   return 'badge-warning';
        default:                                         return 'badge-info';
    }
}

function formatCurrency(cents) {
    return (cents / 100).toFixed(2);
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(it) {
    const statusBadge = `<span class="badge ${getStatusClass(it.order_status)}" style="font-size:10px;">${escapeHtml(it.order_status || 'N/A')}</span>`;
    const created = it.created_at ? formatDate(it.created_at) : '—';
    const subtotal = (it.price_cents || 0) * (it.quantity || 0);

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(it.id))}</td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(it.product_name || 'Unknown Product')}</div>
            <div class="text-slate-500 font-mono" style="font-size:11px;">Order Reference: #${it.order_id}</div>
        </td>
        <td class="td">
            <div class="font-semibold text-black" style="font-size:12px;">${escapeHtml(it.user_name || 'Anonymous')}</div>
            <div class="text-slate-500" style="font-size:11px;">${escapeHtml(it.user_email || '')}</div>
        </td>
        <td class="td text-center font-mono font-bold" style="font-size:13px;">x${it.quantity || 0}</td>
        <td class="td font-mono text-slate-500" style="font-size:12px;">Rs ${formatCurrency(it.price_cents || 0)}</td>
        <td class="td font-bold font-mono text-black" style="font-size:12px;">Rs ${formatCurrency(subtotal)}</td>
        <td class="td">${statusBadge}</td>
        <td class="td text-slate-500" style="font-size:11px;white-space:nowrap;">${created}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${it.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${it.id}" title="Adjust Item">✏️ Edit</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="9" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(it) {
    const subtotal = (it.price_cents || 0) * (it.quantity || 0);

    return `
        <div class="flex flex-col" style="gap:20px;">
            <!-- Header Section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div>
                     <h3 class="font-bold text-black" style="font-size:20px;letter-spacing:-0.02em;">Line Item Analysis #${it.id}</h3>
                     <p class="text-sm text-slate-500">Atomic Event Snapshot • ${formatDate(it.created_at)}</p>
                </div>
                <div class="flex flex-col items-end" style="gap:4px;">
                    <span class="badge ${getStatusClass(it.order_status)} uppercase" style="font-size:10px;padding:4px 10px;">${it.order_status || 'UNKNOWN'}</span>
                    <span class="text-slate-400 font-mono" style="font-size:11px;">Order: #${it.order_id}</span>
                </div>
            </div>

            <div class="flex" style="gap:20px;">
                <!-- Left: Product & Context -->
                <div style="flex:1.2;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:16px;background:var(--slate-50);">
                         <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:8px;">Product Identification</div>
                         <div class="font-bold text-black" style="font-size:16px;">${escapeHtml(it.product_name)}</div>
                         <div class="flex justify-between" style="margin-top:12px;font-size:12px;color:var(--slate-500);">
                            <span>Catalog ID</span><span class="font-mono text-black font-semibold">#${it.product_id}</span>
                         </div>
                         <div class="flex justify-between" style="margin-top:4px;font-size:12px;color:var(--slate-500);">
                            <span>Current SKU Name</span><span class="text-black">${escapeHtml(it.current_product_name || 'N/A')}</span>
                         </div>
                    </div>

                    <div class="google-card" style="padding:16px;">
                         <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:10px;">Logistics Protocol</div>
                         <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div style="background:var(--slate-50);padding:10px;border-radius:8px;">
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Warehouse ID</div>
                                <div class="font-mono font-bold text-black" style="font-size:13px;">${it.warehouse_id || 'Global'}</div>
                            </div>
                            <div style="background:var(--slate-50);padding:10px;border-radius:8px;">
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Qty Dispatched</div>
                                <div class="font-mono font-bold text-black" style="font-size:13px;">x${it.quantity}</div>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- Right: Financials & Actor -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div style="background:var(--black);color:white;padding:20px;border-radius:16px;box-shadow:0 10px 30px -10px rgba(0,0,0,0.3);">
                        <div class="text-slate-500 font-bold uppercase text-center" style="font-size:9px;letter-spacing:0.1em;margin-bottom:12px;">Financial Analysis</div>
                        <div class="flex justify-between" style="font-size:12px;margin-bottom:8px;"><span class="text-slate-400">Fixed Unit Price</span><span class="font-mono">Rs ${formatCurrency(it.price_cents)}</span></div>
                        <div class="flex justify-between" style="font-size:12px;border-top:1px solid #334155;padding-top:8px;margin-bottom:12px;"><span class="text-slate-400">Multiplier</span><span class="font-mono">x${it.quantity}</span></div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-indigo-400 font-black uppercase" style="font-size:12px;">Extended Total</span>
                            <span class="font-mono font-black" style="font-size:24px;">Rs ${formatCurrency(subtotal)}</span>
                        </div>
                    </div>

                    <div class="google-card" style="padding:16px;">
                         <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:8px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Transaction Actor</div>
                         <div class="flex flex-col">
                            <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(it.user_name || 'Anonymous Client')}</div>
                            <div class="text-xs text-slate-500">${escapeHtml(it.user_email || 'no-email@synced')}</div>
                         </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${it.id}" style="padding:0 32px;">✏️ Adjust Item</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id) {
    const it = await fetchOrderItem(id);
    const frag = getTemplate('tpl-order-item-form', {
        id:           it.id,
        order_id:     it.order_id,
        product_name: escapeHtml(it.product_name),
        quantity:     it.quantity,
        unit_price:   formatCurrency(it.price_cents),
        subtotal:     formatCurrency(it.price_cents * it.quantity),
        warehouse_id: it.warehouse_id || ''
    });

    const submit = frag.querySelector('#oit-submit');
    if (submit) submit.innerHTML = 'Save Changes';

    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const form   = modalRoot.querySelector('#oit-form');
    const cancel = modalRoot.querySelector('#oit-cancel');
    const submit = modalRoot.querySelector('#oit-submit');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!submit.dataset.confirmed) {
            submit.dataset.confirmed = '1';
            submit.innerHTML = '⚠️ Confirm Adjust?';
            submit.classList.remove('btn-primary');
            submit.classList.add('btn-warning');
            setTimeout(() => { if (submit.dataset.confirmed) { delete submit.dataset.confirmed; submit.innerHTML = 'Save Changes'; submit.classList.add('btn-primary'); submit.classList.remove('btn-warning'); }}, 3000);
            return;
        }
        submit.disabled = true; submit.innerHTML = 'Syncing Correction…';
        try {
            const data = getFormData(form);
            const payload = { quantity: parseInt(data.quantity), warehouse_id: data.warehouse_id ? parseInt(data.warehouse_id) : null };
            await apiRequest(API_ROUTES.ORDER_ITEMS.UPDATE(id), { method: 'PUT', body: payload });
            closeModal(); onSuccess?.();
        } catch (err) {
            showFormError(form, err.message);
            delete submit.dataset.confirmed; submit.disabled = false; submit.innerHTML = 'Save Changes';
            submit.classList.add('btn-primary'); submit.classList.remove('btn-warning');
        }
    });
}

function showFormError(form, msg) {
    let el = form.querySelector('.form-error-banner');
    if (!el) { el = Object.assign(document.createElement('div'), { className: 'form-error-banner' }); form.prepend(el); }
    el.textContent = `Error: ${msg}`; el.style.display = 'block';
}

// ─── Reload / Redraw ──────────────────────────────────────────────────────────

async function reloadOrderItems(container) {
    const html = await OrderItems();
    container.innerHTML = html;
    await initOrderItems(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No granular line items found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:180px;">Product / Item Trace</th>
    <th class="th" style="min-width:150px;">Actor Profile</th>
    <th class="th" style="width:70px;text-align:center;">Qty</th>
    <th class="th" style="width:100px;">Unit Price</th>
    <th class="th" style="width:120px;">Snapshot Total</th>
    <th class="th" style="width:110px;">Order Status</th>
    <th class="th" style="width:130px;">Recorded At</th>
    <th class="th" style="width:150px;">Actions</th>
</tr>`;

export async function OrderItems() {
    _offset = 0;
    const data = await fetchOrderItems(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No granular line items found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Order Items',
        'entity-subtitle': 'Atomic transactional analysis of individual line items',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by Product, Order # or User…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').style.display = 'none';
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

export function initOrderItems(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:order_items:query', _query); _offset = 0;
        const data = await fetchOrderItems(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => {
        if (e.target.id === 'entity-search') performSearch(e.target.value.trim());
    }, { signal });

    // View Detail
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const it = await fetchOrderItem(btn.dataset.id);
            openStandardModal({ title: 'Order Item Details', bodyHtml: renderViewModal(it), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Order Item', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadOrderItems(container));
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
            openStandardModal({ title: 'Edit Order Item', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadOrderItems(container));
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchOrderItems(DEFAULT_LIMIT, _offset, _query);
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
        await reloadOrderItems(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
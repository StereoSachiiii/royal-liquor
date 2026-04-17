/**
 * Warehouses.js — Warehouses management module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, formatNumber, debounce, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';
import { uploadImage } from '../../FormHelpers.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = '';
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchWarehouses(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.LIST('warehouses') + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch warehouses');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Warehouses] Fetch failed', err);
        return [];
    }
}

async function fetchWarehouse(id) {
    try {
        // Use the admin detail view which includes stock aggregates
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('warehouses', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch warehouse details');
        return res.data;
    } catch (err) { throw err; }
}

// ─── Row Renderer ─────────────────────────────────────────────────────────────

function renderRow(war) {
    const isActive = war.is_active !== false && war.is_active !== 'f';
    const statusBadge = isActive
        ? `<span class="inline-flex items-center px-2 py-1 text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 rounded-none">Active</span>`
        : `<span class="inline-flex items-center px-2 py-1 text-[9px] font-black uppercase tracking-wider bg-gray-100 text-gray-400 rounded-none">Inactive</span>`;

    const image = war.image_url
        ? `<img src="${escapeHtml(war.image_url)}" class="w-8 h-8 object-cover border border-gray-100 rounded-sm flex-shrink-0" alt="${escapeHtml(war.name)}">`
        : `<div class="w-8 h-8 bg-gray-50 border border-gray-100 flex items-center justify-center text-xs flex-shrink-0">🏢</div>`;

    const stock = war.available_stock ?? 0;
    const skus  = war.unique_products ?? 0;

    return `<tr class="group hover:bg-gray-50/50 transition-colors">
        <td class="px-6 py-4 text-[10px] font-bold text-gray-300 font-mono whitespace-nowrap">#${escapeHtml(String(war.id))}</td>
        <td class="px-6 py-4">
            <div class="flex items-center gap-3">
                ${image}
                <div>
                    <div class="font-semibold text-black text-sm">${escapeHtml(war.name || '—')}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5 truncate max-w-[180px]">${escapeHtml(war.address || 'No address recorded')}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4">
            <div class="font-medium text-gray-700 text-sm tabular-nums">${formatNumber(stock)} available</div>
            <div class="text-[10px] text-gray-400 mt-0.5">${skus} SKUs</div>
        </td>
        <td class="px-6 py-4 text-gray-600 text-sm font-mono">${escapeHtml(war.phone || '—')}</td>
        <td class="px-6 py-4">${statusBadge}</td>
        <td class="px-6 py-4 text-gray-400 text-[10px] whitespace-nowrap">${formatDate(war.updated_at || war.created_at)}</td>
        <td class="px-6 py-4">
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-black hover:bg-black hover:text-white transition-all js-view" data-id="${war.id}" title="View details"><span class="text-[10px]">👁</span></button>
                <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-black hover:bg-black hover:text-white transition-all js-edit" data-id="${war.id}" title="Edit warehouse"><span class="text-[10px]">✏️</span></button>
                <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-red-600 hover:bg-red-600 hover:text-white transition-all js-delete" data-id="${war.id}" title="Delete warehouse"><span class="text-[10px]">🗑</span></button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr><td colspan="7" class="px-6 py-16 text-center text-sm text-gray-400">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(war) {
    const isActive = war.is_active !== false && war.is_active !== 'f';
    const totalQty   = parseInt(war.total_quantity)   || 0;
    const reserved   = parseInt(war.total_reserved)   || 0;
    const available  = parseInt(war.total_available)  || 0;
    const entries    = parseInt(war.total_stock_entries) || 0;
    const skus       = parseInt(war.unique_products)  || 0;

    const lowStockRows = Array.isArray(war.low_stock_items) && war.low_stock_items.length
        ? war.low_stock_items.map(item => `
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="text-sm font-medium text-black">${escapeHtml(item.product_name)}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 ${item.quantity <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'} rounded">${item.available} left</span>
            </div>`).join('')
        : `<div class="py-6 text-center text-sm text-gray-400">All products are well stocked.</div>`;

    return `
        <div class="flex flex-col" style="gap:24px;">
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div class="flex items-center" style="gap:16px;">
                    ${war.image_url ? `<img src="${escapeHtml(war.image_url)}" class="thumb-xl rounded-2xl border shadow-md" alt="${escapeHtml(war.name)}">` : `<div class="thumb-xl rounded-2xl bg-slate-50 border flex items-center justify-center text-3xl shadow-sm">🏢</div>`}
                    <div>
                         <h3 class="font-bold text-black" style="font-size:22px;letter-spacing:-0.02em;">${escapeHtml(war.name)}</h3>
                         <div style="margin-top:6px;">
                            ${isActive ? `<span class="badge badge-active">Active</span>` : `<span class="badge badge-inactive">Inactive</span>`}
                         </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="flex" style="gap:12px;">
                <div class="google-card flex-1 text-center" style="padding:16px;">
                    <div class="uppercase text-slate-400 font-bold" style="font-size:10px;letter-spacing:0.1em;margin-bottom:4px;">Stock Entries</div>
                    <div class="font-bold text-black" style="font-size:24px;">${entries}</div>
                </div>
                <div class="google-card flex-1 text-center" style="padding:16px;">
                    <div class="uppercase text-slate-400 font-bold" style="font-size:10px;letter-spacing:0.1em;margin-bottom:4px;">Unique SKUs</div>
                    <div class="font-bold text-black" style="font-size:24px;">${skus}</div>
                </div>
                <div class="google-card flex-1 text-center" style="padding:16px;">
                    <div class="uppercase text-slate-400 font-bold" style="font-size:10px;letter-spacing:0.1em;margin-bottom:4px;">Total Units</div>
                    <div class="font-bold text-black" style="font-size:24px;font-family:monospace;">${formatNumber(totalQty)}</div>
                </div>
                <div class="google-card flex-1 text-center" style="padding:16px;background:var(--success-surface,#f0fdf4);">
                    <div class="uppercase text-green-600 font-bold" style="font-size:10px;letter-spacing:0.1em;margin-bottom:4px;">Available</div>
                    <div class="font-bold text-green-700" style="font-size:24px;font-family:monospace;">${formatNumber(available)}</div>
                </div>
            </div>

            <div class="flex" style="gap:20px;">
                <!-- Left: Details -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:16px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Warehouse Details</h4>
                        <div class="flex flex-col" style="gap:8px;">
                            ${[
                                ['ID', `#${war.id}`],
                                ['Created', formatDate(war.created_at)],
                                ['Last Updated', formatDate(war.updated_at)],
                                ['Phone', war.phone || '—'],
                                ['Reserved Units', formatNumber(reserved)],
                                ['Status', isActive ? 'Active' : 'Inactive']
                            ].map(([l, v]) => `
                                <div class="flex justify-between" style="font-size:12px;padding:4px 0;">
                                    <span class="text-slate-500">${l}</span>
                                    <span class="font-bold text-black">${v}</span>
                                </div>`).join('')}
                        </div>
                    </div>
                    <div class="google-card" style="padding:16px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:8px;">Address</h4>
                        <p class="text-black" style="font-size:14px;line-height:1.6;">
                            ${escapeHtml(war.address || 'No address recorded.')}
                        </p>
                    </div>
                </div>

                <!-- Right: Low Stock Alert -->
                <div style="flex:1.2;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:16px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Low Stock Items</h4>
                        ${lowStockRows}
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${war.id}" style="padding:0 32px;">✏️ Edit Warehouse</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(warehouseId = null) {
    const isEdit = warehouseId !== null;
    let war = {};
    if (isEdit) war = await fetchWarehouse(warehouseId);

    const frag = getTemplate('tpl-warehouse-form', {
        name:              escapeHtml(war.name || ''),
        address:           escapeHtml(war.address || ''),
        phone:             escapeHtml(war.phone || ''),
        image_url:         escapeHtml(war.image_url || ''),
        image_display:     war.image_url ? 'block' : 'none',
        is_active_checked: war.is_active !== false ? 'checked' : '',
        submit_text:       isEdit ? 'Save Changes' : 'Create Warehouse',
        stats_display:     isEdit ? 'block' : 'none',
        stock_entries:     war.total_stock_entries ?? 0,
        product_count:     war.unique_products ?? 0,
        created_at:        formatDate(war.created_at)
    });

    if (isEdit) {
        const footer = frag.querySelector('.war-form-footer');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-outline text-danger';
            del.style.marginRight = 'auto';
            del.id = 'war-delete-btn';
            del.dataset.id = warehouseId;
            del.innerHTML = '🗑️ Delete Warehouse';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, warehouseId, onSuccess) {
    const isEdit = warehouseId !== null;
    const form   = modalRoot.querySelector('#war-form');
    const cancel = modalRoot.querySelector('#war-cancel');
    const delBtn = modalRoot.querySelector('#war-delete-btn');
    const imgInp = modalRoot.querySelector('#war-image-file');
    const imgPre = modalRoot.querySelector('#war-image-preview');
    const imgHid = modalRoot.querySelector('#war-image-hidden');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (imgInp) {
        imgInp.addEventListener('change', async (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            const label = modalRoot.querySelector('label[for="war-image-file"]');
            if (label) label.textContent = 'Uploading…';
            try {
                const url = await uploadImage(file, 'warehouses');
                if (imgHid) imgHid.value = url;
                if (imgPre) { imgPre.src = url; imgPre.style.display = 'block'; }
                if (label) label.textContent = '✅ Image uploaded';
            } catch (err) {
                if (label) label.textContent = '❌ Upload failed';
                console.error('[Warehouses] Image upload error:', err);
            }
        });
    }

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Click again to confirm';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Delete Warehouse'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Deleting…';
            try {
                await apiRequest(API_ROUTES.WAREHOUSES.DELETE(warehouseId), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) {
                showFormError(form, err.message);
                delBtn.disabled = false; delBtn.innerHTML = '🗑️ Delete Warehouse';
                delete delBtn.dataset.confirmed;
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Saving…' : 'Creating…';
        try {
            const data = getFormData(form);
            const payload = { name: data.name, address: data.address || null, phone: data.phone || null, image_url: (imgHid ? imgHid.value : null) || data.image_url || null, is_active: data.is_active !== undefined };
            const url = isEdit ? API_ROUTES.WAREHOUSES.UPDATE(warehouseId) : API_ROUTES.WAREHOUSES.CREATE;
            const res = await apiRequest(url, { method: isEdit ? 'PUT' : 'POST', body: payload });
            if (!res.success) throw new Error(res.message);
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

async function reloadWarehouses(container) {
    const html = await Warehouses();
    container.innerHTML = html;
    await initWarehouses(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No warehouses found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load more</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:220px;">Warehouse</th>
    <th class="th" style="width:160px;">Stock Level</th>
    <th class="th" style="width:130px;">Phone</th>
    <th class="th" style="width:100px;">Status</th>
    <th class="th" style="width:140px;">Last Updated</th>
    <th class="th" style="width:140px;">Actions</th>
</tr>`;

export async function Warehouses() {
    _offset = 0;
    const data = await fetchWarehouses(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No warehouses yet. Add your first warehouse.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Warehouses',
        'entity-subtitle': 'Manage your warehouse locations and stock levels',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by name or address…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = 'Add Warehouse';
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

export function initWarehouses(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; _offset = 0;
        const data = await fetchWarehouses(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const war = await fetchWarehouse(btn.dataset.id);
            openStandardModal({ title: `${escapeHtml(war.name)} — Details`, bodyHtml: renderViewModal(war), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Warehouse', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadWarehouses(container));
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
            openStandardModal({ title: 'Edit Warehouse', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadWarehouses(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Delete
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
            await apiRequest(API_ROUTES.WAREHOUSES.DELETE(id), { method: 'DELETE' });
            reloadWarehouses(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Delete failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Add New Warehouse', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadWarehouses(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchWarehouses(DEFAULT_LIMIT, _offset, _query);
        const list = Array.isArray(data) ? data : [];
        if (!list.length) { btn.closest('#entity-load-more-container').style.display = 'none'; return; }
        _lastResults = [..._lastResults, ...list];
        container.querySelector('#entity-tbody').insertAdjacentHTML('beforeend', list.map(renderRow).join(''));
        if (list.length < DEFAULT_LIMIT) { btn.closest('#entity-load-more-container').style.display = 'none'; }
        else { btn.disabled = false; btn.textContent = 'Load more'; }
    }, { signal });

    // Refresh
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-refresh-btn') return;
        e.target.innerHTML = '⌛'; e.target.disabled = true;
        await reloadWarehouses(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
/**
 * Carts.js — Modernized Carts domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';
import { initSearchableSelect } from '../../FormHelpers.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:carts:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchCarts(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.CARTS.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch carts');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Carts] Fetch failed', err);
        return [];
    }
}

async function fetchCart(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('carts', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch cart details');
        return res.data;
    } catch (err) { throw err; }
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

function renderRow(c) {
    const statusBadge = `<span class="badge ${getStatusClass(c.status)}">${escapeHtml(c.status || 'active')}</span>`;
    const sessionId = (c.session_id || '').substring(0, 12);
    const created = c.created_at ? formatDate(c.created_at) : '—';

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(c.id))}</td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(sessionId)}...</div>
            <div class="text-slate-500" style="font-size:11px;">Intention Cluster</div>
        </td>
        <td class="td">${statusBadge}</td>
        <td class="td font-bold font-mono text-black" style="font-size:13px;">Rs ${formatCurrency(c.total_cents || 0)}</td>
        <td class="td">
            <div class="font-semibold text-black" style="font-size:12px;">${escapeHtml(c.user_name || 'Anonymous Guest')}</div>
            <div class="text-slate-500" style="font-size:11px;">${escapeHtml(c.user_email || 'No email provided')}</div>
        </td>
        <td class="td text-slate-500" style="font-size:11px;white-space:nowrap;">
            <div class="font-mono">${created}</div>
            <div class="text-[9px] uppercase tracking-tighter">Items: ${c.item_count || 0}</div>
        </td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${c.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${c.id}" title="Edit Cart">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${c.id}" title="Void"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="7" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(c) {
    const items = c.items || [];

    const itemsRows = items.map(it => `
        <tr class="tr">
            <td class="td">
                 <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(it.product_name)}</div>
            </td>
            <td class="td text-center font-mono" style="font-size:12px;">x${it.quantity}</td>
            <td class="td text-right font-mono text-slate-500" style="font-size:12px;">Rs ${formatCurrency(it.price_at_add_cents || 0)}</td>
        </tr>
    `).join('');

    return `
        <div class="flex flex-col" style="gap:20px;">
            <!-- Header Section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div>
                     <h3 class="font-bold text-black" style="font-size:22px;letter-spacing:-0.02em;">Cart Intention Protocol #${c.id}</h3>
                     <p class="text-sm text-slate-500">Session Signature: <span class="font-mono">${escapeHtml(c.session_id)}</span></p>
                </div>
                <div class="flex flex-col items-end" style="gap:6px;">
                    <span class="badge ${getStatusClass(c.status)} uppercase" style="font-size:11px;padding:4px 12px;">${c.status || 'ACTIVE'}</span>
                    <span class="text-slate-400" style="font-size:10px;">Created: ${formatDate(c.created_at)}</span>
                </div>
            </div>

            <div class="flex" style="gap:20px;">
                <!-- Left: Manifest -->
                <div style="flex:1.5;display:flex;flex-direction:column;gap:16px;">
                    <div>
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:11px;letter-spacing:0.05em;margin-bottom:8px;">Serialized Manifest (${items.length} items)</h4>
                         <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr class="tr">
                                        <th class="th">Product</th>
                                        <th class="th text-center">Qty</th>
                                        <th class="th text-right">Price @ Entry</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsRows || `<tr class="tr"><td colspan="3" class="td text-center text-slate-400" style="padding:24px;">No items serialized in this session</td></tr>`}
                                </tbody>
                            </table>
                            <div style="background:var(--slate-50);padding:16px;display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                <div class="flex justify-between" style="width:200px;font-size:14px;font-weight:900;color:var(--black);"><span class="uppercase">Accumulated Value</span><span class="font-mono text-lg">Rs ${formatCurrency(c.total_cents)}</span></div>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- Right: Attributes -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:16px;background:var(--slate-50);">
                         <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:12px;">Session Owner</div>
                         <div class="flex items-center" style="gap:12px;">
                             <div class="thumb-md rounded-full bg-white border flex items-center justify-center text-xl shadow-sm">👤</div>
                             <div>
                                <div class="font-bold text-black" style="font-size:14px;">${escapeHtml(c.user_name || 'Anonymous Guest')}</div>
                                <div class="text-xs text-slate-500">${escapeHtml(c.user_email || 'No mapped relay.')}</div>
                             </div>
                         </div>
                    </div>

                    <div class="google-card" style="padding:16px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Life-Cycle Meta</h4>
                        <div class="flex flex-col" style="gap:8px;">
                            <div class="flex justify-between" style="font-size:12px;"><span class="text-slate-500">Node ID</span><span class="font-mono italic">#${c.id}</span></div>
                            <div class="flex justify-between" style="font-size:12px;"><span class="text-slate-500">Converted</span><span>${c.converted_at ? formatDate(c.converted_at) : 'No conversion event'}</span></div>
                            <div class="flex justify-between" style="font-size:12px;"><span class="text-slate-500">Last Pulse</span><span>${formatDate(c.updated_at)}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${c.id}" style="padding:0 32px;">✏️ Modify Interest Protocol</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id = null) {
    const isEdit = id !== null;
    const c = isEdit ? await fetchCart(id) : {};

    const frag = getTemplate('tpl-cart-form', {
        id:              isEdit ? id : '',
        user_name:       escapeHtml(c.user_name || ''),
        user_email:      escapeHtml(c.user_email || ''),
        session_id:      c.session_id || `sess_${Math.random().toString(36).substr(2, 9)}`,
        total:           formatCurrency(c.total_cents || 0),
        item_count:      c.item_count || 0,
        created_at:      c.created_at ? formatDate(c.created_at) : 'N/A',
        updated_at:      c.updated_at ? formatDate(c.updated_at) : 'N/A',
        create_only:     isEdit ? 'hidden' : '',
        edit_only:       isEdit ? '' : 'hidden',
        delete_display:  isEdit ? '' : 'hidden',
        submit_text:     isEdit ? 'Save Changes' : 'Execute Cart Synthesis'
    });

    if (isEdit) {
        const select = frag.querySelector('#crt-status-select');
        if (select) select.value = c.status || 'active';
    }

    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const isEdit = id !== null;
    const form   = modalRoot.querySelector('#crt-form');
    const cancel = modalRoot.querySelector('#crt-cancel');
    const delBtn = modalRoot.querySelector('#crt-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (!isEdit) {
        const userSearchWrapper = modalRoot.querySelector('#crt-user-search-wrapper');
        if (userSearchWrapper) {
            initSearchableSelect(userSearchWrapper, {
                placeholder: 'Identify targeted User by name/email...',
                name: 'user_id',
                searchUrlBuilder: (q) => `${API_ROUTES.USERS.LIST}${buildQueryString({ search: q, limit: 12 })}`,
                itemRenderer: (u) => `<div class="p-2 hover:bg-slate-50 cursor-pointer flex justify-between items-center"><span class="font-bold text-black">${escapeHtml(u.name || u.username)}</span><span class="text-[10px] text-slate-400 font-mono">${escapeHtml(u.email)}</span></div>`,
            });
        }
    }

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm VOID?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Delete Protocol'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Voiding…';
            try {
                await apiRequest(API_ROUTES.CARTS.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) {
                const msg = err.message.includes('foreign key') ? 'Protocol locked: Converged to Order. Abandon instead.' : err.message;
                showFormError(form, msg);
                delBtn.disabled = false; delBtn.innerHTML = '🗑️ Delete Protocol';
                delete delBtn.dataset.confirmed;
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig   = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Saving…' : 'Creating Cart…';
        try {
            const data = getFormData(form);
            const payload = isEdit ? { status: data.status } : { user_id: parseInt(data.user_id), status: 'active' };
            const url = isEdit ? API_ROUTES.CARTS.UPDATE(id) : API_ROUTES.CARTS.CREATE;
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

async function reloadCarts(container) {
    const html = await Carts();
    container.innerHTML = html;
    await initCarts(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No shopping carts found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:160px;">Session Signature</th>
    <th class="th" style="width:120px;">Protocol State</th>
    <th class="th" style="width:130px;">Total Intention</th>
    <th class="th" style="min-width:180px;">Owner Identity</th>
    <th class="th" style="width:160px;">Temporal Sync</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function Carts() {
    _offset = 0;
    const data = await fetchCarts(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No shopping carts found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Shopping Carts',
        'entity-subtitle': 'Monitor active and abandoned customer shopping carts',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by user name or email…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ New Cart';
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

export function initCarts(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:carts:query', _query); _offset = 0;
        const data = await fetchCarts(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const c = await fetchCart(btn.dataset.id);
            openStandardModal({ title: 'Shopping Cart Details', bodyHtml: renderViewModal(c), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Shopping Cart', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadCarts(container));
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
            openStandardModal({ title: 'Modify Interest Protocol', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadCarts(container));
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
            await apiRequest(API_ROUTES.CARTS.DELETE(id), { method: 'DELETE' });
            reloadCarts(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Void failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Initialize Synthetic Interest', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadCarts(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchCarts(DEFAULT_LIMIT, _offset, _query);
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
        await reloadCarts(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
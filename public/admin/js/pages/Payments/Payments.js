/**
 * Payments.js — Modernized Payments domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:payments:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchPayments(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.PAYMENTS.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch payments');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Payments] Fetch failed', err);
        return [];
    }
}

async function fetchPayment(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('payments', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch payment details');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchDependencies() {
    try {
        const res = await apiRequest(API_ROUTES.ORDERS.LIST + '?limit=100');
        return { orders: res.data?.items || res.data || [] };
    } catch (err) { return { orders: [] }; }
}

// ─── Utils ───────────────────────────────────────────────────────────────────

function getStatusClass(status) {
    const s = (status || 'pending').toLowerCase();
    switch (s) {
        case 'captured': case 'success': case 'paid': return 'badge-active';
        case 'pending':  return 'badge-warning';
        case 'failed':   return 'badge-inactive';
        case 'refunded': return 'badge-info';
        case 'voided':   return 'badge-secondary';
        default:        return 'badge-secondary';
    }
}

function formatCurrency(cents) {
    return (cents / 100).toFixed(2);
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(p) {
    const statusBadge = `<span class="badge ${getStatusClass(p.status)}">${escapeHtml(p.status || 'pending')}</span>`;
    const created = p.created_at ? formatDate(p.created_at) : '—';
    const amount = formatCurrency(p.amount_cents || 0);

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(p.id))}</td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">Order #${escapeHtml(p.order_number || (p.order_id ? p.order_id.toString() : 'ORPHANED'))}</div>
            <div class="text-slate-500 font-mono" style="font-size:10px;">Internal ID: ${p.order_id}</div>
        </td>
        <td class="td">
            <div class="font-semibold text-black" style="font-size:12px;">${escapeHtml(p.gateway || 'manual')}</div>
            <div class="text-slate-400 font-mono truncate max-w-[120px]" style="font-size:10px;">Ref: ${escapeHtml(p.transaction_id || 'N/A')}</div>
        </td>
        <td class="td">${statusBadge}</td>
        <td class="td font-bold font-mono text-black" style="font-size:13px;">Rs ${amount}</td>
        <td class="td text-slate-500 font-mono" style="font-size:11px;">${created}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${p.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${p.id}" title="Edit Payment">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${p.id}" title="Void"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="7" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(p) {
    const payload = p.payload || {};
    const amount = formatCurrency(p.amount_cents || 0);

    return `
        <div class="flex flex-col" style="gap:20px;">
            <!-- Header Section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div>
                     <h3 class="font-bold text-black" style="font-size:22px;letter-spacing:-0.02em;">Financial Settlement Analysis #${p.id}</h3>
                     <p class="text-sm text-slate-500">Gateway: <span class="font-bold text-black uppercase">${escapeHtml(p.gateway)}</span> • Reference: <span class="font-mono text-xs">${escapeHtml(p.transaction_id || 'N/A')}</span></p>
                </div>
                <div class="flex flex-col items-end" style="gap:6px;">
                    <span class="badge ${getStatusClass(p.status)} uppercase" style="font-size:11px;padding:4px 12px;">${p.status || 'PENDING'}</span>
                    <span class="text-slate-400 font-mono" style="font-size:10px;">${formatDate(p.created_at)}</span>
                </div>
            </div>

            <div class="flex" style="gap:20px;">
                <!-- Left: Settlement Identity -->
                <div style="flex:1.5;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:20px;background:var(--slate-50);">
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:16px;">Verified Displacement</h4>
                         <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            <div class="google-card" style="padding:16px;background:white;">
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Displaced Value</div>
                                <div class="font-black text-black" style="font-size:24px;font-family:monospace;">Rs ${amount}</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest">${p.currency || 'LKR'} SETTLEMENT</div>
                            </div>
                            <div class="google-card" style="padding:16px;background:white;">
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Parent Order Mapping</div>
                                <div class="font-bold text-black" style="font-size:18px;">Order #${escapeHtml(p.order_number)}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-1">INTERNAL-REF-${p.order_id}</div>
                            </div>
                         </div>
                    </div>

                    <div class="google-card" style="padding:24px;background:var(--slate-900);color:white;box-shadow:0 10px 30px -10px rgba(0,0,0,0.4);">
                         <h4 class="text-slate-500 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid #334155;padding-bottom:8px;">Protocol Payload Snapshot</h4>
                         <div style="background:#0f172a;padding:16px;border-radius:12px;border:1px solid #1e293b;">
                            <pre style="font-family:monospace;font-size:10px;color:#818cf8;overflow-x:auto;max-height:220px;">${escapeHtml(JSON.stringify(payload, null, 2))}</pre>
                         </div>
                    </div>
                </div>

                <!-- Right: Meta Trace -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:16px;">
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Transaction Actor Profile</h4>
                         <div class="flex items-center" style="gap:12px;">
                             <div class="thumb-md rounded-full bg-slate-50 border flex items-center justify-center text-xl shadow-inner">💳</div>
                             <div>
                                <div class="font-bold text-black" style="font-size:14px;">${escapeHtml(p.user_name || 'Guest Payer')}</div>
                                <div class="text-xs text-slate-500 truncate max-w-[150px] underline">${escapeHtml(p.user_email || 'no-email@payment.trace')}</div>
                             </div>
                         </div>
                    </div>

                    <div class="google-card" style="padding:16px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:10px;">Post-Event Audit</h4>
                        <p class="text-[11px] text-slate-600 leading-relaxed italic">Verified by gateway protocol. Any settlement disputes should be cross-referenced with the provider dashboard before manual modification.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${p.id}" style="padding:0 32px;">✏️ Modify Settlement</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id = null) {
    const isEdit = id !== null;
    const [deps, p] = await Promise.all([fetchDependencies(), isEdit ? fetchPayment(id) : Promise.resolve({})]);

    const frag = getTemplate('tpl-payment-form', {
        id:               isEdit ? id : '',
        order_key:        p.order_id || '',
        order_number:     escapeHtml(p.order_number || ''),
        amount_cents:     p.amount_cents || '',
        currency:         p.currency || 'LKR',
        gateway:          escapeHtml(p.gateway || 'manual'),
        gateway_order_id: escapeHtml(p.gateway_order_id || ''),
        transaction_id:   escapeHtml(p.transaction_id || ''),
        payload_json:     p.payload ? JSON.stringify(p.payload, null, 2) : '',
        create_only:      isEdit ? 'hidden' : '',
        edit_only:        isEdit ? '' : 'hidden',
        delete_display:   isEdit ? '' : 'hidden',
        submit_text:      isEdit ? 'Update Protocol' : 'Authorize Settlement'
    });

    const oSel = frag.querySelector('#pay-order-select');
    if (oSel && !isEdit) {
        oSel.innerHTML = '<option value="">Select Target Order Cluster...</option>' + deps.orders.map(o => `<option value="${o.id}">#${o.order_number} (${formatDate(o.created_at)})</option>`).join('');
    }

    if (isEdit) {
        const gatewaySel = frag.querySelector('#pay-gateway-select');
        const statusSel = frag.querySelector('#pay-status-select');
        if (gatewaySel) gatewaySel.value = p.gateway || 'manual';
        if (statusSel) statusSel.value = p.status || 'pending';

        const footer = frag.querySelector('.flex.justify-end.gap-3.pt-6');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button'; del.className = 'btn btn-outline text-danger'; del.style.marginRight = 'auto';
            del.id = 'pay-delete-btn'; del.dataset.id = id; del.innerHTML = '🗑️ Purge Protocol';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const isEdit = id !== null;
    const form   = modalRoot.querySelector('#pay-form');
    const cancel = modalRoot.querySelector('#pay-cancel');
    const delBtn = modalRoot.querySelector('#pay-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Purge?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Purge Protocol'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Purging…';
            try {
                await apiRequest(API_ROUTES.PAYMENTS.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Purge Protocol'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Syncing…' : 'Authorizing…';
        try {
            const data = getFormData(form);
            let payloadObj = {};
            try { if (data.payload && data.payload.trim()) payloadObj = JSON.parse(data.payload); } catch (je) { throw new Error('Malformed JSON Payload detected.'); }
            const payload = { order_id: parseInt(data.order_id), amount_cents: parseInt(data.amount_cents), currency: data.currency, gateway: data.gateway, status: data.status, gateway_order_id: data.gateway_order_id || null, transaction_id: data.transaction_id || null, payload: payloadObj };
            const url = isEdit ? API_ROUTES.PAYMENTS.UPDATE(id) : API_ROUTES.PAYMENTS.CREATE;
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
    el.textContent = `Protocol Error: ${msg}`; el.style.display = 'block';
}

// ─── Reload / Redraw ──────────────────────────────────────────────────────────

async function reloadPayments(container) {
    const html = await Payments();
    container.innerHTML = html;
    await initPayments(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No payment records found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:180px;">Trace Origin / Order</th>
    <th class="th" style="min-width:160px;">Gateway Protocol / Ref</th>
    <th class="th" style="width:120px;">Disposition</th>
    <th class="th" style="width:140px;">Settled Value</th>
    <th class="th" style="width:140px;">Established</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function Payments() {
    _offset = 0;
    const data = await fetchPayments(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No payment records found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Payments',
        'entity-subtitle': 'Track and manage customer payment records',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by Order #, Gateway, or Trace Reference…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ Authorize Settlement';
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

export function initPayments(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:payments:query', _query); _offset = 0;
        const data = await fetchPayments(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const p = await fetchPayment(btn.dataset.id);
            openStandardModal({ title: 'Payment Details', bodyHtml: renderViewModal(p), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Payment', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadPayments(container));
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
            openStandardModal({ title: 'Edit Payment', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadPayments(container));
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
            await apiRequest(API_ROUTES.PAYMENTS.DELETE(id), { method: 'DELETE' });
            reloadPayments(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Void failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Authorize Settlement Protocol', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadPayments(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchPayments(DEFAULT_LIMIT, _offset, _query);
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
        await reloadPayments(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
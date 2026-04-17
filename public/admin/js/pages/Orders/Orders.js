/**
 * Orders.js — Modernized Orders domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:orders:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchOrders(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.ORDERS.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch orders');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Orders] Fetch failed', err);
        return [];
    }
}

async function fetchOrder(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('orders', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch order details');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchDependencies() {
    try {
        const [uRes, cRes] = await Promise.all([
            apiRequest(API_ROUTES.USERS.LIST + '?limit=200'),
            apiRequest(API_ROUTES.CARTS.LIST + '?limit=100')
        ]);
        return {
            users: uRes.data?.items || uRes.data || [],
            carts: cRes.data?.items || cRes.data || []
        };
    } catch (err) {
        console.error('[Orders] Dependency fetch failed', err);
        return { users: [], carts: [] };
    }
}

async function fetchUserAddresses(userId) {
    if (!userId) return [];
    try {
        const url = API_ROUTES.USER_ADDRESSES.BY_USER(userId);
        const res = await apiRequest(url);
        return res.success ? (res.data || []) : [];
    } catch (err) {
        console.error('[Orders] Address fetch failed', err);
        return [];
    }
}

// ─── Utils ───────────────────────────────────────────────────────────────────

function getStatusClass(status) {
    const s = (status || 'pending').toLowerCase();
    switch (s) {
        case 'delivered': case 'completed': case 'paid': return 'badge-active';
        case 'processing': case 'shipped': case 'shipped': return 'badge-info';
        case 'cancelled': case 'returned':                return 'badge-inactive';
        case 'pending':                                   return 'badge-warning';
        default:                                         return 'badge-info';
    }
}

function formatCurrency(cents) {
    return (cents / 100).toFixed(2);
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(o) {
    const statusBadge = `<span class="badge ${getStatusClass(o.status)}">${escapeHtml(o.status || 'pending')}</span>`;
    const created = o.created_at ? formatDate(o.created_at) : '—';
    const orderNum = o.order_number || `#${o.id}`;
    
    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(o.id))}</td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(orderNum)}</div>
            <div class="text-slate-500" style="font-size:11px;">${o.item_count || 0} items</div>
        </td>
        <td class="td">${statusBadge}</td>
        <td class="td font-bold font-mono text-black" style="font-size:13px;">Rs ${formatCurrency(o.total_cents || 0)}</td>
        <td class="td">
            <div class="font-semibold text-black" style="font-size:12px;">${escapeHtml(o.user_name || 'Guest User')}</div>
            <div class="text-slate-500" style="font-size:11px;">${escapeHtml(o.user_email || '')}</div>
        </td>
        <td class="td text-slate-500" style="font-size:11px;white-space:nowrap;">${created}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${o.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${o.id}" title="Edit Order">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${o.id}" title="Void"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="7" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(o) {
    const items = o.items || [];
    const shipping = o.shipping_address || {};

    const itemsRows = items.map(it => `
        <tr class="tr">
            <td class="td">
                 <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(it.product_name)}</div>
            </td>
            <td class="td text-center font-mono" style="font-size:12px;">x${it.quantity}</td>
            <td class="td text-right font-mono text-slate-500" style="font-size:12px;">Rs ${formatCurrency(it.price_cents)}</td>
            <td class="td text-right font-mono font-bold text-black" style="font-size:12px;">Rs ${formatCurrency(it.price_cents * it.quantity)}</td>
        </tr>
    `).join('');

    return `
        <div class="flex flex-col" style="gap:20px;">
            <!-- Header Section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div>
                     <h3 class="font-bold text-black" style="font-size:22px;letter-spacing:-0.02em;">Order ${escapeHtml(o.order_number || `#${o.id}`)}</h3>
                     <p class="text-sm text-slate-500">Recorded on ${formatDate(o.created_at)}</p>
                </div>
                <div class="flex flex-col items-end" style="gap:6px;">
                    <span class="badge ${getStatusClass(o.status)} uppercase" style="font-size:11px;padding:4px 12px;">${o.status || 'PENDING'}</span>
                    ${o.paid_at ? `<span class="text-success font-bold" style="font-size:10px;text-transform:uppercase;">✓ Paid ${formatDate(o.paid_at)}</span>` : '<span class="text-warning font-black" style="font-size:10px;text-transform:uppercase;letter-spacing:0.05em;">⚠ Awaiting Payment</span>'}
                </div>
            </div>

            <div class="flex" style="gap:20px;">
                <!-- Left Column: manifestation & summary -->
                <div style="flex:2;display:flex;flex-direction:column;gap:16px;">
                    <!-- Customer Summary -->
                    <div class="google-card flex items-center" style="padding:16px;gap:14px;background:var(--slate-50);">
                         <div class="thumb-md rounded-full bg-white border flex items-center justify-center text-xl shadow-sm">👤</div>
                         <div>
                            <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;">Customer Details</div>
                            <div class="font-bold text-black" style="font-size:14px;">${escapeHtml(o.user_name || 'Guest User')}</div>
                            <div class="text-xs text-slate-500">${escapeHtml(o.user_email || 'no-email@provided')}</div>
                         </div>
                    </div>

                    <!-- Manifest Table -->
                    <div>
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:11px;letter-spacing:0.05em;margin-bottom:8px;">Manifest (${items.length} items)</h4>
                         <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr class="tr">
                                        <th class="th">Product</th>
                                        <th class="th text-center">Qty</th>
                                        <th class="th text-right">Unit</th>
                                        <th class="th text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsRows || `<tr class="tr"><td colspan="4" class="td text-center text-slate-400" style="padding:24px;">No items found</td></tr>`}
                                </tbody>
                            </table>
                            <div style="background:var(--slate-50);padding:16px;display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                <div class="flex justify-between" style="width:200px;font-size:12px;"><span class="text-slate-500">Subtotal</span><span class="font-mono">Rs ${formatCurrency(o.total_cents)}</span></div>
                                <div class="flex justify-between" style="width:200px;font-size:14px;font-weight:900;border-top:1px solid var(--slate-200);margin-top:4px;padding-top:4px;color:var(--black);"><span class="uppercase">Order Total</span><span class="font-mono text-lg">Rs ${formatCurrency(o.total_cents)}</span></div>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- Right Column: Logistics -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <!-- Logistics -->
                    <div class="google-card" style="padding:16px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:11px;letter-spacing:0.05em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Logistics</h4>
                        ${shipping.address_line1 ? `
                            <div class="flex flex-col" style="gap:8px;">
                                <div>
                                    <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.05em;">Recipient</div>
                                    <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(shipping.recipient_name)}</div>
                                </div>
                                <div style="margin-top:4px;">
                                    <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.05em;">Destination</div>
                                    <div class="text-black" style="font-size:12px;line-height:1.4;">
                                        ${escapeHtml(shipping.address_line1)}<br>
                                        ${shipping.address_line2 ? `${escapeHtml(shipping.address_line2)}<br>` : ''}
                                        <span class="font-semibold">${escapeHtml(shipping.city)}, ${escapeHtml(shipping.postal_code)}</span><br>
                                        <span class="uppercase font-bold" style="font-size:10px;">${escapeHtml(shipping.country)}</span>
                                    </div>
                                </div>
                            </div>
                        ` : '<div class="text-slate-400 italic" style="font-size:11px;padding:10px 0;">No logistics data (Self-Pickup or Guest)</div>'}
                    </div>

                    <!-- Internal Notes -->
                    <div style="padding:14px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;">
                         <h4 class="text-amber-500 font-bold uppercase" style="font-size:10px;letter-spacing:0.05em;margin-bottom:6px;">Internal Narrative</h4>
                         <p class="text-amber-800 italic" style="font-size:11px;line-height:1.5;">${escapeHtml(o.notes || 'No internal annotations for this session.')}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${o.id}" style="padding:0 32px;">✏️ Modify Order</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id = null) {
    const isEdit = id !== null;
    const [deps, o] = await Promise.all([
        fetchDependencies(),
        isEdit ? fetchOrder(id) : Promise.resolve({})
    ]);

    let addresses = [];
    if (o.user_id) {
        addresses = await fetchUserAddresses(o.user_id);
    }

    const frag = getTemplate('tpl-order-form', {
        id:             isEdit ? id : '',
        order_number:   escapeHtml(o.order_number || ''),
        total_cents:    o.total_cents || '',
        notes:          escapeHtml(o.notes || ''),
        created_at:     o.created_at ? formatDate(o.created_at) : 'N/A',
        paid_at:        o.paid_at ? formatDate(o.paid_at) : 'Not paid',
        create_only:    isEdit ? 'hidden' : '',
        edit_only:      isEdit ? '' : 'hidden',
        cart_required:  isEdit ? '' : 'required',
        delete_display: isEdit ? '' : 'hidden',
        submit_text:    isEdit ? 'Save Protocol' : 'Execute Order'
    });

    // Populate Users
    const uSel = frag.querySelector('#ord-user-select');
    if (uSel) {
        uSel.innerHTML = '<option value="">Select Customer (Required)</option>' + 
            deps.users.map(u => `<option value="${u.id}" ${o.user_id == u.id ? 'selected' : ''}>${escapeHtml(u.name || u.username)} (${escapeHtml(u.email)})</option>`).join('');
    }

    // Populate Carts (Create Only)
    if (!isEdit) {
        const cSel = frag.querySelector('#ord-cart-select');
        if (cSel) {
            cSel.innerHTML = '<option value="">Synthesize from Active Cart...</option>' + 
                deps.carts.map(c => `<option value="${c.id}">Cart #${c.id} • User: ${escapeHtml(c.user_name || c.user_id)}</option>`).join('');
        }
    }

    if (isEdit) {
        const statusSel = frag.querySelector('#ord-status-select');
        if (statusSel) statusSel.value = o.status || 'pending';
    }

    // Populate Addresses
    const sSel = frag.querySelector('#ord-shipping-select');
    const bSel = frag.querySelector('#ord-billing-select');
    const addrOptions = addresses.map(a => `<option value="${a.id}">${escapeHtml(a.recipient_name || 'Address')} - ${escapeHtml(a.address_line1)}, ${escapeHtml(a.city)} (${escapeHtml(a.address_type)})</option>`).join('');
    
    if (sSel) {
        sSel.innerHTML += addrOptions;
        if (isEdit && o.shipping_address_id) sSel.value = o.shipping_address_id;
    }
    if (bSel) {
        bSel.innerHTML += addrOptions;
        if (isEdit && o.billing_address_id) bSel.value = o.billing_address_id;
    }

    if (isEdit) {
        const footer = frag.querySelector('.flex.justify-end.gap-3.pt-6');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-outline text-danger';
            del.style.marginRight = 'auto';
            del.id = 'ord-delete-btn';
            del.dataset.id = id;
            del.innerHTML = '🗑️ Void Order';
            footer.prepend(del);
        }
    }

    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const isEdit = id !== null;
    const form   = modalRoot.querySelector('#ord-form');
    const cancel = modalRoot.querySelector('#ord-cancel');
    const delBtn = modalRoot.querySelector('#ord-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    const uSel = form.querySelector('#ord-user-select');
    const sSel = form.querySelector('#ord-shipping-select');
    const bSel = form.querySelector('#ord-billing-select');

    if (uSel) {
        uSel.addEventListener('change', async () => {
             const uid = uSel.value;
             if (!uid) {
                 if (sSel) sSel.innerHTML = '<option value="">Select shipping address...</option>';
                 if (bSel) bSel.innerHTML = '<option value="">Select billing address...</option>';
                 return;
             }
             if (sSel) { sSel.disabled = true; sSel.innerHTML = '<option>Loading addresses...</option>'; }
             if (bSel) { bSel.disabled = true; bSel.innerHTML = '<option>Loading addresses...</option>'; }
             
             const addrs = await fetchUserAddresses(uid);
             const options = addrs.map(a => `<option value="${a.id}">${escapeHtml(a.recipient_name || 'Address')} - ${escapeHtml(a.address_line1)}, ${escapeHtml(a.city)} (${escapeHtml(a.address_type)})</option>`).join('');
             
             if (sSel) {
                 sSel.disabled = false;
                 sSel.innerHTML = '<option value="">Select shipping address...</option>' + options;
                 // Auto-select first shipping address if available
                 const ship = addrs.find(a => a.address_type === 'shipping' || a.address_type === 'both');
                 if (ship) sSel.value = ship.id;
             }
             if (bSel) {
                 bSel.disabled = false;
                 bSel.innerHTML = '<option value="">Select billing address...</option>' + options;
                 // Auto-select first billing address if available
                 const bill = addrs.find(a => a.address_type === 'billing' || a.address_type === 'both');
                 if (bill) bSel.value = bill.id;
             }
        });
    }

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1';
                delBtn.innerHTML = '⚠️ Confirm Void?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Void Order'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Voiding…';
            try {
                await apiRequest(API_ROUTES.ORDERS.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Void Order'; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig   = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Saving…' : 'Creating Order…';
        try {
            const data    = getFormData(form);
            const payload = {
                user_id:             parseInt(data.user_id),
                status:              data.status,
                order_number:        data.order_number,
                total_cents:         parseInt(data.total_cents),
                notes:               data.notes || null,
                shipping_address_id: data.shipping_address_id ? parseInt(data.shipping_address_id) : null,
                billing_address_id:  data.billing_address_id ? parseInt(data.billing_address_id) : null,
                ...(isEdit ? {} : { cart_id: parseInt(data.cart_id) })
            };
            const url = isEdit ? API_ROUTES.ORDERS.UPDATE(id) : API_ROUTES.ORDERS.CREATE;
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
    el.textContent = `Error: ${msg}`; el.style.display = 'block';
}

// ─── Reload / Redraw ──────────────────────────────────────────────────────────

async function reloadOrders(container) {
    const html = await Orders();
    container.innerHTML = html;
    await initOrders(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No order records found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:180px;">Reference/Items</th>
    <th class="th" style="width:100px;">Status</th>
    <th class="th" style="width:120px;">Total</th>
    <th class="th" style="min-width:180px;">Customer Profile</th>
    <th class="th" style="width:150px;">Recorded At</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function Orders() {
    _offset = 0;
    const data = await fetchOrders(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No order records found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Transaction Ledger',
        'entity-subtitle': 'Managing purchase history and synthesized orders',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by Order # or User…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ New Order';
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

export function initOrders(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:orders:query', _query); _offset = 0;
        const data = await fetchOrders(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => {
        if (e.target.id === 'entity-search') performSearch(e.target.value.trim());
    }, { signal });

    // Modal Action Redirects (Edit from inside View Modal)
    // We attach this to container using signal so it cleans up.
    container.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.modal-overlay .js-edit');
        if (!editBtn) return;
        
        const id = editBtn.dataset.id;
        closeModal();
        setTimeout(async () => {
            const f = await renderFormModal(id);
            openStandardModal({ title: 'Edit Order', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), id, () => reloadOrders(container));
        }, 200);
    }, { signal });

    // View Detail
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const o = await fetchOrder(btn.dataset.id);
            openStandardModal({ title: `Order ${escapeHtml(o.order_number || '#' + o.id)} Detail`, bodyHtml: renderViewModal(o), size: 'xl' });
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Edit Modal (Direct or from modal)
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-edit');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const f = await renderFormModal(btn.dataset.id);
            openStandardModal({ title: 'Edit Order', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadOrders(container));
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
            await apiRequest(API_ROUTES.ORDERS.DELETE(id), { method: 'DELETE' });
            reloadOrders(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Void failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Draft Transaction Protocol', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadOrders(container));
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchOrders(DEFAULT_LIMIT, _offset, _query);
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
        await reloadOrders(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}

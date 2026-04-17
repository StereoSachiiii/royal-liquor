/**
 * UserAddresses.js — Modernized User Addresses domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:uadr:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchAddresses(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.USER_ADDRESSES.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch addresses');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[UserAddresses] Fetch failed', err);
        return [];
    }
}

async function fetchAddress(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('user_addresses', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch address details');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchUsers() {
    try { const res = await apiRequest(API_ROUTES.USERS.LIST + '?limit=200'); return res.success ? (res.data || []) : []; }
    catch (err) { return []; }
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(a) {
    const defaultBadge = a.is_default 
        ? `<span class="badge badge-active" style="font-size:10px;">Primary</span>` 
        : `<span class="badge badge-inactive" style="font-size:10px;">Secondary</span>`;

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(a.id))}</td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(a.user_name || 'N/A')}</div>
            <div class="text-slate-500 font-mono" style="font-size:10px;">${escapeHtml(a.user_email || 'n/a')}</div>
        </td>
        <td class="td">
            <span class="badge badge-secondary uppercase" style="font-size:9px;padding:2px 8px;">${escapeHtml(a.address_type || 'both')}</span>
        </td>
        <td class="td">
            <div class="font-bold text-black" style="font-size:12px;">${escapeHtml(a.city || 'N/A')}</div>
            <div class="text-slate-400" style="font-size:10px;">${escapeHtml(a.country || '')}</div>
        </td>
        <td class="td">${defaultBadge}</td>
        <td class="td text-slate-500 font-mono" style="font-size:11px;">${formatDate(a.created_at)}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${a.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${a.id}" title="Edit Address">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${a.id}" title="Purge"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="7" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(a) {
    return `
        <div class="flex flex-col" style="gap:24px;">
            <!-- Header section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div class="flex items-center" style="gap:16px;">
                    <div class="thumb-lg rounded-2xl bg-slate-50 border flex items-center justify-center text-2xl shadow-inner">📍</div>
                    <div>
                         <h3 class="font-bold text-black" style="font-size:22px;letter-spacing:-0.02em;">Geographic Identity Forensic #${a.id}</h3>
                         <p class="text-sm text-slate-500">Node Type: <span class="font-bold text-black uppercase">${escapeHtml(a.address_type)}</span></p>
                    </div>
                </div>
                <div class="flex flex-col items-end" style="gap:6px;">
                    ${a.is_default ? '<span class="badge badge-active uppercase font-black tracking-widest" style="font-size:10px;padding:4px 12px;">✓ PRIMARY NODE</span>' : '<span class="badge badge-inactive uppercase font-black tracking-widest" style="font-size:10px;padding:4px 12px;">SECONDARY NODE</span>'}
                    <span class="text-slate-400 font-mono" style="font-size:10px;">ESTABLISHED ${formatDate(a.created_at)}</span>
                </div>
            </div>

            <div class="flex" style="gap:24px;">
                <!-- Left: Location Details -->
                <div style="flex:1.5;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:24px;background:var(--slate-50);border:1px solid var(--slate-200);">
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:16px;">Verified Recipient Architecture</h4>
                         <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            <div>
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Legal Recipient</div>
                                <div class="font-bold text-black" style="font-size:18px;letter-spacing:-0.01em;">${escapeHtml(a.recipient_name || a.user_name)}</div>
                                <div class="text-sm text-slate-500 font-mono mt-1">${escapeHtml(a.phone || 'NO-CONTACT-TRACE')}</div>
                            </div>
                            <div>
                                <div class="text-slate-400 font-bold uppercase" style="font-size:9px;">Account Association</div>
                                <div class="font-bold text-black" style="font-size:16px;">${escapeHtml(a.user_name)}</div>
                                <div class="text-xs text-slate-500 font-mono mt-1">${escapeHtml(a.user_email)}</div>
                            </div>
                         </div>
                    </div>

                    <div class="google-card" style="padding:24px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:6px;">Structural Coordinates</h4>
                        <div class="text-slate-800 font-medium" style="font-size:16px;line-height:1.7;">
                             <div class="font-bold" style="font-size:18px;">${escapeHtml(a.address_line1)}</div>
                             ${a.address_line2 ? `<div class="text-slate-500">${escapeHtml(a.address_line2)}</div>` : ''}
                             <div style="margin-top:4px;">${escapeHtml(a.city)}, ${escapeHtml(a.state || 'N/A')}</div>
                             <div class="flex items-center" style="gap:10px;margin-top:2px;">
                                <span class="bg-slate-100 px-2 py-0.5 rounded text-xs font-mono font-bold text-slate-600">${escapeHtml(a.postal_code)}</span>
                                <span class="text-slate-400 uppercase tracking-widest font-black" style="font-size:12px;">${escapeHtml(a.country)}</span>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Logic Trace -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:20px;background:var(--slate-900);color:white;box-shadow:0 10px 30px -10px rgba(0,0,0,0.5);">
                         <h4 class="text-slate-500 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:12px;">Usage Forensic Trace</h4>
                         <div style="display:flex;flex-direction:column;gap:12px;">
                            <div class="flex justify-between items-center" style="border-bottom:1px solid #334155;padding-bottom:8px;">
                                <span class="text-slate-400 text-xs">Shipping Manifests</span>
                                <span class="font-mono text-xl font-black text-indigo-400">${a.used_as_shipping || 0}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400 text-xs">Billing Declarations</span>
                                <span class="font-mono text-xl font-black text-emerald-400">${a.used_as_billing || 0}</span>
                            </div>
                         </div>
                    </div>

                    <div style="background:var(--slate-50);padding:16px;border-radius:12px;border:1px solid var(--slate-200);">
                        <h4 class="text-indigo-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:6px;">Security Notice</h4>
                        <p class="text-[11px] text-slate-600 leading-relaxed italic">
                            Primary nodes are automatically prioritized in checkout protocol. Manual modification will alter future fulfilling routing.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${a.id}" style="padding:0 32px;">✏️ Modify Identity</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id = null) {
    const isEdit = id !== null;
    let a = {};
    if (isEdit) {
        a = await fetchAddress(id);
    }
    const users = isEdit ? [] : await fetchUsers();

    const frag = getTemplate('tpl-user-address-form', {
        recipient_name:   escapeHtml(a.recipient_name || ''),
        phone:            escapeHtml(a.phone || ''),
        address_line1:    escapeHtml(a.address_line1 || ''),
        address_line2:    escapeHtml(a.address_line2 || ''),
        city:             escapeHtml(a.city || ''),
        state:            escapeHtml(a.state || ''),
        postal_code:      escapeHtml(a.postal_code || ''),
        country:          escapeHtml(a.country || 'Sri Lanka'),
        default_checked:  a.is_default ? 'checked' : '',
        delete_display:   isEdit ? 'block' : 'none',
        submit_text:      isEdit ? 'Save Changes' : 'Add Address'
    });

    const uSel = frag.querySelector('#uadr-user-select');
    if (uSel) {
        if (isEdit) {
            // Show the known user as read-only; no need to load full list
            uSel.innerHTML = `<option value="${a.user_id}" selected>${escapeHtml(a.user_name || '')} (${escapeHtml(a.user_email || '')})</option>`;
            uSel.disabled = true;
        } else {
            uSel.innerHTML = '<option value="" disabled selected>Identify Account Target...</option>' +
                users.map(u => `<option value="${u.id}">${escapeHtml(u.name)} (${u.email})</option>`).join('');
        }
    }

    const tSel = frag.querySelector('#uadr-type-select');
    if (tSel && isEdit) tSel.value = a.address_type || 'both';

    if (isEdit) {
        const footer = frag.querySelector('.flex.justify-end.gap-3.pt-6');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button'; del.className = 'btn btn-outline text-danger'; del.style.marginRight = 'auto';
            del.id = 'uadr-delete-btn'; del.dataset.id = id; del.innerHTML = '🗑️ Purge Identity';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const isEdit = id !== null;
    const form   = modalRoot.querySelector('#uadr-form');
    const cancel = modalRoot.querySelector('#uadr-cancel');
    const delBtn = modalRoot.querySelector('#uadr-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Purge?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Purge Identity'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Purging…';
            try {
                await apiRequest(API_ROUTES.USER_ADDRESSES.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Purge Identity'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Syncing…' : 'Executing…';
        try {
            const data = getFormData(form);
            const payload = { user_id: parseInt(data.user_id), address_type: data.address_type, recipient_name: data.recipient_name || null, phone: data.phone || null, address_line1: data.address_line1, address_line2: data.address_line2 || null, city: data.city, state: data.state || null, postal_code: data.postal_code, country: data.country, is_default: data.is_default !== undefined };
            const url = isEdit ? API_ROUTES.USER_ADDRESSES.UPDATE(id) : API_ROUTES.USER_ADDRESSES.CREATE;
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

async function reloadAddresses(container) {
    const html = await UserAddresses();
    container.innerHTML = html;
    await initUserAddresses(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No addresses found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:180px;">Account Target / Identity</th>
    <th class="th" style="width:100px;text-align:center;">Node Type</th>
    <th class="th" style="min-width:160px;">Coordinates</th>
    <th class="th" style="width:100px;">Priority</th>
    <th class="th" style="width:140px;">Established</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function UserAddresses() {
    _offset = 0;
    const data = await fetchAddresses(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No addresses found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Delivery Addresses',
        'entity-subtitle': 'Manage shipping and billing addresses for your customers',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by name, city, or email…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ New Address';
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

export function initUserAddresses(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:uadr:query', _query); _offset = 0;
        const data = await fetchAddresses(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const addr = await fetchAddress(btn.dataset.id);
            openStandardModal({ title: 'Address Details', bodyHtml: renderViewModal(addr), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit Address', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadAddresses(container));
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
            openStandardModal({ title: 'Edit Address', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadAddresses(container));
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
            await apiRequest(API_ROUTES.USER_ADDRESSES.DELETE(id), { method: 'DELETE' });
            reloadAddresses(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Void failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Add New Address', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadAddresses(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchAddresses(DEFAULT_LIMIT, _offset, _query);
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
        await reloadAddresses(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
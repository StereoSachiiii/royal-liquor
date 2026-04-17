/**
 * Users.js — Modernized Users domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';
import { initImageUpload } from '../../FormHelpers.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:users:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchUsers(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.USERS.LIST + buildQueryString({ limit, offset, ...(query ? { search: query } : {}) });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch users');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Users] Fetch failed', err);
        return [];
    }
}

async function fetchUser(id) {
    try {
        const res = await apiRequest(API_ROUTES.ADMIN_VIEWS.DETAIL('users', id));
        if (!res.success) throw new Error(res.message || 'Failed to fetch user');
        return res.data;
    } catch (err) { throw err; }
}

// ─── Row Renderer ─────────────────────────────────────────────────────────────

function renderRow(u) {
    const isActive    = u.is_active !== false && u.is_active !== 'f';
    const statusBadge = isActive
        ? `<span class="badge badge-active">Active</span>`
        : `<span class="badge badge-inactive">Inactive</span>`;
    const roleBadge   = u.is_admin
        ? `<span class="badge badge-warning">Admin</span>`
        : `<span class="badge badge-info">User</span>`;
    const avatar = u.profile_image_url
        ? `<img src="${escapeHtml(u.profile_image_url)}" class="thumb-md rounded-full border shadow-sm" alt="${escapeHtml(u.name || '')}"`
        + ` style="flex-shrink:0;">`
        : `<div class="thumb-md rounded-full bg-slate-100 border flex items-center justify-center font-bold text-slate-400"
                style="flex-shrink:0;font-size:14px;">${escapeHtml((u.name || 'U')[0].toUpperCase())}</div>`;
    const joined    = u.created_at     ? formatDate(u.created_at)     : '—';
    const lastLogin = u.last_login_at  ? formatDate(u.last_login_at)  : 'Never';

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(u.id))}</td>
        <td class="td">
            <div class="flex items-center" style="gap:10px;">
                ${avatar}
                <div>
                    <div class="font-semibold text-black" style="font-size:13px;">${escapeHtml(u.name || 'N/A')}</div>
                    <div class="text-slate-500" style="font-size:11px;">${escapeHtml(u.email || '')}</div>
                </div>
            </div>
        </td>
        <td class="td text-slate-500" style="font-size:12px;">${escapeHtml(u.phone || '—')}</td>
        <td class="td">${statusBadge}</td>
        <td class="td">${roleBadge}</td>
        <td class="td text-slate-500" style="font-size:11px;white-space:nowrap;">${joined}</td>
        <td class="td text-slate-500" style="font-size:11px;white-space:nowrap;">${lastLogin}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${u.id}" title="View">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${u.id}" title="Edit">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${u.id}" title="Delete"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="8" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function getOrderStatusBadge(s) {
    const map = { completed: 'badge-active', paid: 'badge-active', delivered: 'badge-active',
                  pending: 'badge-info', processing: 'badge-info', shipped: 'badge-info',
                  cancelled: 'badge-inactive', returned: 'badge-inactive' };
    return map[s?.toLowerCase()] || 'badge-info';
}

function renderViewModal(u) {
    const ltv    = (parseFloat(u.lifetime_value_cents || 0) / 100).toFixed(2);
    const aov    = (parseFloat(u.avg_order_value_cents || 0) / 100).toFixed(2);
    const isActive = u.is_active !== false && u.is_active !== 'f';

    const ordersRows = Array.isArray(u.recent_orders) && u.recent_orders.length
        ? u.recent_orders.map(o =>
            `<tr class="tr">
                <td class="td font-mono" style="font-size:11px;font-weight:600;">${escapeHtml(o.order_number || `#${o.id}`)}</td>
                <td class="td"><span class="badge ${getOrderStatusBadge(o.status)}" style="font-size:10px;">${escapeHtml(o.status || '—')}</span></td>
                <td class="td text-right font-bold font-mono" style="font-size:12px;">Rs ${(parseFloat(o.total_cents || 0) / 100).toFixed(2)}</td>
            </tr>`).join('')
        : `<tr class="tr"><td colspan="3" class="td text-center text-slate-400" style="padding:20px;font-style:italic;">No recent orders</td></tr>`;

    const avatar = u.profile_image_url
        ? `<img src="${escapeHtml(u.profile_image_url)}" class="thumb-xl rounded-full border shadow-md" alt="${escapeHtml(u.name || '')}">`
        : `<div class="thumb-xl rounded-full bg-slate-100 border flex items-center justify-center font-bold text-slate-400"
                style="font-size:32px;">${escapeHtml((u.name || 'U')[0].toUpperCase())}</div>`;

    return `
        <div class="flex flex-col" style="gap:20px;">
            <!-- Header -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div class="flex items-center" style="gap:14px;">
                    ${avatar}
                    <div>
                        <div class="font-bold text-black" style="font-size:20px;letter-spacing:-0.02em;">${escapeHtml(u.name || 'N/A')}</div>
                        <div class="text-slate-500" style="font-size:13px;">${escapeHtml(u.email || '')}</div>
                        <div class="flex items-center" style="gap:6px;margin-top:6px;">
                            ${isActive ? `<span class="badge badge-active">Active</span>` : `<span class="badge badge-inactive">Inactive</span>`}
                            ${u.is_admin ? `<span class="badge badge-warning">Admin</span>` : `<span class="badge badge-info">User</span>`}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Strip -->
            <div class="flex" style="gap:12px;">
                <div class="google-card flex-1 text-center" style="padding:14px;">
                    <div class="uppercase text-slate-500 font-semibold" style="font-size:10px;letter-spacing:0.05em;">LTV</div>
                    <div class="font-bold text-black font-mono" style="font-size:20px;">Rs ${ltv}</div>
                </div>
                <div class="google-card flex-1 text-center" style="padding:14px;">
                    <div class="uppercase text-slate-500 font-semibold" style="font-size:10px;letter-spacing:0.05em;">Orders</div>
                    <div class="font-bold text-black" style="font-size:20px;">${u.total_orders || 0}</div>
                </div>
                <div class="google-card flex-1 text-center" style="padding:14px;">
                    <div class="uppercase text-slate-500 font-semibold" style="font-size:10px;letter-spacing:0.05em;">Avg Order</div>
                    <div class="font-bold text-black font-mono" style="font-size:20px;">Rs ${aov}</div>
                </div>
                <div class="google-card flex-1 text-center" style="padding:14px;">
                    <div class="uppercase text-slate-500 font-semibold" style="font-size:10px;letter-spacing:0.05em;">Addresses</div>
                    <div class="font-bold text-black" style="font-size:20px;">${u.address_count || 0}</div>
                </div>
            </div>

            <!-- Details + Orders -->
            <div class="flex" style="gap:16px;">
                <div style="flex:1;">
                    <div class="uppercase text-slate-400 font-semibold" style="font-size:11px;letter-spacing:0.05em;margin-bottom:10px;">Account Details</div>
                    <div class="google-card" style="padding:14px;">
                        ${[
                            ['ID', `#${u.id}`],
                            ['Phone', u.phone || '—'],
                            ['Joined', u.created_at ? formatDate(u.created_at) : '—'],
                            ['Last Login', u.last_login_at ? formatDate(u.last_login_at) : 'Never'],
                            ['Completed', u.completed_orders || 0],
                            ['Pending', u.pending_orders || 0],
                            ['Cancelled', u.cancelled_orders || 0],
                        ].map(([l, v]) => `
                            <div class="flex justify-between" style="padding:5px 0;border-bottom:1px solid var(--slate-50);">
                                <span class="text-slate-500" style="font-size:12px;">${l}</span>
                                <span class="font-semibold text-black" style="font-size:12px;">${v}</span>
                            </div>`).join('')}
                    </div>
                </div>
                <div style="flex:1.5;">
                    <div class="uppercase text-slate-400 font-semibold" style="font-size:11px;letter-spacing:0.05em;margin-bottom:10px;">Recent Orders</div>
                    <div class="table-container" style="border-radius:10px;">
                        <table class="table">
                            <thead>
                                <tr class="tr">
                                    <th class="th">Order #</th>
                                    <th class="th">Status</th>
                                    <th class="th text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>${ordersRows}</tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${u.id}" style="padding:0 28px;">✏️ Edit User</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(userId = null) {
    const isEdit = userId !== null;
    let u = {};
    if (isEdit) u = await fetchUser(userId);

    const frag = getTemplate('tpl-user-form', {
        name:                    escapeHtml(u.name || ''),
        email:                   escapeHtml(u.email || ''),
        phone:                   escapeHtml(u.phone || ''),
        image_url:               escapeHtml(u.profile_image_url || ''),
        image_display:           u.profile_image_url ? 'block' : 'none',
        admin_checked:           u.is_admin ? 'checked' : '',
        active_checked:          u.is_active !== false ? 'checked' : '',
        submit_text:             isEdit ? 'Save Changes' : 'Create User',
        password_required:       isEdit ? '' : 'required',
        password_required_class: isEdit ? '' : 'field-required',
        password_placeholder:    isEdit ? 'Leave blank to keep' : 'Min 8 characters',
        info_display:            isEdit ? 'block' : 'none',
        created_at:              u.created_at ? formatDate(u.created_at) : '—',
        last_login:              u.last_login_at ? formatDate(u.last_login_at) : 'Never',
    });

    if (isEdit) {
        const footer = frag.querySelector('.flex.justify-end.gap-3.pt-6');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-outline text-danger';
            del.style.marginRight = 'auto';
            del.id = 'user-delete-btn';
            del.dataset.id = userId;
            del.innerHTML = '🗑️ Delete User';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, userId, onSuccess) {
    const isEdit    = userId !== null;
    const form      = modalRoot.querySelector('#usr-form');
    const cancel    = modalRoot.querySelector('#usr-cancel');
    const delBtn    = modalRoot.querySelector('#user-delete-btn');
    const imgPrev   = modalRoot.querySelector('#usr-image-preview');
    const imgHidden = modalRoot.querySelector('#usr-image-hidden');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    initImageUpload(modalRoot, 'users', 'usr-image-file', (url) => {
        if (imgHidden) imgHidden.value = url;
        if (imgPrev)  { imgPrev.src = url; imgPrev.style.display = 'block'; }
        const label = modalRoot.querySelector('label[for="usr-image-file"]');
        if (label) label.textContent = '✅ Avatar Uploaded';
    });

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1';
                delBtn.innerHTML = '⚠️ Confirm Delete?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Delete User'; delBtn.classList.remove('btn-warning'); }, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Deleting…';
            try {
                await apiRequest(API_ROUTES.USERS.DELETE(userId), { method: 'DELETE' });
                closeModal(); onSuccess?.(null, 'deleted');
            } catch (err) {
                showFormError(form, err.message);
                delBtn.disabled = false; delBtn.innerHTML = '🗑️ Delete User';
                delete delBtn.dataset.confirmed;
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig   = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Saving…' : 'Creating…';
        try {
            const data    = getFormData(form);
            const payload = {
                name:              data.name,
                email:             data.email,
                phone:             data.phone || null,
                profile_image_url: (imgHidden ? imgHidden.value : null) || data.profile_image_url || null,
                is_active:         data.is_active !== undefined,
                is_admin:          data.is_admin  !== undefined,
            };
            if (data.password) payload.password = data.password;
            const url = isEdit ? API_ROUTES.USERS.UPDATE(userId) : API_ROUTES.USERS.CREATE;
            const res = await apiRequest(url, { method: isEdit ? 'PUT' : 'POST', body: payload });
            if (!res.success) throw new Error(res.message);
            closeModal(); onSuccess?.(res.data, isEdit ? 'updated' : 'created');
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

async function reloadUsers(container) {
    const html = await Users();
    container.innerHTML = html;
    await initUsers(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No users found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:200px;">User</th>
    <th class="th" style="width:120px;">Phone</th>
    <th class="th" style="width:80px;">Status</th>
    <th class="th" style="width:70px;">Role</th>
    <th class="th" style="width:130px;">Joined</th>
    <th class="th" style="width:130px;">Last Login</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function Users() {
    _offset = 0;
    const data = await fetchUsers(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No users found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Users',
        'entity-subtitle': `${_lastResults.length} user${_lastResults.length === 1 ? '' : 's'} found`,
    });

    frag.querySelector('#entity-search').placeholder = 'Search name, email, phone…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ New User';
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

export function initUsers(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:users:query', _query); _offset = 0;
        const data = await fetchUsers(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => {
        if (e.target.id === 'entity-search') performSearch(e.target.value.trim());
    }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const u = await fetchUser(btn.dataset.id);
            openStandardModal({ title: 'User Account Details', bodyHtml: renderViewModal(u), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit User', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadUsers(container));
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
            openStandardModal({ title: 'Edit User', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadUsers(container));
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
            await apiRequest(API_ROUTES.USERS.DELETE(id), { method: 'DELETE' });
            reloadUsers(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Delete failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Create New User', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadUsers(container));
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchUsers(DEFAULT_LIMIT, _offset, _query);
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
        await reloadUsers(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}

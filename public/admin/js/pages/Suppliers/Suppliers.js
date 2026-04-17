/**
 * Suppliers.js — Modernized Suppliers domain module.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = '';
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchSuppliers(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.SUPPLIERS.LIST + buildQueryString({ limit, offset, ...(query ? { search: query } : {}) });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch suppliers');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Suppliers]', err);
        return [];
    }
}

async function fetchSupplier(id) {
    try {
        const res = await apiRequest(API_ROUTES.ADMIN_VIEWS.DETAIL('suppliers', id));
        if (!res.success) throw new Error(res.message || 'Not found');
        return res.data;
    } catch (err) { throw err; }
}

// ─── Row Renderer ─────────────────────────────────────────────────────────────

function renderRow(sup) {
    const isActive    = sup.is_active !== false && sup.is_active !== 'f';
    const statusBadge = isActive
        ? `<span class="badge badge-active">Active</span>`
        : `<span class="badge badge-inactive">Inactive</span>`;
    const created = sup.created_at ? formatDate(sup.created_at) : '—';

    return `<tr class="tr">
        <td class="td" style="color:#94a3b8;font-size:11px;font-family:monospace;">#${escapeHtml(String(sup.id))}</td>
        <td class="td">
            <div style="font-weight:600;color:#0f172a;font-size:13px;">${escapeHtml(sup.name || '—')}</div>
        </td>
        <td class="td" style="font-size:12px;color:#475569;">${escapeHtml(sup.email || '—')}</td>
        <td class="td" style="font-size:12px;color:#475569;white-space:nowrap;">${escapeHtml(sup.phone || '—')}</td>
        <td class="td" style="font-size:11px;color:#64748b;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(sup.address || '—')}</td>
        <td class="td">${statusBadge}</td>
        <td class="td" style="font-size:11px;color:#64748b;white-space:nowrap;">${created}</td>
        <td class="td" style="white-space:nowrap;">
            <div style="display:flex;gap:6px;align-items:center;">
                <button class="btn btn-outline btn-sm js-view" data-id="${sup.id}" title="View" style="padding:4px 10px;font-size:12px;">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${sup.id}" title="Edit" style="padding:4px 10px;font-size:12px;">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${sup.id}" title="Delete" style="padding:4px 8px;font-size:12px;color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="8" class="td text-center" style="padding:48px;color:#94a3b8;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(sup) {
    const avgPrice = sup.avg_product_price_cents ? (sup.avg_product_price_cents / 100).toFixed(2) : '—';
    const isActive = sup.is_active !== false && sup.is_active !== 'f';

    const productsList = Array.isArray(sup.products) && sup.products.length
        ? sup.products.slice(0, 6).map(p =>
            `<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:13px;">${escapeHtml(p.name || 'Unknown')}</span>
                <span style="font-size:12px;font-weight:600;font-family:monospace;">Rs ${((p.price_cents || 0) / 100).toFixed(2)}</span>
            </div>`).join('')
        : '<p style="font-size:13px;color:#94a3b8;margin:0;">No products supplied</p>';

    const moreCount = Array.isArray(sup.products) && sup.products.length > 6
        ? `<div style="font-size:11px;color:#94a3b8;margin-top:6px;">+ ${sup.products.length - 6} more products</div>` : '';

    const metaRow = (label, val) =>
        `<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f8fafc;">
            <span style="color:#64748b;font-size:13px;">${label}</span>
            <span style="font-weight:600;font-size:13px;color:#0f172a;">${val}</span>
        </div>`;

    return `
        <div style="display:flex;flex-direction:column;gap:20px;">
            <!-- Header -->
            <div style="display:flex;align-items:center;gap:16px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
                <div style="width:64px;height:64px;background:#f1f5f9;border-radius:12px;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:28px;">🏭</div>
                <div>
                    <h3 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-0.025em;">${escapeHtml(sup.name)}</h3>
                    <div style="margin-top:8px;display:flex;gap:8px;">
                        ${isActive ? `<span class="badge badge-active">Active</span>` : `<span class="badge badge-inactive">Inactive</span>`}
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:0.05em;">Products</div>
                    <div style="font-size:24px;font-weight:800;color:#1d4ed8;">${sup.total_products ?? 0}</div>
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">In Stock</div>
                    <div style="font-size:24px;font-weight:800;color:#15803d;">${sup.total_inventory ?? 0}</div>
                </div>
                <div style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#ca8a04;text-transform:uppercase;letter-spacing:0.05em;">Avg Price</div>
                    <div style="font-size:18px;font-weight:800;color:#b45309;font-family:monospace;">Rs ${avgPrice}</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <!-- Contact -->
                <div>
                    <h4 style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 10px;">📋 Contact Info</h4>
                    ${metaRow('ID', `#${sup.id}`)}
                    ${metaRow('Email', sup.email ? `<a href="mailto:${escapeHtml(sup.email)}" style="color:#2563eb;">${escapeHtml(sup.email)}</a>` : '—')}
                    ${metaRow('Phone', sup.phone || '—')}
                    ${metaRow('Address', sup.address ? `<span style="max-width:160px;text-align:right;display:inline-block;">${escapeHtml(sup.address)}</span>` : '—')}
                    ${metaRow('Created', sup.created_at ? formatDate(sup.created_at) : '—')}
                    ${metaRow('Updated', sup.updated_at ? formatDate(sup.updated_at) : '—')}
                </div>
                <!-- Products -->
                <div>
                    <h4 style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 10px;">📦 Supplied Products</h4>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                        ${productsList}${moreCount}
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;padding-top:12px;border-top:1px solid #f1f5f9;gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${sup.id}" style="padding:0 28px;">✏️ Edit Supplier</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(supplierId = null) {
    const isEdit = supplierId !== null;
    let sup = {};
    if (isEdit) sup = await fetchSupplier(supplierId);

    const frag = getTemplate('tpl-supplier-form', {
        name:              escapeHtml(sup.name || ''),
        email:             escapeHtml(sup.email || ''),
        phone:             escapeHtml(sup.phone || ''),
        address:           escapeHtml(sup.address || ''),
        is_active_checked: sup.is_active !== false ? 'checked' : '',
        submit_text:       isEdit ? 'Save Changes' : 'Create Supplier',
        stats_display:     isEdit ? 'block' : 'none',
        product_count:     sup.total_products ?? 0,
        created_at:        formatDate(sup.created_at)
    });

    if (isEdit) {
        const footer = frag.querySelector('.sup-form-footer');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-outline text-danger mr-auto';
            del.id = 'sup-delete-btn';
            del.dataset.id = supplierId;
            del.innerHTML = '🗑️ Delete';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, supplierId, onSuccess) {
    const isEdit = supplierId !== null;
    const form   = modalRoot.querySelector('#sup-form');
    const cancel = modalRoot.querySelector('#sup-cancel');
    const delBtn = modalRoot.querySelector('#sup-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1';
                delBtn.innerHTML = '⚠️ Confirm Delete?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Delete'; delBtn.classList.remove('btn-warning'); }, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Deleting…';
            try {
                await apiRequest(API_ROUTES.SUPPLIERS.DELETE(supplierId), { method: 'DELETE' });
                closeModal(); onSuccess?.(null, 'deleted');
            } catch (err) {
                showFormError(form, err.message);
                delBtn.disabled = false; delBtn.innerHTML = '🗑️ Delete';
                delete delBtn.dataset.confirmed;
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('[type="submit"]');
        const orig   = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Saving…' : 'Creating…';
        try {
            const data    = getFormData(form);
            const payload = { name: data.name, email: data.email || null, phone: data.phone || null, address: data.address || null, is_active: data.is_active !== undefined };
            const url     = isEdit ? API_ROUTES.SUPPLIERS.UPDATE(supplierId) : API_ROUTES.SUPPLIERS.CREATE;
            const res     = await apiRequest(url, { method: isEdit ? 'PUT' : 'POST', body: payload });
            if (!res.success) throw new Error(res.message || 'Request failed');
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

async function reloadSuppliers(container) {
    const html = await Suppliers();
    container.innerHTML = html;
    await initSuppliers(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No suppliers found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:160px;">Supplier</th>
    <th class="th">Email</th>
    <th class="th" style="width:130px;">Phone</th>
    <th class="th" style="min-width:180px;">Address</th>
    <th class="th" style="width:80px;">Status</th>
    <th class="th" style="width:130px;">Created</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function Suppliers() {
    _offset = 0;
    const data = await fetchSuppliers(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No suppliers found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Suppliers',
        'entity-subtitle': `${_lastResults.length} supplier${_lastResults.length === 1 ? '' : 's'} found`,
    });

    frag.querySelector('#entity-search').placeholder = 'Search name, email…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ New Supplier';
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

export function initSuppliers(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const debouncedSearch = debounce(async (e) => {
        _query = e.target.value.trim(); _offset = 0;
        const data = await fetchSuppliers(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') debouncedSearch(e); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const sup = await fetchSupplier(btn.dataset.id);
            openStandardModal({ title: `${escapeHtml(sup.name)} — Details`, bodyHtml: renderViewModal(sup), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const frag = await renderFormModal(parseInt(editBtn.dataset.id));
                        openStandardModal({ title: 'Edit Supplier', bodyHtml: frag.firstElementChild.outerHTML, size: 'lg' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), parseInt(editBtn.dataset.id), () => reloadSuppliers(container));
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
        const id = parseInt(btn.dataset.id);
        try {
            const frag = await renderFormModal(id);
            openStandardModal({ title: 'Edit Supplier', bodyHtml: frag.firstElementChild.outerHTML, size: 'lg' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), id, () => reloadSuppliers(container));
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
            await apiRequest(API_ROUTES.SUPPLIERS.DELETE(id), { method: 'DELETE' });
            reloadSuppliers(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Delete failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        const frag = await renderFormModal(null);
        openStandardModal({ title: 'Create Supplier', bodyHtml: frag.firstElementChild.outerHTML, size: 'lg' });
        initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadSuppliers(container));
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchSuppliers(DEFAULT_LIMIT, _offset, _query);
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
        await reloadSuppliers(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
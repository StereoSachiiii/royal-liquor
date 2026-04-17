/**
 * Categories.js — Consolidated Categories domain module.
 *
 * Rows rendered as inline HTML strings to avoid the getTemplate
 * browser-parsing bug that orphans <tr> elements outside the table.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatNumber, formatDate, debounce, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';
import { uploadImage } from '../../FormHelpers.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = '';
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchCategories(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.CATEGORIES.LIST + buildQueryString({
            limit, offset, enriched: 'true',
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch categories');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Categories]', err);
        return [];
    }
}

async function fetchCategory(id) {
    try {
        const url = API_ROUTES.CATEGORIES.GET(id) + buildQueryString({ enriched: 'true' });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch category');
        return res.data;
    } catch (err) { throw err; }
}

// ─── Row Renderer (inline HTML — avoids getTemplate table-parsing bug) ────────

function renderRow(cat) {
    const isActive = cat.is_active !== false && cat.is_active !== 'f';
    const statusBadge = isActive
        ? `<span class="inline-flex items-center px-2 py-1 text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 rounded-none">Active</span>`
        : `<span class="inline-flex items-center px-2 py-1 text-[9px] font-black uppercase tracking-wider bg-gray-100 text-gray-400 rounded-none">Inactive</span>`;

    const imgHtml = cat.image_url
        ? `<img src="${escapeHtml(cat.image_url)}" class="w-10 h-10 object-cover border border-gray-100" alt="${escapeHtml(cat.name)}">`
        : `<div class="w-10 h-10 bg-gray-50 border border-gray-100 flex items-center justify-center text-xs">🏷️</div>`;

    return `
        <tr class="group hover:bg-gray-50/50 transition-colors">
            <td class="px-8 py-5 text-[10px] font-bold text-gray-300 font-mono whitespace-nowrap">#${escapeHtml(String(cat.id))}</td>
            <td class="px-8 py-5">
                <div class="flex items-center gap-4">
                    ${imgHtml}
                    <div>
                        <div class="text-sm font-black text-black tracking-tight">${escapeHtml(cat.name || '—')}</div>
                        ${cat.description ? `<div class="text-[10px] text-gray-400 font-medium mt-0.5 max-w-[200px] truncate">${escapeHtml(cat.description)}</div>` : ''}
                    </div>
                </div>
            </td>
            <td class="px-8 py-5">
                <div class="text-sm font-medium text-gray-600 tabular-nums">${cat.product_count ?? 0} items</div>
            </td>
            <td class="px-8 py-5">${statusBadge}</td>
            <td class="px-8 py-5 text-right">
                <div class="flex items-center justify-end gap-2">
                    <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-black hover:bg-black hover:text-white transition-all js-view" data-id="${cat.id}" title="View details">
                        <span class="text-[10px]">👁️</span>
                    </button>
                    <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-black hover:bg-black hover:text-white transition-all js-edit" data-id="${cat.id}" title="Edit category">
                        <span class="text-[10px]">✏️</span>
                    </button>
                    <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-red-600 hover:bg-red-600 hover:text-white transition-all js-delete" data-id="${cat.id}" title="Delete category">
                        <span class="text-[10px]">🗑️</span>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

function emptyRow(msg) {
    return `<tr><td colspan="5" class="px-8 py-20 text-center text-xs font-medium text-gray-400">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(cat) {
    const avgPrice = cat.avg_price_cents ? (cat.avg_price_cents / 100).toFixed(2) : '—';
    const minPrice = cat.min_price_cents ? (cat.min_price_cents / 100).toFixed(2) : '—';
    const maxPrice = cat.max_price_cents ? (cat.max_price_cents / 100).toFixed(2) : '—';
    const isActive = cat.is_active !== false && cat.is_active !== 'f';

    const topProducts = Array.isArray(cat.top_products) && cat.top_products.length
        ? cat.top_products.map(p => `
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:13px;">${escapeHtml(p.name || 'Unknown')}</span>
                <span style="font-size:13px;font-weight:600;font-family:monospace;">Rs ${((p.price_cents || 0) / 100).toFixed(2)}</span>
            </div>`).join('')
        : '<p style="font-size:13px;color:#94a3b8;margin:0;">No products in this category</p>';

    const metaRow = (label, val) =>
        `<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f8fafc;">
            <span style="color:#64748b;font-size:13px;">${label}</span>
            <span style="font-weight:600;font-size:13px;color:#0f172a;">${val}</span>
        </div>`;

    return `
        <div style="display:flex;flex-direction:column;gap:20px;">
            <!-- Header -->
            <div style="display:flex;align-items:center;gap:16px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
                ${cat.image_url
                    ? `<img src="${escapeHtml(cat.image_url)}" style="width:80px;height:80px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0;" alt="${escapeHtml(cat.name)}">`
                    : `<div style="width:80px;height:80px;background:#f1f5f9;border-radius:12px;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:32px;">🏷️</div>`
                }
                <div>
                    <h3 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-0.025em;">${escapeHtml(cat.name)}</h3>
                    <code style="font-size:11px;color:#94a3b8;">${escapeHtml(cat.slug || '')}</code>
                    <div style="margin-top:8px;">
                        ${isActive ? `<span class="badge badge-active">Active</span>` : `<span class="badge badge-inactive">Inactive</span>`}
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:0.05em;">Products</div>
                    <div style="font-size:24px;font-weight:800;color:#1d4ed8;">${cat.total_products ?? cat.product_count ?? 0}</div>
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">Active</div>
                    <div style="font-size:24px;font-weight:800;color:#15803d;">${cat.active_products ?? 0}</div>
                </div>
                <div style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#ca8a04;text-transform:uppercase;letter-spacing:0.05em;">Total Sales</div>
                    <div style="font-size:24px;font-weight:800;color:#b45309;">${cat.total_sales ?? 0}</div>
                </div>
                <div style="background:#fdf4ff;border:1px solid #e9d5ff;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#9333ea;text-transform:uppercase;letter-spacing:0.05em;">Avg Price</div>
                    <div style="font-size:18px;font-weight:800;color:#7c3aed;font-family:monospace;">Rs ${avgPrice}</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <h4 style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 10px;">📋 Details</h4>
                    ${metaRow('ID', `#${cat.id}`)}
                    ${metaRow('Price Range', `Rs ${minPrice} – Rs ${maxPrice}`)}
                    ${metaRow('Description', cat.description || '—')}
                    ${metaRow('Created', cat.created_at ? formatDate(cat.created_at) : '—')}
                </div>
                <div>
                    <h4 style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 10px;">⭐ Top Products</h4>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                        ${topProducts}
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;padding-top:12px;border-top:1px solid #f1f5f9;gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${cat.id}" style="padding:0 28px;">✏️ Edit Category</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(categoryId = null) {
    const isEdit = categoryId !== null;
    let cat = {};
    if (isEdit) cat = await fetchCategory(categoryId);

    const frag = getTemplate('tpl-category-form', {
        name:              escapeHtml(cat.name || ''),
        slug:              escapeHtml(cat.slug || ''),
        description:       escapeHtml(cat.description || ''),
        image_url:         escapeHtml(cat.image_url || ''),
        image_display:     cat.image_url ? 'block' : 'none',
        is_active_checked: cat.is_active !== false ? 'checked' : '',
        submit_text:       isEdit ? 'Save Changes' : 'Create Category',
    });

    if (isEdit) {
        const footer = frag.querySelector('.cat-form-footer');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-outline text-danger mr-auto';
            del.id = 'cat-delete-btn';
            del.dataset.id = categoryId;
            del.innerHTML = '🗑️ Delete';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, categoryId, onSuccess) {
    const isEdit  = categoryId !== null;
    const form    = modalRoot.querySelector('#cat-form');
    const cancel  = modalRoot.querySelector('#cat-cancel');
    const delBtn  = modalRoot.querySelector('#cat-delete-btn');
    const imgFile = modalRoot.querySelector('#cat-image-file');
    const imgPrev = modalRoot.querySelector('#cat-image-preview');
    const imgHid  = modalRoot.querySelector('#cat-image-hidden');
    const nameInp = modalRoot.querySelector('[name="name"]');
    const slugInp = modalRoot.querySelector('[name="slug"]');

    if (!form) return;

    // Auto-slug
    if (!isEdit && nameInp && slugInp) {
        nameInp.addEventListener('input', () => {
            if (!slugInp.dataset.manual) {
                slugInp.value = nameInp.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
        slugInp.addEventListener('input', () => { slugInp.dataset.manual = '1'; });
    }

    // Image upload
    if (imgFile) {
        imgFile.addEventListener('change', async (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            const label = modalRoot.querySelector('label[for="cat-image-file"]');
            if (label) label.textContent = 'Uploading…';
            try {
                const url = await uploadImage(file, 'categories');
                if (imgHid) imgHid.value = url;
                if (imgPrev) { imgPrev.src = url; imgPrev.style.display = 'block'; }
                if (label) label.textContent = '✅ Change Image';
            } catch (err) {
                if (label) label.textContent = '✗ Upload Failed';
                console.error('[Categories] Image upload error:', err);
            }
        });
    }

    if (cancel) cancel.addEventListener('click', () => closeModal());

    // Delete
    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1';
                delBtn.innerHTML = '⚠️ Confirm Delete?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => {
                    delete delBtn.dataset.confirmed;
                    delBtn.innerHTML = '🗑️ Delete';
                    delBtn.classList.remove('btn-warning');
                }, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Deleting…';
            try {
                await apiRequest(API_ROUTES.CATEGORIES.DELETE(categoryId), { method: 'DELETE' });
                closeModal();
                onSuccess?.(null, 'deleted');
            } catch (err) {
                showFormError(form, err.message);
                delBtn.disabled = false; delBtn.innerHTML = '🗑️ Delete';
                delete delBtn.dataset.confirmed;
            }
        });
    }

    // Submit
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('[type="submit"]');
        const orig   = submit.innerHTML;
        submit.disabled = true;
        submit.innerHTML = isEdit ? 'Saving…' : 'Creating…';
        try {
            const data    = getFormData(form);
            const payload = {
                name:        data.name,
                slug:        data.slug || undefined,
                description: data.description || '',
                image_url:   (imgHid ? imgHid.value : null) || data.image_url || null,
                is_active:   data.is_active !== undefined,
            };
            const url    = isEdit ? API_ROUTES.CATEGORIES.UPDATE(categoryId) : API_ROUTES.CATEGORIES.CREATE;
            const method = isEdit ? 'PUT' : 'POST';
            const res    = await apiRequest(url, { method, body: payload });
            if (!res.success) throw new Error(res.message || 'Request failed');
            closeModal();
            onSuccess?.(res.data, isEdit ? 'updated' : 'created');
        } catch (err) {
            showFormError(form, err.message);
            submit.disabled = false; submit.innerHTML = orig;
        }
    });
}

function showFormError(form, msg) {
    let el = form.querySelector('.form-error-banner');
    if (!el) {
        el = Object.assign(document.createElement('div'), { className: 'form-error-banner' });
        form.prepend(el);
    }
    el.textContent = `Error: ${msg}`;
    el.style.display = 'block';
}

// ─── Page Reload Helper ───────────────────────────────────────────────────────

async function reloadCategories(container) {
    const html = await Categories();
    container.innerHTML = html;
    await initCategories(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No categories found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else {
        lmc.style.display = 'none';
        lmc.innerHTML = '';
    }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `
    <tr>
        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">ID</th>
        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Category</th>
        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Products</th>
        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Status</th>
        <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Actions</th>
    </tr>`;


export async function Categories() {
    _offset = 0;
    const data = await fetchCategories(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No categories found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Categories',
        'entity-subtitle': `${_lastResults.length} categor${_lastResults.length === 1 ? 'y' : 'ies'} found`,
    });

    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-search').placeholder = 'Search by name or description…';
    frag.querySelector('#entity-sort').remove();
    frag.querySelector('#entity-create-btn').innerHTML = 'Add Category';
    frag.querySelector('#entity-thead').innerHTML = THEAD;
    frag.querySelector('#entity-tbody').innerHTML = rows;

    const lmc = frag.querySelector('#entity-load-more-container');
    if (_lastResults.length === DEFAULT_LIMIT) {
        lmc.classList.remove('hidden');
    }

    return frag.firstElementChild.outerHTML;
}

// ─── Listeners / Init ─────────────────────────────────────────────────────────

export function initCategories(container) {
    if (!container) return null;
    const ac     = new AbortController();
    const signal = ac.signal;

    const debouncedSearch = debounce(async (e) => {
        _query  = e.target.value.trim();
        _offset = 0;
        const data = await fetchCategories(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => {
        if (e.target.id === 'entity-search') debouncedSearch(e);
    }, { signal });

    // Modal Action Redirects (Edit from inside View Modal)
    // We attach this to container using signal so it cleans up.
    container.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.modal-overlay .js-edit');
        if (!editBtn) return;
        
        const id = parseInt(editBtn.dataset.id);
        closeModal();
        setTimeout(async () => {
            const frag = await renderFormModal(id);
            openStandardModal({ title: 'Edit Category', bodyHtml: frag.firstElementChild.outerHTML, size: 'lg' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), id, () => reloadCategories(container));
        }, 200);
    }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return; // Ignore if inside another modal
        try {
            const cat = await fetchCategory(btn.dataset.id);
            openStandardModal({ title: `${escapeHtml(cat.name)} — Details`, bodyHtml: renderViewModal(cat), size: 'xl' });
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
            openStandardModal({ title: 'Edit Category', bodyHtml: frag.firstElementChild.outerHTML, size: 'lg' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), id, () => reloadCategories(container));
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Delete (direct from row)
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-delete');
        if (!btn) return;
        const id = btn.dataset.id;
        if (!btn.dataset.confirmed) {
            btn.dataset.confirmed = '1';
            btn.innerHTML = '⚠️';
            btn.style.background = '#fef9c3';
            setTimeout(() => { if (btn.isConnected) { delete btn.dataset.confirmed; btn.innerHTML = '🗑'; btn.style.background = ''; }}, 3000);
            return;
        }
        btn.disabled = true; btn.innerHTML = '…';
        try {
            await apiRequest(API_ROUTES.CATEGORIES.DELETE(id), { method: 'DELETE' });
            reloadCategories(container);
        } catch (err) {
            btn.disabled = false; btn.innerHTML = '🗑';
            alert('Delete failed: ' + err.message);
        }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        const frag = await renderFormModal(null);
        openStandardModal({ title: 'Create Category', bodyHtml: frag.firstElementChild.outerHTML, size: 'lg' });
        initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadCategories(container));
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchCategories(DEFAULT_LIMIT, _offset, _query);
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
        await reloadCategories(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
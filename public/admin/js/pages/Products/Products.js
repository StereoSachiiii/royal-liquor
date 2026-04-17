/**
 * Products.js — Consolidated Products domain module (Modernized).
 *
 * Uses /products/enriched/all for rich data: stock, sales, ratings, supplier.
 * Rows rendered as inline HTML strings to avoid getTemplate table-parsing bugs.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatCurrency, formatNumber, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';
import { initImageUpload } from '../../FormHelpers.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:products:query', '');
let _sort   = getState('admin:products:sort', 'newest');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchProducts(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.PRODUCTS.ENRICHED_ALL + buildQueryString({ limit, offset, ...(query ? { search: query } : {}) });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch products');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Products] Fetch failed', err);
        return [];
    }
}

async function fetchProduct(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('products', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch product');
        return res.data;
    } catch (err) {
        throw err;
    }
}

async function fetchCategories() {
    try {
        const res = await apiRequest(API_ROUTES.CATEGORIES.LIST + '?limit=100');
        return res.success ? (res.data || []) : [];
    } catch { return []; }
}

async function fetchSuppliers() {
    try {
        const res = await apiRequest(API_ROUTES.SUPPLIERS.LIST + '?limit=100');
        return res.success ? (res.data || []) : [];
    } catch { return []; }
}

// ─── Row Renderer (inline HTML — avoids getTemplate table-parsing bug) ────────

function stockBadge(stock) {
    const n = parseInt(stock || 0);
    if (n <= 0)  return `<span class="badge badge-danger" title="Out of Stock">0</span>`;
    if (n <= 5)  return `<span class="badge badge-warning" title="Low Stock">⚠️ ${n}</span>`;
    return `<span class="badge badge-active">${n}</span>`;
}

function renderRow(p) {
    const price = formatCurrency(p.price_cents || 0);
    const inStock = (p.available_stock ?? p.stock_quantity ?? 0) > 0;
    const stockQty = p.available_stock ?? p.stock_quantity ?? 0;
    const statusBadge = inStock
        ? `<span class="inline-flex items-center px-2 py-1 text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 rounded-none">In Stock</span>`
        : `<span class="inline-flex items-center px-2 py-1 text-[9px] font-black uppercase tracking-wider bg-red-100 text-red-700 rounded-none">Out of Stock</span>`;

    const imgHtml = p.image_url
        ? `<img src="${escapeHtml(p.image_url)}" class="w-10 h-10 object-cover border border-gray-100" alt="${escapeHtml(p.name)}">`
        : `<div class="w-10 h-10 bg-gray-50 border border-gray-100 flex items-center justify-center text-xs">🍾</div>`;

    return `
        <tr class="group hover:bg-gray-50/50 transition-colors">
            <td class="px-8 py-5 text-[10px] font-bold text-gray-300 font-mono whitespace-nowrap">#${escapeHtml(String(p.id))}</td>
            <td class="px-8 py-5">
                <div class="flex items-center gap-4">
                    ${imgHtml}
                    <div>
                        <div class="text-sm font-black text-black tracking-tight">${escapeHtml(p.name || '—')}</div>
                        <div class="text-[10px] text-gray-400 font-medium mt-0.5">${escapeHtml(p.category_name || 'Uncategorized')}</div>
                    </div>
                </div>
            </td>
            <td class="px-8 py-5">
                <div class="text-sm font-bold text-black tabular-nums">${price}</div>
            </td>
            <td class="px-8 py-5 text-center">
                <div class="text-sm font-bold text-black tabular-nums">${formatNumber(stockQty)}</div>
            </td>
            <td class="px-8 py-5 text-center">${statusBadge}</td>
            <td class="px-8 py-5 text-right">
                <div class="flex items-center justify-end gap-2">
                    <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-black hover:bg-black hover:text-white transition-all js-view" data-id="${p.id}" title="View details">
                        <span class="text-[10px]">👁️</span>
                    </button>
                    <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-black hover:bg-black hover:text-white transition-all js-edit" data-id="${p.id}" title="Edit product">
                        <span class="text-[10px]">✏️</span>
                    </button>
                    <button class="w-8 h-8 flex items-center justify-center bg-white border border-gray-100 text-red-600 hover:bg-red-600 hover:text-white transition-all js-delete" data-id="${p.id}" title="Delete product">
                        <span class="text-[10px]">🗑️</span>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

function emptyRow(msg) {
    return `<tr><td colspan="6" class="px-8 py-20 text-center text-xs font-black text-gray-300 uppercase tracking-widest bg-gray-50/30">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(p) {
    const price         = (parseFloat(p.price_cents || 0) / 100).toFixed(2);
    const totalRevenue  = (parseFloat(p.total_revenue_cents || 0) / 100).toFixed(2);
    const stock         = parseInt(p.available_stock || p.stock || 0);
    const rating        = p.avg_rating ? parseFloat(p.avg_rating).toFixed(1) : 'N/A';
    const isActive      = p.is_active === true || p.is_active === 't' || p.is_active === '1';

    const metaRow = (label, value) =>
        `<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9;">
            <span style="color:#64748b;font-size:13px;">${label}</span>
            <span style="font-weight:600;font-size:13px;color:#0f172a;">${value}</span>
        </div>`;

    // Flavor profile if available
    let flavorHtml = '';
    if (p.flavor_profile) {
        let fp = p.flavor_profile;
        if (typeof fp === 'string') { try { fp = JSON.parse(fp); } catch { fp = null; } }
        if (fp && typeof fp === 'object') {
            const attrs = ['sweetness','bitterness','strength','smokiness','fruitiness','spiciness'];
            const bars = attrs.map(attr => {
                const val = parseInt(fp[attr] || 0);
                const pct = Math.round(val * 10);
                const colors = { sweetness:'#ec4899', bitterness:'#f59e0b', strength:'#ef4444', smokiness:'#6b7280', fruitiness:'#10b981', spiciness:'#f97316' };
                return `<div style="margin-bottom:8px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px;">
                        <span>${attr.charAt(0).toUpperCase()+attr.slice(1)}</span><span>${val}/10</span>
                    </div>
                    <div style="background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden;">
                        <div style="width:${pct}%;height:100%;background:${colors[attr]||'#6366f1'};border-radius:4px;transition:width 0.4s;"></div>
                    </div>
                </div>`;
            }).join('');
            const tags = Array.isArray(fp.tags) ? fp.tags.filter(Boolean) : [];
            const tagsHtml = tags.length ? `<div style="margin-top:8px;display:flex;gap:4px;flex-wrap:wrap;">${tags.map(t=>`<span class="badge badge-info" style="font-size:10px;">${escapeHtml(t)}</span>`).join('')}</div>` : '';
            flavorHtml = `
                <div style="margin-top:16px;">
                    <h4 style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 12px;">🍷 Flavor Profile</h4>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
                        ${bars}${tagsHtml}
                    </div>
                </div>`;
        }
    }

    return `
        <div style="display:flex;flex-direction:column;gap:20px;">
            <!-- Header -->
            <div style="display:flex;align-items:center;gap:16px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
                ${p.image_url ? `<img src="${escapeHtml(p.image_url)}" class="thumb-xl border shadow-sm" alt="${escapeHtml(p.name)}" style="border-radius:10px;">` : `<div class="thumb-xl bg-slate-100 border flex items-center justify-center" style="font-size:32px;border-radius:10px;">📦</div>`}
                <div style="flex:1;">
                    <h3 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-0.025em;">${escapeHtml(p.name)}</h3>
                    <code style="font-size:11px;color:#94a3b8;">${escapeHtml(p.slug||'')}</code>
                    <div style="margin-top:8px;display:flex;gap:8px;">
                        ${isActive ? `<span class="badge badge-active">Active</span>` : `<span class="badge badge-inactive">Inactive</span>`}
                        ${p.category_name ? `<span class="badge badge-info">${escapeHtml(p.category_name)}</span>` : ''}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:28px;font-weight:800;color:#0f172a;font-family:monospace;">Rs ${price}</div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px;">per unit</div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">Stock</div>
                    <div style="font-size:24px;font-weight:800;color:#15803d;">${stock}</div>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:0.05em;">Sold</div>
                    <div style="font-size:24px;font-weight:800;color:#1d4ed8;">${parseInt(p.total_sold || p.units_sold || 0)}</div>
                </div>
                <div style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#ca8a04;text-transform:uppercase;letter-spacing:0.05em;">Rating</div>
                    <div style="font-size:24px;font-weight:800;color:#b45309;">⭐ ${rating}</div>
                </div>
                <div style="background:#fdf4ff;border:1px solid #e9d5ff;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#9333ea;text-transform:uppercase;letter-spacing:0.05em;">Revenue</div>
                    <div style="font-size:20px;font-weight:800;color:#7c3aed;font-family:monospace;">Rs ${totalRevenue}</div>
                </div>
            </div>

            <!-- Details -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <h4 style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 10px;">📋 Product Details</h4>
                    ${metaRow('ID', `#${p.id}`)}
                    ${metaRow('Category', p.category_name || '—')}
                    ${metaRow('Supplier', p.supplier_name || 'None')}
                    ${metaRow('Feedback Count', p.feedback_count || 0)}
                    ${metaRow('Created', p.created_at ? formatDate(p.created_at) : '—')}
                    ${metaRow('Updated', p.updated_at ? formatDate(p.updated_at) : '—')}
                </div>
                <div>
                    <h4 style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 10px;">📝 Description</h4>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;font-size:13px;color:#475569;line-height:1.6;min-height:80px;">
                        ${escapeHtml(p.description || 'No description provided.')}
                    </div>
                </div>
            </div>

            ${flavorHtml}

            <div style="display:flex;justify-content:flex-end;padding-top:12px;border-top:1px solid #f1f5f9;gap:8px;">
                <button class="btn btn-outline" onclick="document.querySelector('.modal-overlay').dispatchEvent(new CustomEvent('modal-close'))">Close</button>
                <button class="btn btn-primary js-edit" data-id="${p.id}" style="padding:0 28px;">✏️ Edit Product</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(productId = null) {
    const isEdit = productId !== null;
    let p = {}, categories = [], suppliers = [];

    if (isEdit) {
        [p, categories, suppliers] = await Promise.all([fetchProduct(productId), fetchCategories(), fetchSuppliers()]);
    } else {
        [categories, suppliers] = await Promise.all([fetchCategories(), fetchSuppliers()]);
    }

    const frag = getTemplate('tpl-product-form', {
        name:              escapeHtml(p.name || ''),
        slug:              escapeHtml(p.slug || ''),
        price_cents:       p.price_cents || '',
        description:       escapeHtml(p.description || ''),
        image_url:         escapeHtml(p.image_url || ''),
        image_display:     p.image_url ? 'block' : 'none',
        is_active_checked: p.is_active !== false ? 'checked' : '',
        submit_text:       isEdit ? 'Save Changes' : 'Create Product'
    });

    const catSelect = frag.querySelector('#form-category-select');
    const supSelect = frag.querySelector('#form-supplier-select');

    catSelect.innerHTML = '<option value="" disabled selected>Select Category</option>' +
        categories.map(c => `<option value="${c.id}" ${parseInt(p.category_id) === c.id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`).join('');

    supSelect.innerHTML = '<option value="">No Supplier</option>' +
        suppliers.map(s => `<option value="${s.id}" ${parseInt(p.supplier_id) === s.id ? 'selected' : ''}>${escapeHtml(s.name)}</option>`).join('');

    if (isEdit) {
        const footer = frag.querySelector('.flex.justify-end.gap-3.pt-6');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-outline text-danger mr-auto';
            del.id = 'product-delete-btn';
            del.dataset.id = productId;
            del.innerHTML = '🗑️ Delete Product';
            footer.prepend(del);
        }
    }

    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, productId, onSuccess) {
    const isEdit    = productId !== null;
    const form      = modalRoot.querySelector('#product-form');
    const cancel    = modalRoot.querySelector('#form-cancel');
    const delBtn    = modalRoot.querySelector('#product-delete-btn');
    const imgPrev   = modalRoot.querySelector('#image-preview');
    const imgHidden = modalRoot.querySelector('#image-url-hidden');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    // Image Upload (using prod-file-input which matches the form template id)
    initImageUpload(modalRoot, 'products', 'prod-file-input', (url) => {
        if (imgHidden) imgHidden.value = url;
        if (imgPrev) { imgPrev.src = url; imgPrev.style.display = 'block'; }
        const label = modalRoot.querySelector('label[for="prod-file-input"]');
        if (label) label.textContent = '✅ Image Uploaded';
    });

    // Delete
    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1';
                delBtn.innerHTML = '⚠️ Confirm Delete?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => {
                    delete delBtn.dataset.confirmed;
                    delBtn.innerHTML = '🗑️ Delete Product';
                    delBtn.classList.remove('btn-warning');
                }, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Deleting…';
            try {
                await apiRequest(API_ROUTES.PRODUCTS.DELETE(productId), { method: 'DELETE' });
                closeModal();
                onSuccess?.(null, 'deleted');
            } catch (err) {
                showFormError(form, err.message);
                delBtn.disabled = false; delBtn.innerHTML = '🗑️ Delete Product';
                delete delBtn.dataset.confirmed;
            }
        });
    }

    // Submit
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('#form-submit');
        const orig   = submit.innerHTML;
        submit.disabled = true;
        submit.innerHTML = isEdit ? 'Saving…' : 'Creating…';
        try {
            const data    = getFormData(form);
            const payload = {
                name:        data.name,
                slug:        data.slug,
                description: data.description || null,
                price_cents: parseInt(data.price_cents),
                category_id: parseInt(data.category_id),
                supplier_id: data.supplier_id ? parseInt(data.supplier_id) : null,
                image_url:   (imgHidden ? imgHidden.value : null) || data.image_url || null,
                is_active:   data.is_active !== undefined,
            };
            const url    = isEdit ? API_ROUTES.PRODUCTS.UPDATE(productId) : API_ROUTES.PRODUCTS.CREATE;
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

// ─── Sort Helper ──────────────────────────────────────────────────────────────

function applySort(items, sort) {
    if (!Array.isArray(items)) return [];
    return [...items].sort((a, b) => {
        const da = a.created_at ? new Date(a.created_at).getTime() : 0;
        const db = b.created_at ? new Date(b.created_at).getTime() : 0;
        return sort === 'oldest' ? da - db : db - da;
    });
}

// ─── Page Reload Helper ───────────────────────────────────────────────────────

async function reloadProducts(container) {
    const html = await Products();
    container.innerHTML = html;
    await initProducts(container);
}

const THEAD = `
    <tr>
        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">ID</th>
        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Product</th>
        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Price</th>
        <th class="px-8 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Stock</th>
        <th class="px-8 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Status</th>
        <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Actions</th>
    </tr>`;

// ─── Main View ────────────────────────────────────────────────────────────────

export async function Products() {
    _offset = 0;
    const data = await fetchProducts(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const sorted = applySort(_lastResults, _sort);
    const rows   = sorted.length ? sorted.map(renderRow).join('') : emptyRow('No products yet. Add your first one.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Products',
        'entity-subtitle': `${_lastResults.length > 0 ? _lastResults.length : 'No'} products found`,
    });

    frag.querySelector('#entity-search').placeholder = 'Search by name, category, or SKU…';
    frag.querySelector('#entity-search').value       = _query;
    frag.querySelector('#entity-sort').value         = _sort;
    frag.querySelector('#entity-create-btn').innerHTML = 'Add Product';
    frag.querySelector('#entity-thead').innerHTML      = THEAD;
    frag.querySelector('#entity-tbody').innerHTML      = rows;

    const lmc = frag.querySelector('#entity-load-more-container');
    if (_lastResults.length === DEFAULT_LIMIT) {
        lmc.classList.remove('hidden');
    }

    return frag.firstElementChild.outerHTML;
}

// ─── Listeners / Init ─────────────────────────────────────────────────────────

export function initProducts(container) {
    if (!container) return null;
    const ac     = new AbortController();
    const signal = ac.signal;

    const redrawTable = (items) => {
        const sorted = applySort(items, _sort);
        container.querySelector('#entity-tbody').innerHTML =
            sorted.length ? sorted.map(renderRow).join('') : emptyRow('No products found.');
        const lmc = container.querySelector('#entity-load-more-container');
        if (items.length === DEFAULT_LIMIT) {
            lmc.style.display = 'flex';
            lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
        } else {
            lmc.style.display = 'none';
            lmc.innerHTML = '';
        }
    };

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:products:query', _query); _offset = 0;
        const data = await fetchProducts(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(_lastResults);
    }, 300);

    // Search & Sort
    container.addEventListener('input',  (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });
    container.addEventListener('change', (e) => {
        if (e.target.id === 'entity-sort') {
            _sort = e.target.value; saveState('admin:products:sort', _sort);
            container.querySelector('#entity-tbody').innerHTML = applySort(_lastResults, _sort).map(renderRow).join('');
        }
    }, { signal });

    // Modal Action Redirects (Edit from inside View Modal)
    // We attach this to container using signal so it cleans up.
    container.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.modal-overlay .js-edit');
        if (!editBtn) return;
        
        const id = editBtn.dataset.id;
        closeModal();
        setTimeout(async () => {
            const frag = await renderFormModal(id);
            openStandardModal({ title: 'Edit Product', bodyHtml: frag.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), id, () => reloadProducts(container));
        }, 200);
    }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const p = await fetchProduct(btn.dataset.id);
            openStandardModal({ title: `${escapeHtml(p.name)}`, bodyHtml: renderViewModal(p), size: 'xl' });
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Edit (direct from row)
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-edit');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const frag = await renderFormModal(btn.dataset.id);
            openStandardModal({ title: 'Edit Product', bodyHtml: frag.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadProducts(container));
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
            await apiRequest(API_ROUTES.PRODUCTS.DELETE(id), { method: 'DELETE' });
            reloadProducts(container);
        } catch (err) {
            btn.disabled = false; btn.innerHTML = '🗑';
            alert('Delete failed: ' + err.message);
        }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const frag = await renderFormModal(null);
            openStandardModal({ title: 'Create Product', bodyHtml: frag.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadProducts(container));
        } catch (err) {
            openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchProducts(DEFAULT_LIMIT, _offset, _query);
        const list = Array.isArray(data) ? data : [];
        if (!list.length) { btn.remove(); return; }
        _lastResults = [..._lastResults, ...list];
        container.querySelector('#entity-tbody').insertAdjacentHTML('beforeend', applySort(list, _sort).map(renderRow).join(''));
        if (list.length < DEFAULT_LIMIT) { btn.closest('#entity-load-more-container').style.display = 'none'; }
        else { btn.disabled = false; btn.textContent = 'Load More'; }
    }, { signal });

    // Refresh
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-refresh-btn') return;
        e.target.innerHTML = '⌛'; e.target.disabled = true;
        await reloadProducts(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}

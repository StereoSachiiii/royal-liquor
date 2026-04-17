/**
 * Feedback.js — Modernized Feedback domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = '';
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchFeedbackList(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.FEEDBACK.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch feedback');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[Feedback] Fetch failed', err);
        return [];
    }
}

async function fetchFeedbackItem(id) {
    try {
        const url = API_ROUTES.FEEDBACK.GET(id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch feedback item');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchUsersForDropdown() {
    try {
        const res = await apiRequest('/api/v1/users?limit=200'); 
        return res.success ? (res.data.items || res.data || []) : [];
    } catch (err) { return []; }
}

async function fetchProductsForDropdown() {
    try {
        const res = await apiRequest(API_ROUTES.PRODUCTS.LIST + '?limit=200');
        return res.success ? (res.data || []) : [];
    } catch (err) { return []; }
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(f) {
    const rating = f.rating ?? 0;
    const stars = '⭐'.repeat(rating);
    const comment = f.comment ? (f.comment.length > 40 ? f.comment.substring(0, 37) + '...' : f.comment) : '—';
    const verifiedBadge = f.is_verified_purchase
        ? `<span class="badge badge-active" style="font-size:10px;">Verified</span>`
        : `<span class="badge badge-inactive" style="font-size:10px;">Public</span>`;
    
    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(f.id))}</td>
        <td class="td">
            <div class="text-amber-500 font-bold" style="font-size:13px;letter-spacing:-1px;">${stars}</div>
            <div class="text-[10px] text-slate-400 uppercase font-black">${rating}/5 Score</div>
        </td>
        <td class="td">
            <div class="text-slate-700 italic" style="font-size:12px;line-height:1.2;">"${escapeHtml(comment)}"</div>
        </td>
        <td class="td">
            <div class="font-semibold text-black" style="font-size:12px;">${escapeHtml(f.user_name || 'System')}</div>
            <div class="text-slate-500" style="font-size:10px;">${escapeHtml(f.user_email || '')}</div>
        </td>
        <td class="td">
            <div class="text-black font-medium truncate max-w-[140px]" style="font-size:12px;">${escapeHtml(f.product_name || 'N/A')}</div>
        </td>
        <td class="td">${verifiedBadge}</td>
        <td class="td text-slate-500 font-mono" style="font-size:11px;">${formatDate(f.created_at)}</td>
        <td class="td">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${f.id}" title="View Details">👁</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${f.id}" title="Edit Feedback">✏️</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${f.id}" title="Delete"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="8" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(f) {
    const isActive = f.is_active !== false && f.is_active !== 'f';
    return `
        <div class="flex flex-col" style="gap:24px;">
            <!-- Header -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div class="flex items-center" style="gap:16px;">
                    <div class="flex flex-col items-center justify-center bg-amber-50 border border-amber-200 rounded-2xl p-4 shadow-sm">
                        <div class="text-3xl">⭐</div>
                        <div class="font-black text-black" style="font-size:24px;">${f.rating}/5</div>
                    </div>
                    <div>
                         <h3 class="font-bold text-black" style="font-size:20px;letter-spacing:-0.02em;">Sentiment Log #${f.id}</h3>
                         <div class="flex items-center" style="gap:8px;margin-top:6px;">
                            ${f.is_verified_purchase ? `<span class="badge badge-active" style="font-size:10px;">Verified Purchase</span>` : `<span class="badge badge-inactive" style="font-size:10px;">Public Review</span>`}
                            ${isActive ? `<span class="badge badge-active" style="font-size:10px;">Operative</span>` : `<span class="badge badge-inactive" style="font-size:10px;">Hidden</span>`}
                         </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;">Recorded On</div>
                    <div class="font-bold text-black" style="font-size:14px;">${formatDate(f.created_at)}</div>
                </div>
            </div>

            <!-- Comment Block -->
            <div class="google-card" style="padding:24px;background:var(--slate-50);border-left:4px solid var(--primary);">
                 <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:12px;">Customer Statement</div>
                 <p class="text-black italic" style="font-size:16px;line-height:1.6;color:var(--slate-800);">
                    "${escapeHtml(f.comment || 'No textual narrative provided.')}"
                 </p>
            </div>

            <!-- Context Grids -->
            <div class="grid grid-cols-2 gap-20">
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Consumer Attributes</h4>
                    <div class="flex flex-col" style="gap:8px;">
                        <div class="flex justify-between" style="font-size:13px;"><span class="text-slate-500">Identity</span><span class="font-bold text-black">${escapeHtml(f.user_name || '-')}</span></div>
                        <div class="flex justify-between" style="font-size:13px;"><span class="text-slate-500">Relay Email</span><span class="text-indigo-600 font-medium underline">${escapeHtml(f.user_email || '-')}</span></div>
                        <div class="flex justify-between" style="font-size:13px;"><span class="text-slate-500">User Trace ID</span><span class="font-mono">#${f.user_id}</span></div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Product Target</h4>
                    <div class="flex flex-col" style="gap:8px;">
                        <div class="flex justify-between" style="font-size:13px;"><span class="text-slate-500">Catalog Name</span><span class="font-bold text-black">${escapeHtml(f.product_name || '-')}</span></div>
                        <div class="flex justify-between" style="font-size:13px;"><span class="text-slate-500">Slug Identity</span><span class="font-mono text-slate-500">${escapeHtml(f.product_slug || '-')}</span></div>
                        <div class="flex justify-between" style="font-size:13px;"><span class="text-slate-500">Product ID</span><span class="font-mono">#${f.product_id}</span></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${f.id}" style="padding:0 32px;">✏️ Edit Impression</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(feedbackId = null) {
    const isEdit = feedbackId !== null;
    let [f, users, products] = [{}, [], []];
    if (isEdit) {
        [f, users, products] = await Promise.all([fetchFeedbackItem(feedbackId), fetchUsersForDropdown(), fetchProductsForDropdown()]);
    } else {
        [users, products] = await Promise.all([fetchUsersForDropdown(), fetchProductsForDropdown()]);
    }

    const frag = getTemplate('tpl-feedback-form', {
        id:               f.id || '',
        comment:          escapeHtml(f.comment || ''),
        verified_checked: f.is_verified_purchase ? 'checked' : '',
        active_checked:   f.is_active !== false ? 'checked' : '',
        submit_text:      isEdit ? 'Save Changes' : 'Create Sentiment'
    });

    const uSelect = frag.querySelector('#fdb-user-select');
    const pSelect = frag.querySelector('#fdb-product-select');
    if (uSelect) uSelect.innerHTML = '<option value="">-- Select Actor --</option>' + users.map(u => `<option value="${u.id}" ${parseInt(f.user_id) === u.id ? 'selected' : ''}>${escapeHtml(u.name || u.username)} (${escapeHtml(u.email || 'N/A')})</option>`).join('');
    if (pSelect) pSelect.innerHTML = '<option value="">-- Select Target Product --</option>' + products.map(p => `<option value="${p.id}" ${parseInt(f.product_id) === p.id ? 'selected' : ''}>${escapeHtml(p.name)} (${p.sku || 'N/A'})</option>`).join('');

    if (isEdit) {
        const rSelect = frag.querySelector('select[name="rating"]');
        if (rSelect) rSelect.value = f.rating || 5;
        const footer = frag.querySelector('.fdb-form-footer');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button'; del.className = 'btn btn-outline text-danger'; del.style.marginRight = 'auto';
            del.id = 'fdb-delete-btn'; del.dataset.id = feedbackId; del.innerHTML = '🗑️ Purge Log';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, feedbackId, onSuccess) {
    const isEdit = feedbackId !== null;
    const form   = modalRoot.querySelector('#fdb-form');
    const cancel = modalRoot.querySelector('#fdb-cancel');
    const delBtn = modalRoot.querySelector('#fdb-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Purge?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Purge Log'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Purging…';
            try {
                await apiRequest(API_ROUTES.FEEDBACK.DELETE(feedbackId), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Purge Log'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Saving…' : 'Adding…';
        try {
            const data = getFormData(form);
            const payload = { user_id: parseInt(data.user_id), product_id: parseInt(data.product_id), rating: parseInt(data.rating), comment: data.comment || null, is_verified_purchase: data.is_verified_purchase !== undefined, is_active: data.is_active !== undefined };
            const url = isEdit ? API_ROUTES.FEEDBACK.UPDATE(feedbackId) : API_ROUTES.FEEDBACK.CREATE;
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

async function reloadFeedback(container) {
    const html = await Feedback();
    container.innerHTML = html;
    await initFeedback(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No feedback found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="width:100px;">Score</th>
    <th class="th" style="min-width:200px;">Sentiment Statement</th>
    <th class="th" style="min-width:160px;">Consumer Profile</th>
    <th class="th" style="min-width:140px;">Product Target</th>
    <th class="th" style="width:100px;">Context</th>
    <th class="th" style="width:130px;">Established</th>
    <th class="th" style="width:160px;">Actions</th>
</tr>`;

export async function Feedback() {
    _offset = 0;
    const data = await fetchFeedbackList(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No feedback found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Customer Feedback',
        'entity-subtitle': 'View and manage customer reviews and ratings',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by comment, user, or product…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ New Impression';
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

export function initFeedback(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; _offset = 0;
        const data = await fetchFeedbackList(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View logic
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const fb = await fetchFeedback(btn.dataset.id);
            openStandardModal({ title: 'Feedback Details', bodyHtml: renderViewModal(fb), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Modify Sentiment Protocol', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadFeedback(container));
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
            openStandardModal({ title: 'Modify Sentiment Protocol', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadFeedback(container));
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
            await apiRequest(API_ROUTES.FEEDBACK.DELETE(id), { method: 'DELETE' });
            reloadFeedback(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Deletion failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Synthesize New Impression', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadFeedback(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchFeedbackList(DEFAULT_LIMIT, _offset, _query);
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
        await reloadFeedback(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
/**
 * FlavourProfiles.js — Modernized Flavor Profiles domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:flav:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchFlavorProfiles(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.FLAVOR_PROFILES.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch profiles');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[FlavorProfiles] Fetch failed', err);
        return [];
    }
}

async function fetchFlavorProfile(id) {
    try {
        const url = API_ROUTES.FLAVOR_PROFILES.GET(id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch profile');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchProductsForDropdown() {
    try {
        const res = await apiRequest(API_ROUTES.PRODUCTS.LIST + '?limit=100');
        return res.success ? (res.data || []) : [];
    } catch (err) { return []; }
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderBarMini(val, color) {
    const pct = (val / 10) * 100;
    return `<div style="width:40px;height:4px;background:var(--slate-100);border-radius:2px;overflow:hidden;border:1px solid var(--slate-200);">
        <div style="width:${pct}%;height:100%;background:${color};"></div>
    </div>`;
}

function renderRow(p) {
    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(p.product_id))}</td>
        <td class="td">
            <div class="flex items-center" style="gap:10px;">
                ${p.product_image_url ? `<img src="${p.product_image_url}" class="thumb-sm rounded-lg border shadow-xs" style="width:32px;height:32px;object-fit:cover;">` : `<div class="thumb-sm rounded-lg bg-slate-100 flex items-center justify-center text-lg border" style="width:32px;height:32px;">🍶</div>`}
                <div>
                    <div class="font-bold text-black" style="font-size:13px;">${escapeHtml(p.product_name || 'Individual Profile')}</div>
                    <div class="text-slate-400 font-mono" style="font-size:10px;">${escapeHtml(p.product_slug)}</div>
                </div>
            </div>
        </td>
        <td class="td"><div class="flex flex-col items-center gap-1">${renderBarMini(p.sweetness || 0, '#f59e0b')}<span class="font-mono text-[9px] text-slate-400">${p.sweetness || 0}/10</span></div></td>
        <td class="td"><div class="flex flex-col items-center gap-1">${renderBarMini(p.bitterness || 0, '#8b5cf6')}<span class="font-mono text-[9px] text-slate-400">${p.bitterness || 0}/10</span></div></td>
        <td class="td"><div class="flex flex-col items-center gap-1">${renderBarMini(p.strength || 0, '#ef4444')}<span class="font-mono text-[9px] text-slate-400">${p.strength || 0}/10</span></div></td>
        <td class="td"><div class="flex flex-col items-center gap-1">${renderBarMini(p.fruitiness || 0, '#10b981')}<span class="font-mono text-[9px] text-slate-400">${p.fruitiness || 0}/10</span></div></td>
        <td class="td"><div class="flex flex-col items-center gap-1">${renderBarMini(p.spiciness || 0, '#f43f5e')}<span class="font-mono text-[9px] text-slate-400">${p.spiciness || 0}/10</span></div></td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${p.product_id}" title="View Details">👁</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${p.product_id}" title="Edit Profile">✏️</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${p.product_id}" title="Purge"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="8" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(p) {
    const renderBar = (label, value, color) => {
        const pct = (value / 10) * 100;
        return `
            <div style="margin-bottom:16px;">
                <div class="flex justify-between items-baseline" style="margin-bottom:6px;">
                    <span class="text-slate-500 font-bold uppercase" style="font-size:10px;letter-spacing:0.05em;">${escapeHtml(label)}</span>
                    <span class="font-black" style="color:${color};font-size:16px;font-family:monospace;">${value}/10</span>
                </div>
                <div style="height:8px;background:var(--slate-100);border-radius:4px;overflow:hidden;border:1px solid var(--slate-200);">
                    <div style="width:${pct}%;height:100%;background:${color};border-radius:4px;transition:width 0.6s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                </div>
            </div>`;
    };

    const tags = Array.isArray(p.tags) ? p.tags : [];
    const tagsHtml = tags.map(t => `<span class="badge" style="background:var(--slate-50);border:1px solid var(--slate-200);color:var(--slate-600);font-size:10px;padding:4px 10px;">#${escapeHtml(t)}</span>`).join('');

    return `
        <div class="flex flex-col" style="gap:24px;">
            <!-- Header section -->
            <div class="flex items-center" style="gap:20px;padding-bottom:20px;border-bottom:1px solid var(--slate-100);">
                ${p.product_image_url ? `<img src="${p.product_image_url}" class="thumb-xl rounded-2xl border shadow-sm" style="width:100px;height:100px;object-fit:cover;">` : `<div class="thumb-xl rounded-2xl bg-slate-50 flex items-center justify-center text-4xl border" style="width:100px;height:100px;">🍶</div>`}
                <div style="flex:1;">
                    <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:4px;">Sensory Node Strategy</div>
                    <h3 class="font-bold text-black" style="font-size:26px;letter-spacing:-0.03em;">${escapeHtml(p.product_name)}</h3>
                    <div class="flex items-center" style="gap:10px;margin-top:4px;">
                        <span class="font-mono text-slate-500" style="font-size:12px;">Trace ID: #${p.product_id}</span>
                        <span class="text-slate-300">•</span>
                        <span class="font-mono text-indigo-500" style="font-size:12px;">${escapeHtml(p.product_slug)}</span>
                    </div>
                </div>
                <div class="text-right">
                     <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:4px;">Profile Pulse</div>
                     <span class="badge badge-active font-black tracking-widest" style="font-size:11px;padding:6px 16px;">VERIFIED</span>
                </div>
            </div>

            <div class="flex" style="gap:24px;">
                <!-- Left: Matrix -->
                <div style="flex:1.5;">
                    <div class="google-card" style="padding:24px;background:var(--slate-50);">
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:20px;border-bottom:1px solid var(--slate-200);padding-bottom:4px;">Organoleptic Matrix</h4>
                         <div style="display:grid;grid-template-columns:1fr 1fr;gap:x 40px;">
                            ${renderBar('Intensity Strategy', p.strength || 0, '#ef4444')}
                            ${renderBar('Glucose Index', p.sweetness || 0, '#f59e0b')}
                            ${renderBar('Tannic Bitterness', p.bitterness || 0, '#8b5cf6')}
                            ${renderBar('Phenolic Smokiness', p.smokiness || 0, '#64748b')}
                            ${renderBar('Esther Fruitiness', p.fruitiness || 0, '#10b981')}
                            ${renderBar('Capsaicin Heat', p.spiciness || 0, '#f43f5e')}
                         </div>
                    </div>
                </div>

                <!-- Right: Meta -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:20px;">
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Curated Taxonomy</h4>
                         <div class="flex flex-wrap" style="gap:6px;">
                            ${tagsHtml || '<span class="text-xs text-slate-400 italic">No taxonomic markers assigned.</span>'}
                         </div>
                    </div>

                    <div style="padding:16px;background:var(--slate-50);border:1px solid var(--slate-200);border-radius:12px;">
                        <h4 class="text-indigo-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:10px;">Forensic Guidance</h4>
                        <p class="text-[11px] text-slate-600 leading-relaxed italic">
                            Sensory profiles are calibrated against a 1-10 intensity scale. Modifications to this node will propagate to discovery filters and recommendation engines.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${p.product_id}" style="padding:0 32px;">✏️ Modify Matrix</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

function renderSlider(label, name, value = 5, color) {
    return `
        <div style="background:var(--slate-50);padding:14px;border-radius:12px;border:1px solid var(--slate-100);margin-bottom:12px;">
            <div class="flex justify-between items-center" style="margin-bottom:10px;">
                <label class="text-slate-600 font-bold uppercase" style="font-size:10px;letter-spacing:0.05em;">${escapeHtml(label)}</label>
                <span class="font-black" style="color:${color};font-size:16px;font-family:monospace;">${value}</span>
            </div>
            <input type="range" name="${name}" min="0" max="10" value="${value}" 
                style="width:100%;cursor:pointer;accent-color:${color};height:4px;">
            <div class="flex justify-between" style="margin-top:6px;text-[9px] text-slate-400 font-bold uppercase">
                <span>Passive</span>
                <span>Profound</span>
            </div>
        </div>`;
}

async function renderFormModal(productId = null) {
    const isEdit = productId !== null;
    let p = {};
    let products = [];
    if (isEdit) p = await fetchFlavorProfile(productId);
    else products = await fetchProductsForDropdown();

    const tagsValue = Array.isArray(p.tags) ? p.tags.join(', ') : '';
    const frag = getTemplate('tpl-flavor-profile-form', {
        id:                      p.product_id || '',
        name:                    escapeHtml(p.product_name || ''),
        slug:                    escapeHtml(p.product_slug || ''),
        image_url:               escapeHtml(p.product_image_url || ''),
        image_display:           p.product_image_url ? 'block' : 'none',
        product_section_display: isEdit ? 'none' : 'block',
        product_disabled:        isEdit ? 'disabled' : '',
        edit_header_display:     isEdit ? 'flex' : 'none',
        tags_value:              escapeHtml(tagsValue),
        submit_text:             isEdit ? 'Save Matrix Changes' : 'Execute Initial Calibration'
    });

    const sliderContainer = frag.querySelector('#flap-sliders-container');
    if (sliderContainer) {
        sliderContainer.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
                ${renderSlider('Strength Intensity', 'strength', p.strength ?? 5, '#ef4444')}
                ${renderSlider('Glucose Sweetness', 'sweetness', p.sweetness ?? 5, '#f59e0b')}
                ${renderSlider('Tannic Bitterness', 'bitterness', p.bitterness ?? 5, '#8b5cf6')}
                ${renderSlider('Phenolic Smokiness', 'smokiness', p.smokiness ?? 5, '#64748b')}
                ${renderSlider('Esther Fruitiness', 'fruitiness', p.fruitiness ?? 5, '#10b981')}
                ${renderSlider('Capsaicin Spiciness', 'spiciness', p.spiciness ?? 5, '#f43f5e')}
            </div>`;
    }

    if (!isEdit) {
        const select = frag.querySelector('#flap-product-select');
        if (select) select.innerHTML = '<option value="">-- Identify Supply Node Target --</option>' + products.map(pr => `<option value="${pr.id}">${escapeHtml(pr.name)} (${pr.slug || 'no-slug'})</option>`).join('');
    }

    if (isEdit) {
        const footer = frag.querySelector('.flap-form-footer') || frag.querySelector('.flex.justify-end.gap-3.pt-6');
        if (footer) {
            const del = document.createElement('button');
            del.type = 'button'; del.className = 'btn btn-outline text-danger'; del.style.marginRight = 'auto';
            del.id = 'flap-delete-btn'; del.dataset.id = productId; del.innerHTML = '🗑️ Purge Matrix';
            footer.prepend(del);
        }
    }
    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, productId, onSuccess) {
    const isEdit = productId !== null;
    const form   = modalRoot.querySelector('#flap-form');
    const cancel = modalRoot.querySelector('#flap-cancel');
    const delBtn = modalRoot.querySelector('#flap-delete-btn');

    if (!form) return;
    form.querySelectorAll('input[type="range"]').forEach(range => {
        range.addEventListener('input', (e) => {
            const valSpan = e.target.parentElement.querySelector('.font-black');
            if (valSpan) valSpan.textContent = e.target.value;
        });
    });
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Purge?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Purge Matrix'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Purging…';
            try {
                await apiRequest(API_ROUTES.FLAVOR_PROFILES.DELETE(productId), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Purge Matrix'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = isEdit ? 'Syncing Matrix…' : 'Executing Calibration…';
        try {
            const data = getFormData(form);
            const targetId = isEdit ? productId : data.product_id;
            if (!targetId) throw new Error('Specify target supply node.');
            const tags = (data.tags || '').split(',').map(t => t.trim()).filter(Boolean);
            const payload = { product_id: parseInt(targetId), sweetness: parseInt(data.sweetness ?? 5), bitterness: parseInt(data.bitterness ?? 5), strength: parseInt(data.strength ?? 5), smokiness: parseInt(data.smokiness ?? 5), fruitiness: parseInt(data.fruitiness ?? 5), spiciness: parseInt(data.spiciness ?? 5), tags: tags };
            const url = isEdit ? API_ROUTES.FLAVOR_PROFILES.UPDATE(targetId) : API_ROUTES.FLAVOR_PROFILES.CREATE;
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

async function reloadFlavorProfiles(container) {
    const html = await FlavourProfiles();
    container.innerHTML = html;
    await initFlavourProfiles(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No flavour profiles found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:180px;">Supply Node / Identity</th>
    <th class="th" style="width:70px;text-align:center;">Sweet</th>
    <th class="th" style="width:70px;text-align:center;">Bitter</th>
    <th class="th" style="width:70px;text-align:center;">Intense</th>
    <th class="th" style="width:70px;text-align:center;">Fruity</th>
    <th class="th" style="width:70px;text-align:center;">Spicy</th>
    <th class="th" style="width:160px;">Actions</th>
</tr>`;

export async function FlavourProfiles() {
    _offset = 0;
    const data = await fetchFlavorProfiles(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No flavour profiles found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Flavour Profiles',
        'entity-subtitle': 'Manage tasting notes and flavour characteristics for products',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by flavour profile name or slug…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').innerHTML = '➕ Create Profile';
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

export function initFlavourProfiles(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:flav:query', _query); _offset = 0;
        const data = await fetchFlavorProfiles(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const p = await fetchFlavorProfile(btn.dataset.id);
            openStandardModal({ title: 'Flavor Profile Details', bodyHtml: renderViewModal(p), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Modify Sensory Matrix', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadFlavorProfiles(container));
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
            openStandardModal({ title: 'Modify Sensory Matrix', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadFlavorProfiles(container));
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
            await apiRequest(API_ROUTES.FLAVOR_PROFILES.DELETE(id), { method: 'DELETE' });
            reloadFlavorProfiles(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Purge failed: ' + err.message); }
    }, { signal });

    // Create
    container.addEventListener('click', async (e) => {
        if (!e.target.closest('#entity-create-btn')) return;
        try {
            const f = await renderFormModal(null);
            openStandardModal({ title: 'Initial Sensory Calibration', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), null, () => reloadFlavorProfiles(container));
        } catch (err) {
             openStandardModal({ title: 'Error', bodyHtml: `<p class="text-danger" style="padding:12px;">${escapeHtml(err.message)}</p>` });
        }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchFlavorProfiles(DEFAULT_LIMIT, _offset, _query);
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
        await reloadFlavorProfiles(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}
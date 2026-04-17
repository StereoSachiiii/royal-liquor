/**
 * UserPreferences.js — Modernized User Preferences domain module.
 * Uses dashboard-tailwind.css classes throughout.
 * Rows rendered as inline HTML strings for correct table parsing.
 */

import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal, getTemplate, getFormData } from '../../utils.js';

const DEFAULT_LIMIT = 20;
let _offset = 0;
let _query  = getState('admin:upref:query', '');
let _lastResults = [];

// ─── API ─────────────────────────────────────────────────────────────────────

async function fetchPreferences(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = API_ROUTES.USER_PREFERENCES.LIST + buildQueryString({
            limit, offset,
            ...(query ? { search: query } : {})
        });
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch preferences');
        return res.data?.items || (Array.isArray(res.data) ? res.data : []);
    } catch (err) {
        console.error('[UserPreferences] Fetch failed', err);
        return [];
    }
}

async function fetchPreference(id) {
    try {
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('user_preferences', id);
        const res = await apiRequest(url);
        if (!res.success) throw new Error(res.message || 'Failed to fetch preference details');
        return res.data;
    } catch (err) { throw err; }
}

async function fetchCategories() {
    try { const res = await apiRequest(API_ROUTES.CATEGORIES.LIST + '?limit=100'); return res.success ? (res.data || []) : []; }
    catch (err) { return []; }
}

// ─── Utils ───────────────────────────────────────────────────────────────────

function parsePgArray(val) {
    if (!val) return [];
    if (Array.isArray(val)) return val;
    if (typeof val === 'string') return val.replace(/[{}]/g, '').split(',').filter(s => s).map(Number);
    return [];
}

// ─── Row Renderer (inline HTML for correct parsing) ───────────────────────────

function renderRow(p) {
    const sw = p.preferred_sweetness ?? 0;
    const bi = p.preferred_bitterness ?? 0;
    const st = p.preferred_strength ?? 0;
    const sm = p.preferred_smokiness ?? 0;
    const favsCount = parsePgArray(p.favorite_categories).length;

    return `<tr class="tr">
        <td class="td font-mono text-slate-400" style="font-size:11px;">#${escapeHtml(String(p.id))}</td>
        <td class="td font-bold text-black" style="font-size:13px;">${escapeHtml(p.user_name || 'Individual Profile')}</td>
        <td class="td">
            <div class="flex items-center" style="gap:8px;">
                <div class="flex flex-col items-center"><div style="width:12px;height:12px;border-radius:3px;background:#f59e0b;"></div><span style="font-size:9px;" class="font-mono text-slate-400">${sw}</span></div>
                <div class="flex flex-col items-center"><div style="width:12px;height:12px;border-radius:3px;background:#8b5cf6;"></div><span style="font-size:9px;" class="font-mono text-slate-400">${bi}</span></div>
                <div class="flex flex-col items-center"><div style="width:12px;height:12px;border-radius:3px;background:#ef4444;"></div><span style="font-size:9px;" class="font-mono text-slate-400">${st}</span></div>
                <div class="flex flex-col items-center"><div style="width:12px;height:12px;border-radius:3px;background:#64748b;"></div><span style="font-size:9px;" class="font-mono text-slate-400">${sm}</span></div>
            </div>
        </td>
        <td class="td">
            <span class="badge badge-secondary" style="font-size:10px;">${favsCount} Node Favorites</span>
        </td>
        <td class="td text-slate-500 font-mono" style="font-size:11px;">${formatDate(p.created_at)}</td>
        <td class="td" style="white-space:nowrap;">
            <div class="flex items-center" style="gap:6px;">
                <button class="btn btn-outline btn-sm js-view" data-id="${p.id}" title="View Details">👁 View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="${p.id}" title="Edit Settings">✏️ Edit</button>
                <button class="btn btn-outline btn-sm js-delete" data-id="${p.id}" title="Reset"
                    style="color:var(--danger);border-color:var(--danger);">🗑</button>
            </div>
        </td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr class="tr"><td colspan="6" class="td text-center text-slate-500" style="padding:48px;">${escapeHtml(msg)}</td></tr>`;
}

// ─── View Modal ───────────────────────────────────────────────────────────────

function renderViewModal(p) {
    const renderBar = (label, value, color) => {
        const pct = (value / 10) * 100;
        return `
            <div style="margin-bottom:12px;">
                <div class="flex justify-between items-baseline" style="margin-bottom:4px;">
                    <span class="text-slate-500 font-bold uppercase" style="font-size:9px;letter-spacing:0.05em;">${escapeHtml(label)}</span>
                    <span class="font-black" style="color:${color};font-size:14px;font-family:monospace;">${value}/10</span>
                </div>
                <div style="height:6px;background:var(--slate-100);border-radius:3px;overflow:hidden;border:1px solid var(--slate-200);">
                    <div style="width:${pct}%;height:100%;background:${color};"></div>
                </div>
            </div>`;
    };

    const favs = parsePgArray(p.favorite_categories);

    return `
        <div class="flex flex-col" style="gap:24px;">
            <!-- Header section -->
            <div class="flex items-center justify-between" style="padding-bottom:16px;border-bottom:1px solid var(--slate-100);">
                <div class="flex items-center" style="gap:16px;">
                    <div class="thumb-lg rounded-2xl bg-slate-50 border flex items-center justify-center text-2xl shadow-inner">🧬</div>
                    <div>
                         <h3 class="font-bold text-black" style="font-size:22px;letter-spacing:-0.02em;">Palate Intelligence Forensic #${p.id}</h3>
                         <p class="text-sm text-slate-500">Account Identity: <span class="font-bold text-black">${escapeHtml(p.user_name)}</span> (#${p.user_id})</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-slate-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:4px;">Profile established</div>
                    <span class="text-slate-500 font-mono" style="font-size:11px;">${formatDate(p.created_at)}</span>
                </div>
            </div>

            <div class="flex" style="gap:24px;">
                <!-- Palate Pulse -->
                <div style="flex:1.4;">
                    <div class="google-card" style="padding:24px;background:var(--slate-50);">
                         <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:20px;border-bottom:1px solid var(--slate-200);padding-bottom:4px;">Palate Bias Matrix</h4>
                         <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 32px;">
                            ${renderBar('Sweetness Threshold', p.preferred_sweetness ?? 5, '#f59e0b')}
                            ${renderBar('Bitterness Index', p.preferred_bitterness ?? 5, '#8b5cf6')}
                            ${renderBar('Intensity Gradient', p.preferred_strength ?? 5, '#ef4444')}
                            ${renderBar('Phenolic Smokiness', p.preferred_smokiness ?? 5, '#64748b')}
                            ${renderBar('Esther Fruitiness', p.preferred_fruitiness ?? 5, '#10b981')}
                            ${renderBar('Capsaicin Tolerance', p.preferred_spiciness ?? 5, '#f43f5e')}
                         </div>
                    </div>
                </div>

                <!-- Right: Taxonomy -->
                <div style="flex:1;display:flex;flex-direction:column;gap:16px;">
                    <div class="google-card" style="padding:20px;">
                        <h4 class="text-slate-400 font-bold uppercase" style="font-size:10px;letter-spacing:0.1em;margin-bottom:12px;border-bottom:1px solid var(--slate-100);padding-bottom:4px;">Taxonomic Interests</h4>
                        <div class="flex flex-wrap" style="gap:6px;">
                            ${favs.length ? favs.map(c => `<span class="badge" style="background:var(--slate-50);border:1px solid var(--slate-200);color:var(--slate-600);font-size:10px;">NODE-CAT #${c}</span>`).join('') : '<span class="text-xs text-slate-400 italic">No specific nodes favorited.</span>'}
                        </div>
                    </div>

                    <div style="background:var(--slate-50);padding:16px;border-radius:12px;border:1px solid var(--slate-200);">
                        <h4 class="text-indigo-400 font-bold uppercase" style="font-size:9px;letter-spacing:0.1em;margin-bottom:8px;">Forensic Guidance</h4>
                        <p class="text-[11px] text-slate-600 leading-relaxed italic">
                            Palate profiles are dynamically calculated based on consumer interaction and manual calibration. This logic governs automated recommendation protocols.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end" style="padding-top:12px;border-top:1px solid var(--slate-100);gap:8px;">
                <button class="btn btn-primary js-edit" data-id="${p.id}" style="padding:0 32px;">✏️ Modify DNA Settings</button>
            </div>
        </div>`;
}

// ─── Form Builder ─────────────────────────────────────────────────────────────

async function renderFormModal(id) {
    if (!id) throw new Error('UserPreferences: renderFormModal requires a valid record ID.');

    const [p, cats] = await Promise.all([fetchPreference(id), fetchCategories()]);
    const selectedCats = parsePgArray(p.favorite_categories);

    const frag = getTemplate('tpl-user-preference-form', {
        user_name: escapeHtml(p.user_name || 'Individual Profile')
    });

    const flavorMappings = {
        'sweet': 'sweetness', 'bitter': 'bitterness', 'strength': 'strength', 
        'smoke': 'smokiness', 'fruit': 'fruitiness', 'spice': 'spiciness'
    };
    
    Object.keys(flavorMappings).forEach(k => {
        const sel = frag.querySelector(`#upref-${k}`);
        if (sel) {
            sel.innerHTML = Array.from({ length: 11 }, (_, i) => `<option value="${i}">${i}</option>`).join('');
            sel.value = p[`preferred_${flavorMappings[k]}`] ?? 5;
        }
    });

    const area = frag.querySelector('#upref-categories-area');
    if (area) {
        area.innerHTML = cats.map(c => `
            <label class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100 text-xs cursor-pointer hover:bg-slate-100 transition-colors">
                <input type="checkbox" name="favorite_categories" value="${c.id}" ${selectedCats.includes(c.id) ? 'checked' : ''} class="w-3.5 h-3.5">
                <span class="font-bold text-slate-700">${escapeHtml(c.name)}</span>
            </label>
        `).join('');
    }

    const footer = frag.querySelector('.flex.justify-end.gap-3.pt-6');
    if (footer) {
        const del = document.createElement('button');
        del.type = 'button'; del.className = 'btn btn-outline text-danger'; del.style.marginRight = 'auto';
        del.id = 'upref-delete-btn'; del.dataset.id = id; del.innerHTML = '🗑️ Reset Identity';
        footer.prepend(del);
    }

    return frag;
}

// ─── Form Handlers ────────────────────────────────────────────────────────────

function initFormHandlers(modalRoot, id, onSuccess) {
    const form   = modalRoot.querySelector('#upref-form');
    const cancel = modalRoot.querySelector('#upref-cancel');
    const delBtn = modalRoot.querySelector('#upref-delete-btn');

    if (!form) return;
    if (cancel) cancel.addEventListener('click', () => closeModal());

    if (delBtn) {
        delBtn.addEventListener('click', async () => {
            if (!delBtn.dataset.confirmed) {
                delBtn.dataset.confirmed = '1'; delBtn.innerHTML = '⚠️ Confirm Reset?';
                delBtn.classList.add('btn-warning');
                setTimeout(() => { if (delBtn.isConnected) { delete delBtn.dataset.confirmed; delBtn.innerHTML = '🗑️ Reset Identity'; delBtn.classList.remove('btn-warning'); }}, 3000);
                return;
            }
            delBtn.disabled = true; delBtn.innerHTML = 'Resetting…';
            try {
                await apiRequest(API_ROUTES.USER_PREFERENCES.DELETE(id), { method: 'DELETE' });
                closeModal(); onSuccess?.();
            } catch (err) { showFormError(form, err.message); delBtn.disabled = false; delBtn.innerHTML = '🗑️ Reset Identity'; delete delBtn.dataset.confirmed; }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const orig = submit.innerHTML;
        submit.disabled = true; submit.innerHTML = 'Syncing DNA…';
        try {
            const data = getFormData(form);
            const cats = Array.from(form.querySelectorAll('input[name="favorite_categories"]:checked')).map(cb => parseInt(cb.value));
            const payload = {
                preferred_sweetness:  parseInt(data.preferred_sweetness),
                preferred_bitterness: parseInt(data.preferred_bitterness),
                preferred_strength:   parseInt(data.preferred_strength),
                preferred_smokiness:  parseInt(data.preferred_smokiness),
                preferred_fruitiness: parseInt(data.preferred_fruitiness),
                preferred_spiciness:  parseInt(data.preferred_spiciness),
                favorite_categories:  cats
            };
            await apiRequest(API_ROUTES.USER_PREFERENCES.UPDATE(id), { method: 'PUT', body: payload });
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

async function reloadPreferences(container) {
    const html = await UserPreferences();
    container.innerHTML = html;
    await initUserPreferences(container);
}

function redrawTable(container, list) {
    container.querySelector('#entity-tbody').innerHTML =
        list.length ? list.map(renderRow).join('') : emptyRow('No user preferences found.');
    const lmc = container.querySelector('#entity-load-more-container');
    if (list.length === DEFAULT_LIMIT) {
        lmc.style.display = 'flex';
        lmc.innerHTML = `<button id="entity-load-more-btn" class="btn btn-outline" style="padding:0 48px;">Load More</button>`;
    } else { lmc.style.display = 'none'; lmc.innerHTML = ''; }
}

// ─── Main View ────────────────────────────────────────────────────────────────

const THEAD = `<tr class="tr">
    <th class="th" style="width:50px;">ID</th>
    <th class="th" style="min-width:200px;">Account Target / Palate Owner</th>
    <th class="th" style="width:140px;">Palate DNA (Sw/Bi/St/Sm)</th>
    <th class="th" style="width:120px;">Taxonomy</th>
    <th class="th" style="width:140px;">Established</th>
    <th class="th" style="width:180px;">Actions</th>
</tr>`;

export async function UserPreferences() {
    _offset = 0;
    const data = await fetchPreferences(DEFAULT_LIMIT, 0, _query);
    _lastResults = Array.isArray(data) ? data : [];
    const rows = _lastResults.length ? _lastResults.map(renderRow).join('') : emptyRow('No user preferences found.');

    const frag = getTemplate('tpl-admin-entity', {
        'entity-title':    'Taste Preferences',
        'entity-subtitle': 'Manage individual user taste profiles and product interests',
    });

    frag.querySelector('#entity-search').placeholder = 'Search by user name or email…';
    frag.querySelector('#entity-search').value = _query;
    frag.querySelector('#entity-sort').style.display = 'none';
    frag.querySelector('#entity-create-btn').style.display = 'none';
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

export function initUserPreferences(container) {
    if (!container) return null;
    const ac = new AbortController();
    const signal = ac.signal;

    const performSearch = debounce(async (q) => {
        _query = q; saveState('admin:upref:query', _query); _offset = 0;
        const data = await fetchPreferences(DEFAULT_LIMIT, 0, _query);
        _lastResults = Array.isArray(data) ? data : [];
        redrawTable(container, _lastResults);
    }, 300);

    container.addEventListener('input', (e) => { if (e.target.id === 'entity-search') performSearch(e.target.value.trim()); }, { signal });

    // View
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-view');
        if (!btn || e.target.closest('.modal-overlay')) return;
        try {
            const p = await fetchPreference(btn.dataset.id);
            openStandardModal({ title: 'User Preference Details', bodyHtml: renderViewModal(p), size: 'xl' });
            const overlay = document.querySelector('.modal-overlay:last-child');
            overlay?.addEventListener('click', async (me) => {
                const editBtn = me.target.closest('.js-edit');
                if (editBtn) {
                    closeModal();
                    setTimeout(async () => {
                        const f = await renderFormModal(editBtn.dataset.id);
                        openStandardModal({ title: 'Edit User Preference', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
                        initFormHandlers(document.querySelector('.modal-overlay:last-child'), editBtn.dataset.id, () => reloadPreferences(container));
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
            openStandardModal({ title: 'Edit Preferences', bodyHtml: f.firstElementChild.outerHTML, size: 'xl' });
            initFormHandlers(document.querySelector('.modal-overlay:last-child'), btn.dataset.id, () => reloadPreferences(container));
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
            await apiRequest(API_ROUTES.USER_PREFERENCES.DELETE(id), { method: 'DELETE' });
            reloadPreferences(container);
        } catch (err) { btn.disabled = false; btn.innerHTML = '🗑'; alert('Reset failed: ' + err.message); }
    }, { signal });

    // Load More
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'entity-load-more-btn') return;
        const btn = e.target; btn.disabled = true; btn.textContent = 'Loading…';
        _offset += DEFAULT_LIMIT;
        const data = await fetchPreferences(DEFAULT_LIMIT, _offset, _query);
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
        await reloadPreferences(container);
    }, { signal });

    return { cleanup: () => ac.abort() };
}

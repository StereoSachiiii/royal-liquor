export function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function debounce(fn, wait = 300) {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), wait);
    };
}

export function saveState(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.warn('Failed to save state', key, e);
    }
}

export function getState(key, fallback = null) {
    try {
        const raw = localStorage.getItem(key);
        return raw ? JSON.parse(raw) : fallback;
    } catch (e) {
        console.warn('Failed to read state', key, e);
        return fallback;
    }
}

function getModalElements() {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    return { modal, modalBody };
}

export function openStandardModal({ title, bodyHtml, footerHtml = '', size = 'lg' }) {
    // Synchronously remove any existing modal to prevent flickering
    const existing = document.querySelector('.modal-overlay');
    if (existing) {
        existing.remove();
        delete window._activeModalClose;
    }
    
    const template = getTemplate('tpl-admin-modal', {
        title: escapeHtml(title || ''),
        submit_text: ''
    });

    if (!template) return;

    const modalOverlay = template.querySelector('.modal-overlay');
    const modalContainer = template.querySelector('#modal-container');
    const modalBody = template.querySelector('#modal-body');
    const modalFooter = template.querySelector('#modal-footer');
    const closeBtn = template.querySelector('#modal-close');

    // Set size
    if (size === 'xl') modalContainer.style.maxWidth = '1200px';
    if (size === 'lg') modalContainer.style.maxWidth = '900px';
    if (size === 'md') modalContainer.style.maxWidth = '600px';
    if (size === 'sm') modalContainer.style.maxWidth = '400px';

    modalBody.innerHTML = bodyHtml || '';
    if (footerHtml) {
        modalFooter.innerHTML = footerHtml;
    } else {
        modalFooter.classList.add('hidden');
    }

    document.body.appendChild(template);
    
    // Show modal with slight delay for transition
    setTimeout(() => {
        if (modalOverlay.isConnected) {
            modalOverlay.classList.add('active');
        }
    }, 10);

    const closeModalFn = () => {
        modalOverlay.classList.remove('active');
        setTimeout(() => {
            if (modalOverlay.isConnected) {
                modalOverlay.remove();
            }
        }, 300);
    };

    closeBtn.addEventListener('click', closeModalFn);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) closeModalFn();
    });

    // Expose close function for dynamic forms
    window._activeModalClose = closeModalFn;
}

export function closeModal() {
    if (window._activeModalClose) {
        window._activeModalClose();
        delete window._activeModalClose;
    } else {
        // Fallback for dangling modals without a registered close function
        document.querySelector('.modal-overlay')?.remove();
    }
}

/**
 * Helper to get and clone a template from the DOM
 * @param {string} id - The template element ID
 * @param {Object} data - Optional data object for simple {{key}} substitution
 * @returns {DocumentFragment}
 */
export function getTemplate(id, data = {}) {
    const template = document.getElementById(id);
    if (!template) {
        console.error(`Template ${id} not found`);
        return null;
    }

    let content = template.innerHTML;
    
    // Simple interpolation for {{key}}
    Object.entries(data).forEach(([key, value]) => {
        const regex = new RegExp(`{{${key}}}`, 'g');
        content = content.replace(regex, value === null || value === undefined ? '' : value);
    });

    // Detect if the content is a table row (<tr>) — must be parsed inside a
    // <table><tbody> context or the browser will orphan the <tr> elements
    // when using a plain <div> as the parser container.
    const trimmed = content.trim();
    let container;
    if (/^<tr[\s>]/i.test(trimmed)) {
        // Table row context
        const table = document.createElement('table');
        const tbody = document.createElement('tbody');
        table.appendChild(tbody);
        tbody.innerHTML = content;
        container = tbody;
    } else if (/^<td[\s>]|^<th[\s>]/i.test(trimmed)) {
        // Table cell context
        const table = document.createElement('table');
        const tbody = document.createElement('tbody');
        const row   = document.createElement('tr');
        table.appendChild(tbody);
        tbody.appendChild(row);
        row.innerHTML = content;
        container = row;
    } else {
        container = document.createElement('div');
        container.innerHTML = content;
    }
    
    const fragment = document.createDocumentFragment();
    while (container.firstChild) {
        fragment.appendChild(container.firstChild);
    }
    
    return fragment;
}


export function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return dateString;
    }
}

export function formatOrderDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return dateString;
    }
}
/**
 * Format number with thousands separator
 * @param {number} num - Number to format
 * @returns {string} Formatted number (e.g., "1,234")
 */
export function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

/**
 * Format currency in LKR (Sri Lankan Rupees)
 * Converts from cents to rupees and formats with proper locale
 * @param {number} cents - Amount in cents
 * @returns {string} Formatted currency (e.g., "Rs 1,234.50")
 */
export function formatCurrency(cents) {
    if (cents === null || cents === undefined) return 'Rs 0.00';
    
    const rupees = cents / 100;
    return `Rs ${rupees.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
}

/**
 * Format percentage value
 * @param {number} value - Percentage value (e.g., 45 for 45%)
 * @returns {string} Formatted percentage (e.g., "45%")
 */
export function formatPercent(value) {
    if (value === null || value === undefined) return '0%';
    
    const num = Number(value);
    const rounded = Math.round(num * 10) / 10;
    
    return `${rounded.toLocaleString('en-US', {
        minimumFractionDigits: rounded % 1 === 0 ? 0 : 1,
        maximumFractionDigits: 1
    })}%`;
}

/**
 * Format time duration in hours/minutes/days
 * @param {number} hours - Duration in hours
 * @returns {string} Human-readable duration (e.g., "2d 5h")
 */
export function formatDuration(hours) {
    if (hours === null || hours === undefined) return '0h';
    
    const h = Math.floor(hours);
    const days = Math.floor(h / 24);
    const remainingHours = h % 24;
    
    if (days > 0) {
        return remainingHours > 0 ? `${days}d ${remainingHours}h` : `${days}d`;
    }
    return `${remainingHours}h`;
}

/**
 * Format large numbers with abbreviations (K, M, B)
 * @param {number} num - Number to format
 * @returns {string} Abbreviated number (e.g., "1.2K", "5.8M")
 */
export function formatCompactNumber(num) {
    if (num === null || num === undefined) return '0';
    
    const n = Math.abs(Number(num));
    
    if (n >= 1000000000) {
        return (num / 1000000000).toLocaleString('en-US', {
            maximumFractionDigits: 1
        }) + 'B';
    }
    if (n >= 1000000) {
        return (num / 1000000).toLocaleString('en-US', {
            maximumFractionDigits: 1
        }) + 'M';
    }
    if (n >= 1000) {
        return (num / 1000).toLocaleString('en-US', {
            maximumFractionDigits: 1
        }) + 'K';
    }
    
    return formatNumber(num);
}

/**
 * Format date to readable format
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date (e.g., "Jan 15, 2025")
 */
export function formatDateShort(date) {
    if (!date) return '';
    
    try {
        const d = new Date(date);
        return d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return String(date);
    }
}

/**
 * Format date with time
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date with time (e.g., "Jan 15, 2025 2:30 PM")
 */
export function formatDateTime(date) {
    if (!date) return '';
    
    try {
        const d = new Date(date);
        return d.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (error) {
        return String(date);
    }
}

/**
 * Format percentage change with indicator
 * @param {number} value - Percentage value
 * @param {boolean} isIncrease - Whether it's an increase (true) or decrease (false)
 * @returns {string} Formatted percentage with arrow (e.g., "↑ 15%" or "↓ 5%")
 */
export function formatPercentChange(value, isIncrease) {
    if (value === null || value === undefined) return '0%';
    
    const arrow = isIncrease ? '↑' : '↓';
    const num = Math.abs(Number(value));
    const rounded = Math.round(num * 10) / 10;
    
    return `${arrow} ${rounded.toLocaleString('en-US', {
        minimumFractionDigits: rounded % 1 === 0 ? 0 : 1,
        maximumFractionDigits: 1
    })}%`;
}

/**
 * Format inventory status with color indicator
 * @param {number} available - Available quantity
 * @param {number} threshold - Low stock threshold (default 20)
 * @returns {object} Status object with text and color
 */
export function getInventoryStatus(available, threshold = 20) {
    const n = Number(available);
    
    if (n === 0) {
        return { text: 'Out of Stock', color: 'danger', value: n };
    }
    if (n <= threshold) {
        return { text: 'Low Stock', color: 'warning', value: n };
    }
    return { text: 'In Stock', color: 'success', value: n };
}

/**
 * Universal admin API request helper.
 * - Normalizes fetch options
 * - Handles HTML error pages vs JSON
 * - Handles common auth/permission cases
 * Returns the parsed JSON object (full envelope), callers decide how to read data.
 */
export async function apiRequest(url, {
    method = 'GET',
    body = undefined,
    headers = {},
    credentials = 'same-origin',
    redirectOnAuthError = true,
} = {}) {
    const opts = {
        method,
        headers: {
            'Content-Type': 'application/json',
            ...headers,
        },
        credentials,
    };

    if (method !== 'GET' && body !== undefined) {
        opts.body = typeof body === 'string' ? body : JSON.stringify(body);
    }

    const response = await fetch(url, opts);

    // Read text once so we can distinguish HTML vs JSON
    const text = await response.text().catch(() => '');

    if (!response.ok) {
        // Auth / permission shortcuts
        if (response.status === 401 && redirectOnAuthError) {
            window.location.href = 'login.php';
            throw new Error('Authorization required. Please sign in.');
        }
        if (response.status === 403) {
            throw new Error('Access Denied: Administrative credentials required.');
        }

        // HTML error page
        if (text.trim().startsWith('<')) {
            throw new Error(`Server error (${response.status}). Check PHP logs.`);
        }

        let errorJson = null;
        try {
            errorJson = text ? JSON.parse(text) : null;
        } catch (_) {
            // fall through
        }

        const message = errorJson?.message || `HTTP ${response.status}: ${response.statusText}`;
        throw new Error(message);
    }

    // Successful response: allow empty body
    if (!text) return {};

    if (text.trim().startsWith('<')) {
        throw new Error('Server returned HTML instead of JSON');
    }

    try {
        return JSON.parse(text);
    } catch (e) {
        throw new Error('Failed to parse JSON response');
    }
}

// ============================================================================
// Entity Handler Utility - Scoped to Page Container
// ============================================================================

/**
 * Entity handler configuration
 * @typedef {Object} EntityHandlerConfig
 * @property {string} viewClass - CSS class for view buttons (e.g., '.product-view')
 * @property {string} editClass - CSS class for edit buttons (e.g., '.product-edit')
 * @property {Function} renderModal - Function(id) that returns modal HTML
 * @property {string} viewTitle - Modal title for view action
 * @property {string} editTitle - Modal title for edit action
 * @property {string} editPath - Path to edit form (e.g., 'manage/product/update.php')
 * @property {string} [modalSize='lg'] - Modal size ('lg', 'xl')
 * @property {Function} [onLoadMore] - Handler for load more button
 * @property {string} [loadMoreId] - ID of load more button
 * @property {Function} [onRefresh] - Handler for refresh button
 * @property {string} [refreshId] - ID of refresh button
 * @property {Function} [onSearch] - Handler for search input
 * @property {string} [searchId] - ID of search input
 * @property {string} [createClass] - CSS class for create buttons (e.g., '.product-create')
 * @property {string} [createPath] - Path to create form (e.g., 'manage/product/create.php')
 * @property {string} [createTitle] - Modal title for create action
 * @property {Function} [onSortChange] - Handler for sort select change
 * @property {string} [sortId] - ID of sort select
 */

/**
 * Creates scoped entity handlers attached to the page container
 * Listeners are automatically cleaned up when the page container is cleared
 * @param {HTMLElement} container - Page container element (usually #content)
 * @param {EntityHandlerConfig} config - Handler configuration
 * @returns {Object} Controller with cleanup method
 */
export function createEntityHandler(container, config) {
    if (!container) {
        console.warn('createEntityHandler: container is required');
        return { cleanup: () => {} };
    }

    const {
        viewClass,
        editClass,
        renderModal,
        viewTitle,
        editTitle,
        editPath,
        modalSize = 'lg',
        onLoadMore,
        loadMoreId,
        onRefresh,
        refreshId,
        onSearch,
        searchId,
        createClass,
        createPath,
        createTitle,
        onSortChange,
        sortId
    } = config;

    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler - scoped to container
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest(viewClass);
        if (!btn || !btn.dataset.id) return;

        const id = btn.dataset.id;
        try {
            const html = await renderModal(parseInt(id));
            openStandardModal({
                title: viewTitle,
                bodyHtml: html,
                size: modalSize
            });
        } catch (error) {
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Edit button handler - scoped to container
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest(editClass);
        if (!btn || !btn.dataset.id) return;
        
        console.log('[Entity Handler] Edit button clicked:', { editClass, id: btn.dataset.id });

        const id = btn.dataset.id;
        openFormModal(`${editPath}?id=${encodeURIComponent(id)}`, editTitle);
    }, { signal });

    // Load more handler
    if (onLoadMore && loadMoreId) {
        container.addEventListener('click', async (e) => {
            if (e.target.id !== loadMoreId) return;
            const btn = e.target;
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = 'Loading...';

            try {
                await onLoadMore(btn);
            } catch (error) {
                console.error('Load more error:', error);
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }, { signal });
    }

    // Refresh handler
    if (onRefresh && refreshId) {
        container.addEventListener('click', async (e) => {
            if (e.target.id !== refreshId) return;
            const btn = e.target;
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = 'Refreshing...';

            try {
                await onRefresh(btn);
            } catch (error) {
                console.error('Refresh error:', error);
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }, { signal });
    }

    // Create button handler
    if (createClass && createPath) {
        container.addEventListener('click', (e) => {
            const btn = e.target.closest(createClass);
            if (!btn) return;
            e.preventDefault();
            console.log('[Entity Handler] Create button clicked:', { createClass });
            openFormModal(createPath, createTitle || 'Create');
        }, { signal });
    }

    // Search handler with debounce
    if (onSearch && searchId) {
        const debouncedSearch = debounce(async (e) => {
            await onSearch(e);
        }, 300);

        container.addEventListener('input', (e) => {
            if (e.target && e.target.id === searchId) {
                debouncedSearch(e);
            }
        }, { signal });
    }

    // Sort change handler
    if (onSortChange && sortId) {
        container.addEventListener('change', (e) => {
            if (e.target && e.target.id === sortId) {
                onSortChange(e);
            }
        }, { signal });
    }

    return {
        cleanup: () => {
            abortController.abort();
        }
    };
}

/**
 * getFormData — Serialization utility for SPA forms.
 * Extracts values and handles checkbox boolean conversion.
 */
export function getFormData(form) {
    const formData = new FormData(form);
    const data = {};

    for (const [key, value] of formData.entries()) {
        if (form.elements[key]?.type === 'checkbox') {
            data[key] = form.elements[key].checked;
        } else {
            data[key] = value;
        }
    }
    return data;
}
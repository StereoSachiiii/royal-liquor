/**
 * Modern Admin Dashboard - Enhanced JavaScript
 * Clean state management, reusable components, and modern interactions
 */

// Enhanced State Management with localStorage persistence
class StateManager {
    constructor() {
        this.state = this.loadState();
        this.listeners = [];
    }
    
    loadState() {
        try {
            const saved = localStorage.getItem('adminDashboardState');
            return saved ? JSON.parse(saved) : {
                currentView: 'overview',
                searchQuery: '',
                filters: {},
                sort: { field: 'id', order: 'asc' },
                pagination: { page: 1, limit: 10 },
                ui: {
                    sidebarCollapsed: false,
                    theme: 'light'
                }
            };
        } catch (error) {
            console.warn('Failed to load state from localStorage:', error);
            return this.getDefaultState();
        }
    }
    
    getDefaultState() {
        return {
            currentView: 'overview',
            searchQuery: '',
            filters: {},
            sort: { field: 'id', order: 'asc' },
            pagination: { page: 1, limit: 10 },
            ui: {
                sidebarCollapsed: false,
                theme: 'light'
            }
        };
    }
    
    setState(updates) {
        this.state = { ...this.state, ...updates };
        this.saveState();
        this.notifyListeners();
    }
    
    getState() {
        return { ...this.state };
    }
    
    saveState() {
        try {
            localStorage.setItem('adminDashboardState', JSON.stringify(this.state));
        } catch (error) {
            console.warn('Failed to save state to localStorage:', error);
        }
    }
    
    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }
    
    notifyListeners() {
        this.listeners.forEach(listener => listener(this.getState()));
    }
}

// Global state instance
const stateManager = new StateManager();

// Enhanced debounce utility
const debounce = (func, wait = 300) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Modern Toast Notification System
class ToastNotification {
    static container = null;
    
    static init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    }
    
    static show(message, type = 'info', duration = 5000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} animate-slide-in`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-message">${this.escapeHtml(message)}</span>
                <button class="toast-close" aria-label="Close notification">&times;</button>
            </div>
        `;
        
        this.container.appendChild(toast);
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => this.remove(toast), duration);
        }
        
        // Close button handler
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.remove(toast);
        });
        
        return toast;
    }
    
    static remove(toast) {
        if (toast && toast.parentNode) {
            toast.classList.add('animate-fade-out');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 200);
        }
    }
    
    static escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Enhanced Modal System
class Modal {
    constructor(id) {
        this.id = id;
        this.element = document.getElementById(id);
        this.isOpen = false;
        this.focusTrap = null;
        this.previousFocus = null;
        
        if (this.element) {
            this.initialize();
        }
    }
    
    initialize() {
        // Close handlers
        this.element.addEventListener('click', (e) => {
            if (e.target === this.element) {
                this.close();
            }
        });
        
        // Escape key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Close buttons
        this.element.querySelectorAll('[data-dismiss="modal"], .modal-close').forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });
    }
    
    open(content = null) {
        if (!this.element) return;
        
        // Store current focus
        this.previousFocus = document.activeElement;
        
        // Set content if provided
        if (content) {
            const contentElement = this.element.querySelector('.modal-body') || this.element;
            contentElement.innerHTML = content;
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Show modal
        this.element.classList.add('active');
        this.isOpen = true;
        
        // Focus management
        this.trapFocus();
        
        // Emit event
        this.element.dispatchEvent(new CustomEvent('modal:opened', { detail: { modal: this } }));
    }
    
    close() {
        if (!this.element || !this.isOpen) return;
        
        // Hide modal
        this.element.classList.remove('active');
        this.isOpen = false;
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Restore focus
        if (this.previousFocus) {
            this.previousFocus.focus();
        }
        
        // Emit event
        this.element.dispatchEvent(new CustomEvent('modal:closed', { detail: { modal: this } }));
    }
    
    trapFocus() {
        const focusableElements = this.element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
    }
    
    // Static method for quick modals
    static show(content, options = {}) {
        const modal = new Modal('modal');
        modal.open(content);
        return modal;
    }
}

// Table Component
class DataTable {
    constructor(selector, options = {}) {
        this.table = document.querySelector(selector);
        if (!this.table) return;
        
        this.options = {
            searchable: true,
            sortable: true,
            pagination: true,
            pageSize: 10,
            ...options
        };
        
        this.data = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.totalPages = 1;
        
        this.initialize();
    }
    
    async initialize() {
        if (this.options.url) {
            await this.loadData();
        } else {
            this.data = this.getDataFromTable();
            this.filteredData = [...this.data];
            this.render();
        }
        
        if (this.options.searchable) this.initSearch();
        if (this.options.sortable) this.initSorting();
        if (this.options.pagination) this.initPagination();
    }
    
    async loadData() {
        try {
            state.isLoading = true;
            this.renderLoading();
            
            const response = await fetch(this.options.url);
            if (!response.ok) throw new Error('Failed to load data');
            
            this.data = await response.json();
            this.filteredData = [...this.data];
            this.totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
            
            this.render();
        } catch (error) {
            console.error('Error loading data:', error);
            Notifications.show('Failed to load data. Please try again.', 'error');
        } finally {
            state.isLoading = false;
        }
    }
    
    getDataFromTable() {
        if (!this.table) return [];
        
        const headers = Array.from(this.table.querySelectorAll('th')).map(th => th.dataset.field);
        const rows = Array.from(this.table.querySelectorAll('tbody tr'));
        
        return rows.map(row => {
            const item = {};
            const cells = row.querySelectorAll('td');
            
            headers.forEach((header, index) => {
                if (header && cells[index]) {
                    item[header] = cells[index].textContent.trim();
                }
            });
            
            return item;
        });
    }
    
    initSearch() {
        const searchInput = document.querySelector('.search-bar input');
        if (!searchInput) return;
        
        const handleSearch = debounce((e) => {
            const query = e.target.value.toLowerCase();
            state.searchQuery = query;
            this.filterData();
        }, 300);
        
        searchInput.addEventListener('input', handleSearch);
    }
    
    initSorting() {
        const headers = this.table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const field = header.dataset.field;
                const isAsc = state.sort.field === field && state.sort.order === 'asc';
                state.sort = {
                    field,
                    order: isAsc ? 'desc' : 'asc'
                };
                this.sortData();
            });
        });
    }
    
    initPagination() {
        // Implementation for pagination controls
    }
    
    filterData() {
        if (!state.searchQuery) {
            this.filteredData = [...this.data];
        } else {
            const query = state.searchQuery.toLowerCase();
            this.filteredData = this.data.filter(item => 
                Object.values(item).some(value => 
                    String(value).toLowerCase().includes(query)
                )
            );
        }
        
        this.totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        this.currentPage = 1;
        this.render();
    }
    
    sortData() {
        const { field, order } = state.sort;
        
        this.filteredData.sort((a, b) => {
            let valueA = a[field];
            let valueB = b[field];
            
            // Handle numeric values
            if (!isNaN(valueA) && !isNaN(valueB)) {
                valueA = parseFloat(valueA);
                valueB = parseFloat(valueB);
                return order === 'asc' ? valueA - valueB : valueB - valueA;
            }
            
            // Handle string values
            valueA = String(valueA || '').toLowerCase();
            valueB = String(valueB || '').toLowerCase();
            
            if (order === 'asc') {
                return valueA.localeCompare(valueB);
            } else {
                return valueB.localeCompare(valueA);
            }
        });
        
        this.render();
    }
    
    renderLoading() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;
        
        const colSpan = this.table.querySelectorAll('th').length;
        tbody.innerHTML = `
            <tr>
                <td colspan="${colSpan}" class="text-center p-4">
                    <div class="animate-pulse">Loading data...</div>
                </td>
            </tr>
        `;
    }
    
    render() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;
        
        if (this.filteredData.length === 0) {
            const colSpan = this.table.querySelectorAll('th').length;
            tbody.innerHTML = `
                <tr>
                    <td colspan="${colSpan}" class="text-center p-4 text-gray-500">
                        No data available
                    </td>
                </tr>
            `;
            return;
        }
        
        // Apply pagination
        const start = (this.currentPage - 1) * this.options.pageSize;
        const paginatedData = this.filteredData.slice(start, start + this.options.pageSize);
        
        tbody.innerHTML = paginatedData.map(item => this.renderRow(item)).join('');
        
        // Update pagination controls
        this.updatePagination();
    }
    
    renderRow(item) {
        // This should be implemented by the specific table instance
        return '';
    }
    
    updatePagination() {
        // Implementation for pagination controls
    }
}

// Initialize the admin dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => new Modal(modal.id));
    
    // Initialize data tables
    const tables = document.querySelectorAll('table[data-datatable]');
    tables.forEach(table => {
        const url = table.dataset.url;
        if (url) {
            new DataTable(`#${table.id}`, { url });
        } else {
            new DataTable(`#${table.id}`);
        }
    });
    
    // Toggle mobile menu
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }
    
    // Handle form submissions with fetch
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Processing...';
            }
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method,
                    body: form.enctype === 'multipart/form-data' ? formData : new URLSearchParams(formData),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    Notifications.show(result.message || 'Operation completed successfully', 'success');
                    
                    // Close modal if form is in one
                    const modal = form.closest('.modal');
                    if (modal) {
                        modal.classList.remove('active');
                    }
                    
                    // Reload data if needed
                    if (form.dataset.reload) {
                        const table = document.querySelector(form.dataset.reload);
                        if (table && table.DataTable) {
                            table.DataTable.ajax.reload();
                        }
                    }
                } else {
                    throw new Error(result.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                Notifications.show(error.message || 'An error occurred. Please try again.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    });
    
    // Initialize any tooltips
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    tooltipTriggers.forEach(trigger => {
        // Implementation for tooltips
    });
});

// Expose to global scope for easier debugging
window.Admin = {
    state,
    Notifications,
    Modal,
    DataTable
};

// Save state before page unload
window.addEventListener('beforeunload', saveState);

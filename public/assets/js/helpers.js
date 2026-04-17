/**
 * Normalized Error and Null Check Handlers
 * Centralized utilities for consistent error handling and null checks
 */

/**
 * Safe null check - returns default value if null/undefined
 * @param {*} value - Value to check
 * @param {*} defaultValue - Default value if null/undefined
 * @returns {*} - Value or default
 */
export function nullSafe(value, defaultValue = null) {
    return value !== null && value !== undefined ? value : defaultValue;
}

/**
 * Safe number conversion
 * @param {*} value - Value to convert
 * @param {number} defaultValue - Default if conversion fails
 * @returns {number} - Converted number or default
 */
export function toNumber(value, defaultValue = 0) {
    const num = Number(value);
    return isNaN(num) ? defaultValue : num;
}

/**
 * Safe array check
 * @param {*} value - Value to check
 * @returns {Array} - Array or empty array
 */
export function toArray(value) {
    if (Array.isArray(value)) return value;
    if (value === null || value === undefined) return [];
    return [value];
}

/**
 * Safe JSON parse
 * @param {string} json - JSON string to parse
 * @param {*} defaultValue - Default value if parse fails
 * @returns {*} - Parsed object or default
 */
export function safeJsonParse(json, defaultValue = null) {
    try {
        return JSON.parse(json);
    } catch (error) {
        console.warn('JSON parse failed:', error);
        return defaultValue;
    }
}

/**
 * Safe API response handler
 * @param {Response} response - Fetch response
 * @returns {Promise<Object>} - Normalized response
 */
export async function handleApiResponse(response) {
    try {
        // Check if response is ok
        if (!response.ok) {
            return {
                success: false,
                data: null,
                message: `HTTP ${response.status}: ${response.statusText}`,
                error: new Error(response.statusText)
            };
        }

        // Try to parse JSON
        const text = await response.text();

        // Check if response is HTML (error page)
        if (text.trim().startsWith('<')) {
            return {
                success: false,
                data: null,
                message: 'Server returned HTML instead of JSON. Check API endpoint.',
                error: new Error('Invalid JSON response')
            };
        }

        // Parse JSON
        const data = JSON.parse(text);

        // Normalize response format
        return {
            success: data.success !== false,
            data: data.data || data,
            message: data.message || '',
            error: data.success === false ? new Error(data.message) : null
        };

    } catch (error) {
        return {
            success: false,
            data: null,
            message: error.message || 'Unknown error occurred',
            error: error
        };
    }
}

/**
 * Safe localStorage get
 * @param {string} key - Storage key
 * @param {*} defaultValue - Default value
 * @returns {*} - Stored value or default
 */
export function getStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        if (item === null) return defaultValue;
        return JSON.parse(item);
    } catch (error) {
        console.warn(`Failed to get storage key "${key}":`, error);
        return defaultValue;
    }
}

/**
 * Safe localStorage set
 * @param {string} key - Storage key
 * @param {*} value - Value to store
 * @returns {boolean} - Success status
 */
export function setStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (error) {
        console.error(`Failed to set storage key "${key}":`, error);
        return false;
    }
}

/**
 * Safe element query
 * @param {string} selector - CSS selector
 * @param {Element} parent - Parent element (default: document)
 * @returns {Element|null} - Element or null
 */
export function $(selector, parent = document) {
    try {
        return parent.querySelector(selector);
    } catch (error) {
        console.warn(`Invalid selector "${selector}":`, error);
        return null;
    }
}

/**
 * Safe element query all
 * @param {string} selector - CSS selector
 * @param {Element} parent - Parent element (default: document)
 * @returns {Array<Element>} - Array of elements
 */
export function $$(selector, parent = document) {
    try {
        return Array.from(parent.querySelectorAll(selector));
    } catch (error) {
        console.warn(`Invalid selector "${selector}":`, error);
        return [];
    }
}

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in ms
 * @returns {Function} - Debounced function
 */
export function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 * @param {Function} func - Function to throttle
 * @param {number} limit - Time limit in ms
 * @returns {Function} - Throttled function
 */
export function throttle(func, limit = 300) {
    let inThrottle;
    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Format price in cents to Rupees
 * @param {number} cents - Price in cents (LKR)
 * @returns {string} - Formatted price
 */
export function formatPrice(cents) {
    const rupees = toNumber(cents, 0) / 100;
    return `Rs. ${rupees.toFixed(2)}`;
}

/**
 * Validate email
 * @param {string} email - Email to validate
 * @returns {boolean} - Valid or not
 */
export function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Global error handler
 * @param {Error} error - Error object
 * @param {string} context - Context where error occurred
 */
export function logError(error, context = '') {
    const errorInfo = {
        message: error.message || 'Unknown error',
        stack: error.stack,
        context: context,
        timestamp: new Date().toISOString()
    };

    console.error(`[Error${context ? ` in ${context}` : ''}]:`, errorInfo);

    // In production, you might want to send this to an error tracking service
    // sendToErrorTracking(errorInfo);
}

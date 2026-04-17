/**
 * Unified API Handler
 * All API calls should go through this module for consistency
 * No external libraries - vanilla JavaScript only
 */

import { getApiUrl, APP_CONFIG, isDevelopment } from './config.js';

/**
 * API Response wrapper
 * @typedef {Object} ApiResponse
 * @property {boolean} success - Whether the request was successful
 * @property {*} data - Response data
 * @property {string} message - Response message
 * @property {Error} error - Error object if request failed
 */

/**
 * Unified API call function
 * @param {string} endpoint - API endpoint name from config
 * @param {object} options - Request options
 * @param {string} options.method - HTTP method (GET, POST, PUT, DELETE)
 * @param {object} options.params - URL query parameters (for GET requests)
 * @param {object} options.body - Request body (for POST/PUT requests)
 * @param {object} options.headers - Additional headers
 * @param {number} options.timeout - Request timeout in ms
 * @returns {Promise<ApiResponse>} - API response
 */
export async function apiCall(endpoint, options = {}) {
    const {
        method = 'GET',
        params = {},
        body = null,
        headers = {},
        timeout = APP_CONFIG.apiTimeout,
    } = options;

    // Build URL with query parameters for GET requests
    const url = getApiUrl(endpoint, method === 'GET' ? params : {});

    // Build fetch options
    const fetchOptions = {
        method,
        headers: {
            'Content-Type': 'application/json',
            ...headers,
        },
        credentials: 'same-origin', // Include cookies for session management
    };

    // Add body for non-GET requests
    if (body && method !== 'GET') {
        fetchOptions.body = JSON.stringify(body);
    }

    // Create abort controller for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);
    fetchOptions.signal = controller.signal;

    try {
        // Log request in development
        if (isDevelopment()) {
            console.log(`[API] ${method} ${endpoint}`, { params, body });
        }

        const response = await fetch(url, fetchOptions);
        clearTimeout(timeoutId);

        // Parse response
        const data = await response.json();

        // Log response in development
        if (isDevelopment()) {
            console.log(`[API] Response ${endpoint}:`, data);
        }

        // Check if response is successful
        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        // Check API success flag
        if (!data.success) {
            throw new Error(data.message || 'API request failed');
        }

        return {
            success: true,
            data: data.data,
            message: data.message || 'Success',
            error: null,
        };

    } catch (error) {
        clearTimeout(timeoutId);

        // Handle different error types
        let errorMessage = 'An unexpected error occurred';

        if (error.name === 'AbortError') {
            errorMessage = 'Request timeout';
        } else if (error.message) {
            errorMessage = error.message;
        }

        // Log error in development
        if (isDevelopment()) {
            console.error(`[API] Error ${endpoint}:`, error);
        }

        return {
            success: false,
            data: null,
            message: errorMessage,
            error: error,
        };
    }
}

/**
 * Convenience methods for common HTTP verbs
 */
export const api = {
    /**
     * GET request
     * @param {string} endpoint - API endpoint name
     * @param {object} params - Query parameters
     * @returns {Promise<ApiResponse>}
     */
    get: (endpoint, params = {}) => apiCall(endpoint, { method: 'GET', params }),

    /**
     * POST request
     * @param {string} endpoint - API endpoint name
     * @param {object} body - Request body
     * @returns {Promise<ApiResponse>}
     */
    post: (endpoint, body = {}) => apiCall(endpoint, { method: 'POST', body }),

    /**
     * PUT request
     * @param {string} endpoint - API endpoint name
     * @param {object} body - Request body
     * @returns {Promise<ApiResponse>}
     */
    put: (endpoint, body = {}) => apiCall(endpoint, { method: 'PUT', body }),

    /**
     * DELETE request
     * @param {string} endpoint - API endpoint name
     * @param {object} params - Query parameters
     * @returns {Promise<ApiResponse>}
     */
    delete: (endpoint, params = {}) => apiCall(endpoint, { method: 'DELETE', params }),
};

/**
 * Batch API calls - execute multiple requests in parallel
 * @param {Array<{endpoint: string, options: object}>} requests - Array of request configs
 * @returns {Promise<Array<ApiResponse>>} - Array of responses
 */
export async function apiBatch(requests) {
    const promises = requests.map(({ endpoint, options }) => apiCall(endpoint, options));
    return Promise.all(promises);
}

/**
 * Simple cache for API responses
 */
const cache = new Map();

/**
 * Cached API call - stores response for specified duration
 * @param {string} endpoint - API endpoint name
 * @param {object} options - Request options
 * @param {number} cacheDuration - Cache duration in ms (default from config)
 * @returns {Promise<ApiResponse>}
 */
export async function apiCallCached(endpoint, options = {}, cacheDuration = APP_CONFIG.cacheDuration) {
    if (!APP_CONFIG.cacheEnabled) {
        return apiCall(endpoint, options);
    }

    // Create cache key from endpoint and options
    const cacheKey = JSON.stringify({ endpoint, options });

    // Check if cached response exists and is still valid
    const cached = cache.get(cacheKey);
    if (cached && Date.now() - cached.timestamp < cacheDuration) {
        if (isDevelopment()) {
            console.log(`[API] Cache hit: ${endpoint}`);
        }
        return cached.response;
    }

    // Make API call and cache response
    const response = await apiCall(endpoint, options);
    if (response.success) {
        cache.set(cacheKey, {
            response,
            timestamp: Date.now(),
        });
    }

    return response;
}

/**
 * Clear API cache
 * @param {string} endpoint - Optional endpoint to clear (clears all if not specified)
 */
export function clearCache(endpoint = null) {
    if (endpoint) {
        // Clear specific endpoint
        for (const [key] of cache) {
            if (key.includes(endpoint)) {
                cache.delete(key);
            }
        }
    } else {
        // Clear all cache
        cache.clear();
    }
}

// Export default api object
export default api;

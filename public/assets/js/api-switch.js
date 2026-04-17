/**
 * API Configuration - UI Test Mode
 * Set USE_MOCK_API to true to use dummy data instead of real API
 */

// Toggle this to switch between real and mock API
export const USE_MOCK_API = true;

// Import the appropriate API based on mode
let apiModule;
if (USE_MOCK_API) {
    apiModule = await import('./api-mock.js');
} else {
    apiModule = await import('./api.js');
}

// Export the API
export const api = apiModule.default || apiModule.api;
export default api;

import { API_URL} from "./config.js";
export const fetchOrderItems = async (orderId) => {
    try {
        const response = await fetch(`${API_URL}order-items.php?order_id=${orderId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw Error(`Error fetching order items ${response.statusText}`);
        }
        const body = await response.json();
        return Array.isArray(body.data) ? body.data : [body.data];
    } catch (error) {
        return { error: error };
    }
};

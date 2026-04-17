/**
 * Order & Payment Service
 * Handles backend transactions for orders and payments
 */

import { API } from './api-helper.js';

/**
 * Creates an order in the database
 * @param {number} cartId - The cart ID record
 * @param {number} totalCents - Total order amount
 * @param {number} userId - User ID
 * @param {number} addressId - Shipping/Billing address ID
 * @param {string} notes - Order notes
 * @param {Array} items - Cart items array
 */
export const createOrder = async (cartId, totalCents, userId, addressId, notes, items) => {
    try {
        const response = await API.orders.create({
            cart_id: cartId,
            total_cents: totalCents,
            user_id: userId,
            shipping_address_id: addressId,
            billing_address_id: addressId,
            notes: notes,
            items: items.map(item => ({
                product_id: item.id,
                product_name: item.name,
                price_cents: item.price_cents,
                quantity: item.quantity,
                product_image_url: item.image_url
            }))
        });
        return response.data;
    } catch (error) {
        console.error('Error creating order:', error);
        return { error: error.message };
    }
};

/**
 * Creates a payment record
 * @param {number} orderId - Order ID
 * @param {number} amountCents - Payment amount
 * @param {string} paymentMethod - Gateway type (card, gpay, etc)
 * @param {string} transactionId - External transaction reference
 */
export const createPayment = async (orderId, amountCents, paymentMethod, transactionId = null) => {
    try {
        const response = await API.payments.create({
            order_id: orderId,
            amount_cents: amountCents,
            currency: 'LKR',
            gateway: paymentMethod,
            transaction_id: transactionId,
            status: 'completed'
        });
        return response.data;
    } catch (error) {
        console.error('Error creating payment:', error);
        return { error: error.message };
    }
};



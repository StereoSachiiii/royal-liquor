<?php require_once __DIR__.'/../../config/urls.php'; require_once __DIR__.'/../../components/header.php';?>

<div class="order-history-container">
    <div class="order-header">
        <h1>My Orders</h1>
        <div class="order-summary">
            <span class="order-count">0 orders</span>
            <span class="order-total">$0.00</span>
        </div>
    </div>

    <div class="order-grid" id="orderGrid">
        <div class="order-empty-state">
            <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M9 2v4m6-4v4M4 9h16M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
            </svg>
            <p>No orders yet</p>
        </div>
    </div>
</div>

<!-- ORDER DETAIL MODAL -->
<div class="order-modal-overlay" id="orderModalOverlay">
    <div class="order-modal">
        <button class="modal-close" id="closeModal">×</button>
        
        <div class="modal-header">
            <h2>Order Details</h2>
        </div>
        
        <div class="modal-body" id="modalBody">
            <div class="order-items-list" id="orderItemsList">
                <div class="spinner"></div>
            </div>
            
            <div class="order-summary-box">
                <div class="summary-row"><span>Subtotal</span><span id="modalSubtotal">$0.00</span></div>
                <div class="summary-row"><span>Tax</span><span id="modalTax">$0.00</span></div>
                <div class="summary-row total"><span>Total</span><span id="modalTotal">$0.00</span></div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn-secondary" id="closeOrderDetails">Close</button>
            <button class="btn-cancel" id="cancelOrderBtn" style="display:none">Cancel Order</button>
            <button class="btn-primary" id="trackOrderBtn">Track Order</button>
        </div>
    </div>
</div>
<style>

:root {
    --color-primary: #111;
    --color-background: #fff;
    --color-secondary-bg: #f8f8f8;
    --color-border: #eee;
    --color-text-light: #999;
    --color-text-fade: rgba(17, 17, 17, 0.7);
    --color-total-bg: #111;
    --color-total-text: #fff;

    --space-xs: 8px;
    --space-sm: 16px;
    --space-md: 24px;
    --space-lg: 40px;
    --space-xl: 60px;
    --space-xxl: 80px;

    --font-size-base: 1rem;
    --font-heading: 5.2rem;
    --font-card-number: 1.6rem;
    --font-card-detail: 1.4rem;
    --font-card-amount: 2.2rem;
    --font-style-accent: italic;
    --font-family: "Canela", serif;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--color-background);
    color: var(--color-primary);
    font-family: var(--font-family);
}


.order-history-container {
    max-width: 1480px;
    margin: 0 auto;
    padding: var(--space-xxl) var(--space-lg); 
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-xxl);
}

.order-header h1 {
    font-size: var(--font-heading);
    font-weight: 300;
    font-style: var(--font-style-accent);
    letter-spacing: -0.06em;
}

.order-summary {
    text-align: right;
    font-style: var(--font-style-accent);
}

.order-count {
    font-size: 1.6rem;
    opacity: 0.8;
}

.order-total {
    font-size: 3rem;
    font-weight: 300;
    color: var(--color-primary);
    margin-top: var(--space-xs);
    display: block;
}


.order-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
    gap: var(--space-xl); /* 60px */
}

.order-card {
    background: var(--color-background);
    border: 2px solid var(--color-border);
    padding: 48px; 
    cursor: pointer;
    transition: transform 0.5s ease, border-color 0.5s, box-shadow 0.5s;
    position: relative;
    overflow: hidden;
}

.order-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--color-primary);
    opacity: 0;
    transition: opacity 0.6s;
}

.order-card:hover {
    transform: translateY(-20px);
    border-color: var(--color-primary);
    box-shadow: 0 40px 80px rgba(0, 0, 0, 0.15);
}

.order-card:hover::before {
    opacity: 0.04;
}


.order-number {
    font-size: var(--font-card-number);
    font-weight: 300;
    letter-spacing: -0.02em;
}

.order-status {
    position: absolute;
    top: 48px;
    right: 48px;
    padding: 10px 20px;
    font-size: var(--font-size-base);
    font-weight: 600;
    background: var(--color-primary);
    color: var(--color-total-text);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}


.order-status.pending,
.order-status.processing { background: #999; }
.order-status.shipped    { background: var(--color-primary); }
.order-status.delivered  { background: #000; }
.order-status.cancelled  { background: #666; }

.order-info {
    margin: var(--space-lg) 0;
    padding-bottom: var(--space-lg);
    border-bottom: 1px solid var(--color-border);
}

.order-info-row {
    display: flex;
    justify-content: space-between;
    margin: var(--space-sm) 0;
    font-size: var(--font-card-detail);
}

.order-info-row label {
    opacity: 0.7;
    font-style: var(--font-style-accent);
}

.order-amount {
    font-size: var(--font-card-amount) !important;
    font-weight: 300;
}

.order-dates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px 40px; 
    font-size: 1.2rem;
}

.order-date-item label {
    display: block;
    opacity: 0.7;
    font-style: var(--font-style-accent);
    margin-bottom: 4px;
}

.order-date-item span {
    font-weight: 500;
}

.order-empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 140px var(--space-lg);
    opacity: 0.6;
}

.order-empty-state svg {
    stroke: var(--color-primary);
    margin-bottom: 32px;
}


.order-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.94);
    backdrop-filter: blur(12px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.order-modal-overlay.active {
    display: flex;
}

.order-modal {
    background: var(--color-background);
    width: 92%;
    max-width: 1100px;
    max-height: 92vh;
    overflow: hidden;
    position: relative;
    box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
}

.modal-close {
    position: absolute;
    top: 32px;
    right: 32px;
    background: none;
    border: none;
    font-size: 52px; 
    color: var(--color-primary);
    cursor: pointer;
    z-index: 10;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s;
}

.modal-close:hover {
    color: #000;
}

.modal-header {
    padding: var(--space-xl) var(--space-xxl) 0;
}

.modal-header h2 {
    font-size: 4rem;
    font-weight: 300;
    font-style: var(--font-style-accent);
    letter-spacing: -0.04em;
}

.modal-body {
    padding: var(--space-xl) var(--space-xxl);
    max-height: 60vh;
    overflow-y: auto;
}

.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: transparent;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 4px;
}


.order-items-list {
    margin-bottom: var(--space-xl);
}

.order-item {
    display: flex;
    gap: 32px; 
    padding: 32px 0; 
    border-bottom: 1px solid var(--color-border);
    align-items: center;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 100px;
    height: 100px;
    background: var(--color-secondary-bg);
    flex-shrink: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative; 

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-image::after {
    content: "No Image";
    font-size: 0.9rem;
    color: var(--color-text-light);
    position: absolute;
}

.order-item-details {
    flex: 1;
}

.order-item-name {
    font-size: 1.8rem;
    font-weight: 300;
    margin-bottom: var(--space-xs);
}

.order-item-meta {
    font-size: 1.1rem;
    opacity: 0.8;
    margin: 6px 0;
}

.order-item-price {
    font-size: 2.4rem;
    font-weight: 300;
    white-space: nowrap;
}

.order-summary-box {
    background: var(--color-total-bg);
    color: var(--color-total-text);
    padding: var(--space-lg) 48px; 
    text-align: right;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 1.6rem;
    margin: var(--space-sm) 0;
}

.summary-row.total {
    font-size: 2.8rem;
    font-weight: 300;
    margin-top: 32px; 
    padding-top: 32px; 
    border-top: 1px solid #333;
}


.modal-footer {
    padding: var(--space-lg) var(--space-xxl); 
    display: flex;
    gap: 24px; 
    justify-content: flex-end;
    background: var(--color-secondary-bg);
}

.btn-secondary,
.btn-primary,
.btn-cancel {
    padding: 18px 40px;
    font-size: 1.3rem;
    border: none;
    cursor: pointer;
    transition: all 0.4s;
    font-family: var(--font-family);
    font-style: var(--font-style-accent);
}

.btn-secondary {
    background: transparent;
    border: 2px solid var(--color-primary);
    color: var(--color-primary);
}

.btn-secondary:hover {
    background: var(--color-primary);
    color: var(--color-background);
}

.btn-primary {
    background: var(--color-primary);
    color: var(--color-background);
}

.btn-primary:hover {
    background: #333;
}

.btn-cancel {
    background: #666;
    color: var(--color-background);
}

.btn-cancel:hover {
    background: #900;
}


.spinner {
    width: 48px;
    height: 48px;
    border: 3px solid var(--color-border);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 80px auto;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 968px) {
    .order-grid {
        grid-template-columns: 1fr;
    }

    .order-dates {
        grid-template-columns: 1fr;
    }

    .modal-header,
    .modal-body,
    .modal-footer {
        padding: var(--space-lg); 
    }

    .order-item {
        flex-direction: column;
        text-align: center;
    }

    .order-item-price {
        margin-top: var(--space-sm);
    }
}}
</style>

<script type="module">
import {fetchOrders} from '<?= BASE_URL ?>assets/js/orders.js';
import {fetchOrderItems} from '<?= BASE_URL ?>assets/js/order-items.js';
import {fetchUserAddresses, formatAddress} from '<?= BASE_URL ?>assets/js/addresses.js';

const getStatusClass = (s) => {
  const map = {pending:'pending',processing:'pending',shipped:'shipped',delivered:'delivered',cancelled:'cancelled'};
  return map[s] || 'pending';
};

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' });
const formatShortDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' }) : '—';

const renderOrders = async () => {
  const grid = document.getElementById('orderGrid');
  const countEl = document.querySelector('.order-count');
  const totalEl = document.querySelector('.order-total');
  const orders = await fetchOrders(<?= $session->getUserId(); ?>);
  if (!orders || orders.error || orders.length === 0) {
    grid.innerHTML = `<div class="order-empty-state"><svg>...</svg><p>No orders yet</p></div>`;
    return;
  }
  const total = orders.reduce((s,o) => s + (o.total_cents||0), 0) / 100;
  countEl.textContent = `${orders.length} order${orders.length>1?'s':''}`;
  totalEl.textContent = `$${total.toFixed(2)}`;
  const cards = await Promise.all(orders.map(async (o) => {
    const billing = o.billing_address ? formatAddress(o.billing_address) : '—';
    const shipping = o.shipping_address ? formatAddress(o.shipping_address) : '—';
    return `
      <div class="order-card" data-order-id="${o.id}">
        <div class="order-number">#${o.order_number || o.id}</div>
        <div class="order-status ${getStatusClass(o.status)}">
          ${o.status.charAt(0).toUpperCase() + o.status.slice(1)}
        </div>
        <div class="order-info">
          <div class="order-info-row"><label>Order ID</label><span>#${o.id}</span></div>
          <div class="order-info-row"><label>Total</label><span class="order-amount">$${((o.total_cents||0)/100).toFixed(2)}</span></div>
        </div>
        <div class="order-dates">
          <div class="order-date-item"><label>Ordered</label><span>${formatDate(o.created_at)}</span></div>
          ${o.paid_at ? `<div class="order-date-item"><label>Paid</label><span>${formatShortDate(o.paid_at)}</span></div>` : ''}
          ${o.shipped_at ? `<div class="order-date-item"><label>Shipped</label><span>${formatShortDate(o.shipped_at)}</span></div>` : ''}
          ${o.delivered_at ? `<div class="order-date-item"><label>Delivered</label><span>${formatShortDate(o.delivered_at)}</span></div>` : ''}
          <div class="order-date-item"><label>Billing</label><span>${billing}</span></div>
          <div class="order-date-item"><label>Shipping</label><span>${shipping}</span></div>
        </div>
      </div>`;
  }));
  grid.innerHTML = cards.join('');
  document.querySelectorAll('.order-card').forEach(c => c.onclick = () => openOrderModal(c.dataset.orderId));
};

let currentOrderId = null;
const openOrderModal = async (orderId) => {
  currentOrderId = orderId;
  const overlay = document.getElementById('orderModalOverlay');
  const itemsList = document.getElementById('orderItemsList');
  overlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  itemsList.innerHTML = '<div class="spinner"></div>';
  const res = await fetch(`<?= API_BASE_URL ?>orders.php?id=${orderId}&enriched=true`);
  const json = await res.json();
  const order = json.data;
  // Show cancel button if cancellable
  document.getElementById('cancelOrderBtn').style.display = ['pending','processing'].includes(order.status) ? 'block' : 'none';
  const subtotal = order.items.reduce((s,i) => s + (i.price_cents * i.quantity), 0) / 100;
  const total = (order.total_cents || 0) / 100;
  const tax = total - subtotal;
  document.getElementById('modalSubtotal').textContent = `$${subtotal.toFixed(2)}`;
  document.getElementById('modalTax').textContent = `$${tax.toFixed(2)}`;
  document.getElementById('modalTotal').textContent = `$${total.toFixed(2)}`;
  itemsList.innerHTML = order.items.map(item => `
    <div class="order-item">
      <div class="order-item-image">
        ${item.product_image_url ? `<img src="${item.product_image_url}" alt="${item.product_name}">` : ''}
      </div>
      <div class="order-item-details">
        <div class="order-item-name">${item.product_name}</div>
        <div class="order-item-meta">Quantity: ${item.quantity} × $${(item.price_cents/100).toFixed(2)}</div>
      </div>
      <div class="order-item-price">$${((item.price_cents * item.quantity)/100).toFixed(2)}</div>
    </div>
  `).join('');
};

const closeOrderModal = () => {
  document.getElementById('orderModalOverlay').classList.remove('active');
  document.body.style.overflow = '';
};

// Cancel Order
document.getElementById('cancelOrderBtn').onclick = async () => {
  if (!confirm('Cancel this order?')) return;
  const res = await fetch(`<?= API_BASE_URL ?>order.php?id=${currentOrderId}&action=cancel`, {method:'POST'});
  const json = await res.json();
  if (json.success) {
    alert('Order cancelled');
    closeOrderModal();
    renderOrders();
  }
};

document.getElementById('closeModal').onclick = document.getElementById('closeOrderDetails').onclick = closeOrderModal;
document.getElementById('orderModalOverlay').onclick = (e) => {
  if (e.target === document.getElementById('orderModalOverlay')) closeOrderModal();
};
document.getElementById('trackOrderBtn').onclick = () => {
  alert('Tracking not implemented yet');
};

renderOrders();

</script>

<template>
  <div class="orders-container">
    <div class="orders-header">
      <h1>
        <i class="bi bi-receipt"></i>
        {{ authStore.isAdmin ? 'All Orders' : 'My Orders' }}
      </h1>
      <p v-if="authStore.isAdmin" class="admin-badge">
        <i class="bi bi-shield-check"></i> Admin View
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p>Loading orders...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="orders.length === 0" class="empty-state">
      <i class="bi bi-inbox"></i>
      <p>No orders yet</p>
      <RouterLink to="/product" class="btn btn-primary">
        <i class="bi bi-shop"></i> Start Shopping
      </RouterLink>
    </div>

    <!-- Orders List -->
    <div v-else class="orders-list">
      <!-- Order Card -->
      <div v-for="order in orders" :key="order.id" class="order-card">
        <!-- Order Header -->
        <div class="order-header-section">
          <div class="order-info">
            <h3>Order #{{ order.id }}</h3>
            <p class="order-date">
              <i class="bi bi-calendar3"></i>
              {{ formatDate(order.created_at) }}
            </p>
            <!-- Customer Info (Admin only) -->
            <p v-if="authStore.isAdmin" class="customer-info">
              <i class="bi bi-person"></i> {{ order.username }} ({{ order.email }})
            </p>
          </div>
          <div class="order-status">
            <span :class="['status-badge', `status-${order.status}`]">
              {{ formatStatus(order.status) }}
            </span>
          </div>
        </div>

        <!-- Order Items -->
        <div class="order-items">
          <div v-for="(item, idx) in order.items" :key="idx" class="order-item-row">
            <div class="item-details">
              <img :src="item.thumbnail" :alt="item.title" class="item-thumbnail" />
              <div class="item-info">
                <p class="item-title">{{ item.title }}</p>
                <p class="item-price">${{ Number(item.price).toFixed(2) }}</p>
              </div>
            </div>
            <div class="item-quantity">
              <p>Qty: <strong>{{ item.quantity }}</strong></p>
            </div>
            <div class="item-subtotal">
              <p>${{ (Number(item.price) * Number(item.quantity)).toFixed(2) }}</p>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
          <div class="summary-row">
            <span>Total Items:</span>
            <strong>{{ order.total_items }}</strong>
          </div>
          <div class="summary-row total">
            <span>Total Amount:</span>
            <strong>${{ order.total_price.toFixed(2) }}</strong>
          </div>
        </div>

        <!-- Order Actions -->
        <div class="order-actions">
          <button @click="openInvoiceModal(order)" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-file-pdf"></i> Invoice
          </button>
          
          <!-- Status Update Dropdown (Admin only) -->
          <div v-if="authStore.isAdmin" class="status-dropdown">
            <select @change="(e) => { if (e.target.value) updateOrderStatus(order.id, e.target.value); e.target.value = '' }" 
                    class="form-select form-select-sm"
                    :value="''">
              <option value="">Change Status...</option>
              <option v-if="order.status !== 'pending'" value="pending">Mark Pending</option>
              <option v-if="order.status !== 'processing'" value="processing">Mark Processing</option>
              <option v-if="order.status !== 'shipped'" value="shipped">Mark Shipped</option>
              <option v-if="order.status !== 'delivered'" value="delivered">Mark Delivered</option>
              <option v-if="order.status !== 'cancelled'" value="cancelled">Mark Cancelled</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoice Modal -->
    <div v-if="showInvoiceModal" class="invoice-modal-overlay" @click="closeInvoiceModal">
      <div class="invoice-modal" @click.stop>
        <!-- Modal Header -->
        <div class="invoice-header">
          <h2>Order Invoice #{{ selectedOrder?.id }}</h2>
          <button @click="closeInvoiceModal" class="btn-close"></button>
        </div>

        <!-- Invoice Content (Printable) -->
        <div class="invoice-content" ref="invoiceRef">
          <!-- Company Info -->
          <div class="invoice-company">
            <h1>INVOICE</h1>
            <p class="company-name">Vue3 E-Commerce Store</p>
            <p class="company-details">www.store.local</p>
          </div>

          <!-- Order & Date Info -->
          <div class="invoice-info-grid">
            <div class="info-block">
              <label>Order Number</label>
              <p>#{{ selectedOrder?.id }}</p>
            </div>
            <div class="info-block">
              <label>Order Date</label>
              <p>{{ selectedOrder ? formatDate(selectedOrder.created_at) : '' }}</p>
            </div>
            <div class="info-block">
              <label>Status</label>
              <p :class="['status-badge', `status-${selectedOrder?.status}`]">
                {{ selectedOrder ? formatStatus(selectedOrder.status) : '' }}
              </p>
            </div>
          </div>

          <!-- Customer Info -->
          <div class="invoice-customer">
            <h3>Bill To</h3>
            <p class="customer-name">{{ selectedOrder?.username }}</p>
            <p class="customer-email">{{ selectedOrder?.email }}</p>
          </div>

          <!-- Items Table -->
          <table class="invoice-items-table">
            <thead>
              <tr>
                <th>Product</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in selectedOrder?.items" :key="idx">
                <td class="product-cell">
                  <img :src="item.thumbnail" :alt="item.title" class="product-image" />
                  <span>{{ item.title }}</span>
                </td>
                <td class="text-center">{{ item.quantity }}</td>
                <td class="text-right">${{ Number(item.price).toFixed(2) }}</td>
                <td class="text-right">${{ (Number(item.price) * item.quantity).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Totals -->
          <div class="invoice-totals">
            <div class="total-row">
              <span>Total Items:</span>
              <strong>{{ selectedOrder?.total_items }}</strong>
            </div>
            <div class="total-row final">
              <span>Total Amount:</span>
              <strong>${{ Number(selectedOrder?.total_price).toFixed(2) }}</strong>
            </div>
          </div>

          <!-- Footer -->
          <div class="invoice-footer">
            <p>Thank you for your purchase!</p>
            <p class="text-muted">This is a computer-generated invoice. No signature required.</p>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="invoice-actions">
          <button @click="printInvoice" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Invoice
          </button>
          <button @click="downloadInvoice" class="btn btn-outline-secondary">
            <i class="bi bi-download"></i> Download PDF
          </button>
          <button @click="closeInvoiceModal" class="btn btn-outline-secondary">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { showToast } from '../utils/toast'

const authStore = useAuthStore()
const orders = ref([])
const loading = ref(false)
const showInvoiceModal = ref(false)
const selectedOrder = ref(null)
const invoiceRef = ref(null)

// Fetch orders
async function fetchOrders() {
  loading.value = true
  try {
    const response = await fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${authStore.accessToken}`
      }
    })

    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.error || 'Failed to fetch orders')
    }

    const data = await response.json()
    
    // Convert string values to numbers for consistency
    orders.value = data.map(order => ({
      ...order,
      total_items: parseInt(order.total_items),
      total_price: parseFloat(order.total_price)
    }))
    
    if (orders.value.length === 0) {
      showToast.info('No orders found')
    }
  } catch (err) {
    showToast.error(err.message)
  } finally {
    loading.value = false
  }
}

// Format date
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Format status
function formatStatus(status) {
  const statuses = {
    pending: 'Pending',
    paid: 'Paid',
    processing: 'Processing',
    shipped: 'Shipped',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return statuses[status] || status
}

// Open invoice modal
function openInvoiceModal(order) {
  selectedOrder.value = order
  showInvoiceModal.value = true
}

// Close invoice modal
function closeInvoiceModal() {
  showInvoiceModal.value = false
  selectedOrder.value = null
}

// Print invoice
function printInvoice() {
  const printWindow = window.open('', '', 'width=800,height=600')
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Invoice #${selectedOrder.value.id}</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .invoice-container { max-width: 800px; margin: 0 auto; }
        .invoice-company { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .invoice-company h1 { margin: 0; font-size: 32px; font-weight: bold; }
        .company-name { margin: 10px 0 5px; font-size: 18px; font-weight: bold; }
        .company-details { margin: 0; color: #666; }
        .invoice-info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-block label { font-weight: bold; font-size: 12px; color: #666; text-transform: uppercase; }
        .info-block p { margin: 5px 0 0; font-size: 14px; }
        .invoice-customer { margin-bottom: 30px; }
        .invoice-customer h3 { margin: 0 0 10px; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .customer-name { margin: 0; font-size: 14px; font-weight: bold; }
        .customer-email { margin: 5px 0; font-size: 12px; color: #666; }
        .invoice-items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .invoice-items-table th { background: #f5f5f5; padding: 10px; text-align: left; font-weight: bold; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #333; }
        .invoice-items-table td { padding: 12px 10px; border-bottom: 1px solid #ddd; font-size: 13px; }
        .product-cell { display: flex; align-items: center; gap: 10px; }
        .product-image { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .invoice-totals { margin-bottom: 30px; border-top: 2px solid #333; padding-top: 15px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .total-row.final { font-size: 18px; font-weight: bold; color: #27ae60; }
        .invoice-footer { text-align: center; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
        .invoice-footer p { margin: 5px 0; }
        .text-muted { color: #999; }
        @media print { body { margin: 0; } }
      </style>
    </head>
    <body>
      <div class="invoice-container">
        <div class="invoice-company">
          <h1>INVOICE</h1>
          <p class="company-name">Vue3 E-Commerce Store</p>
          <p class="company-details">www.store.local</p>
        </div>

        <div class="invoice-info-grid">
          <div class="info-block">
            <label>Order Number</label>
            <p>#${selectedOrder.value.id}</p>
          </div>
          <div class="info-block">
            <label>Order Date</label>
            <p>${formatDate(selectedOrder.value.created_at)}</p>
          </div>
          <div class="info-block">
            <label>Status</label>
            <p>${formatStatus(selectedOrder.value.status)}</p>
          </div>
        </div>

        <div class="invoice-customer">
          <h3>Bill To</h3>
          <p class="customer-name">${selectedOrder.value.username}</p>
          <p class="customer-email">${selectedOrder.value.email}</p>
        </div>

        <table class="invoice-items-table">
          <thead>
            <tr>
              <th>Product</th>
              <th class="text-center">Quantity</th>
              <th class="text-right">Unit Price</th>
              <th class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            ${selectedOrder.value.items.map(item => `
              <tr>
                <td class="product-cell">
                  <img src="${item.thumbnail}" alt="${item.title}" class="product-image" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" />
                  <span>${item.title}</span>
                </td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-right">$${Number(item.price).toFixed(2)}</td>
                <td class="text-right">$${(Number(item.price) * item.quantity).toFixed(2)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>

        <div class="invoice-totals">
          <div class="total-row">
            <span>Total Items:</span>
            <strong>${selectedOrder.value.total_items}</strong>
          </div>
          <div class="total-row final">
            <span>Total Amount:</span>
            <strong>$${Number(selectedOrder.value.total_price).toFixed(2)}</strong>
          </div>
        </div>

        <div class="invoice-footer">
          <p>Thank you for your purchase!</p>
          <p class="text-muted">This is a computer-generated invoice. No signature required.</p>
        </div>
      </div>
      <script>
        window.print()
        window.onafterprint = function() { window.close() }
      <\/script>
    </body>
    </html>
  `)
  printWindow.document.close()
}

// Download invoice as PDF (requires html2pdf library - fallback to print)
function downloadInvoice() {
  showToast.info('Opening print dialog for PDF save...')
  printInvoice()
}

// Update order status (Admin only)
async function updateOrderStatus(orderId, newStatus) {
  try {
    const response = await fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.accessToken}`
      },
      body: JSON.stringify({
        order_id: orderId,
        status: newStatus
      })
    })

    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.error || 'Failed to update order status')
    }

    const data = await response.json()
    
    // Update the order in the list
    const orderIndex = orders.value.findIndex(o => o.id === orderId)
    if (orderIndex !== -1) {
      orders.value[orderIndex].status = newStatus
      // Also update selected order if it's open
      if (selectedOrder.value?.id === orderId) {
        selectedOrder.value.status = newStatus
      }
    }

    showToast.success(`Order #${orderId} status updated to ${formatStatus(newStatus)}`)
  } catch (err) {
    showToast.error(err.message)
  }
}

// Fetch orders on component mount
onMounted(() => {
  if (!authStore.isAuthenticated) {
    showToast.warning('Please sign in to view orders')
    return
  }
  fetchOrders()
})
</script>

<style lang="scss" scoped>
.orders-container {
  padding: 30px 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.orders-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 30px;

  h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .admin-badge {
    background-color: #ffc107;
    color: #333;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
  }
}

.empty-state {
  text-align: center;
  padding: 60px 20px;

  i {
    font-size: 4rem;
    color: #ccc;
    display: block;
    margin-bottom: 20px;
  }

  p {
    font-size: 1.25rem;
    color: #666;
    margin-bottom: 30px;
  }
}

.orders-list {
  display: grid;
  gap: 20px;
}

.order-card {
  background: white;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

  &:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    border-color: #bbb;
  }
}

.order-header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;

  .order-info {
    h3 {
      margin: 0 0 8px;
      font-size: 1.3rem;
      color: #333;
      font-weight: 600;
    }

    p {
      margin: 4px 0;
      font-size: 0.9rem;
      color: #666;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .customer-info {
      color: #0066cc;
      font-weight: 500;
    }
  }

  .order-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;

    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;

      &.status-pending {
        background-color: #ffc107;
        color: #333;
      }

      &.status-paid {
        background-color: #17a2b8;
        color: white;
      }

      &.status-processing {
        background-color: #007bff;
        color: white;
      }

      &.status-shipped {
        background-color: #6f42c1;
        color: white;
      }

      &.status-delivered {
        background-color: #28a745;
        color: white;
      }

      &.status-cancelled {
        background-color: #dc3545;
        color: white;
      }
    }
  }
}

.order-items {
  margin-bottom: 20px;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
}

.order-item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f5f5f5;

  &:last-child {
    border-bottom: none;
  }
}

.item-details {
  display: flex;
  gap: 12px;
  flex: 1;

  .item-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    background-color: #f5f5f5;
  }

  .item-info {
    .item-title {
      margin: 0 0 4px;
      font-size: 0.95rem;
      font-weight: 500;
      color: #333;
    }

    .item-price {
      margin: 0;
      font-size: 0.85rem;
      color: #27ae60;
      font-weight: 600;
    }
  }
}

.item-quantity {
  min-width: 100px;
  text-align: center;

  p {
    margin: 0;
    font-size: 0.9rem;
    color: #666;
  }
}

.item-subtotal {
  min-width: 100px;
  text-align: right;

  p {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #333;
  }
}

.order-summary {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;

  .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 0.95rem;

    &.total {
      font-size: 1.1rem;
      font-weight: 700;
      color: #27ae60;
      padding-top: 10px;
      border-top: 2px solid #27ae60;
    }
  }
}

.order-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  align-items: center;

  .btn {
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;

    &.btn-sm {
      padding: 6px 12px;
      font-size: 0.85rem;
    }

    i {
      font-size: 0.9rem;
    }
  }

  .status-dropdown {
    .form-select {
      padding: 6px 8px;
      border: 1px solid #ddd;
      border-radius: 6px;
      background-color: white;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.3s ease;

      &:hover {
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
      }

      &:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.2);
      }

      option {
        padding: 8px;
      }
    }
  }
}

.spinner-border {
  width: 2rem;
  height: 2rem;
  color: #0066cc;
}

// ── Invoice Modal ──────────────────────────────────
.invoice-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  overflow-y: auto;
  padding: 20px;
}

.invoice-modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 2px solid #eee;
  flex-shrink: 0;

  h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
  }

  .btn-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      color: #333;
    }
  }
}

.invoice-content {
  flex: 1;
  overflow-y: auto;
  padding: 40px;
  background: white;
}

.invoice-company {
  text-align: center;
  margin-bottom: 40px;
  padding-bottom: 20px;
  border-bottom: 3px solid #333;

  h1 {
    margin: 0;
    font-size: 36px;
    font-weight: 800;
    letter-spacing: 2px;
    color: #333;
  }

  .company-name {
    margin: 15px 0 5px;
    font-size: 18px;
    font-weight: 700;
    color: #333;
  }

  .company-details {
    margin: 0;
    color: #666;
    font-size: 13px;
  }
}

.invoice-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 30px;
  margin-bottom: 40px;

  .info-block {
    label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      color: #999;
      margin-bottom: 5px;
      letter-spacing: 1px;
    }

    p {
      margin: 0;
      font-size: 15px;
      font-weight: 600;
      color: #333;
    }

    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
    }
  }
}

.invoice-customer {
  margin-bottom: 40px;

  h3 {
    margin: 0 0 12px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: #666;
    letter-spacing: 1px;
  }

  .customer-name {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #333;
  }

  .customer-email {
    margin: 5px 0 0;
    font-size: 13px;
    color: #666;
  }
}

.invoice-items-table {
  width: 100%;
  margin-bottom: 30px;
  border-collapse: collapse;

  thead {
    tr {
      background-color: #f8f8f8;
      border-bottom: 2px solid #333;
    }

    th {
      padding: 12px 10px;
      text-align: left;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      color: #333;
      letter-spacing: 0.5px;
    }
  }

  tbody {
    tr {
      border-bottom: 1px solid #ddd;

      &:last-child {
        border-bottom: 2px solid #333;
      }
    }

    td {
      padding: 14px 10px;
      font-size: 13px;
      color: #333;

      &.product-cell {
        display: flex;
        align-items: center;
        gap: 12px;

        .product-image {
          width: 45px;
          height: 45px;
          object-fit: cover;
          border-radius: 4px;
          background-color: #f5f5f5;
        }
      }

      &.text-center {
        text-align: center;
      }

      &.text-right {
        text-align: right;
      }
    }
  }
}

.invoice-totals {
  margin-bottom: 40px;
  padding-top: 15px;
  border-top: 2px solid #333;
  text-align: right;

  .total-row {
    display: flex;
    justify-content: flex-end;
    gap: 60px;
    margin-bottom: 8px;
    font-size: 14px;

    span {
      font-weight: 600;
      color: #666;
    }

    strong {
      min-width: 100px;
      text-align: right;
      font-weight: 700;
      color: #333;
    }

    &.final {
      font-size: 16px;
      padding-top: 10px;
      border-top: 1px solid #ddd;

      span,
      strong {
        color: #27ae60;
        font-weight: 800;
      }
    }
  }
}

.invoice-footer {
  text-align: center;
  padding-top: 30px;
  border-top: 1px solid #ddd;
  color: #666;

  p {
    margin: 8px 0;
    font-size: 13px;
  }

  .text-muted {
    color: #999;
    font-size: 12px;
  }
}

.invoice-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  padding: 20px;
  border-top: 1px solid #eee;
  flex-shrink: 0;
  background-color: #f9f9f9;

  .btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    i {
      font-size: 16px;
    }

    &.btn-primary {
      background-color: #0066cc;
      color: white;
      border: none;

      &:hover {
        background-color: #0052a3;
      }
    }

    &.btn-outline-secondary {
      background-color: white;
      color: #666;
      border: 1px solid #ddd;

      &:hover {
        background-color: #f5f5f5;
        border-color: #999;
      }
    }
  }
}

@media print {
  .invoice-modal-overlay {
    background-color: white;
  }

  .invoice-modal {
    box-shadow: none;
    max-height: 100%;
  }

  .invoice-header,
  .invoice-actions {
    display: none;
  }

  .invoice-content {
    padding: 0;
    max-height: 100%;
  }
}

@media (max-width: 768px) {
  .invoice-modal {
    max-width: calc(100% - 20px);
  }

  .invoice-content {
    padding: 20px;
  }

  .invoice-company h1 {
    font-size: 28px;
  }

  .invoice-info-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .invoice-items-table tbody td.product-cell {
    flex-direction: column;
    gap: 8px;
  }

  .invoice-actions {
    flex-wrap: wrap;
  }
}


@media (max-width: 768px) {
  .orders-header {
    flex-direction: column;
    gap: 12px;

    h1 {
      font-size: 1.5rem;
    }
  }

  .order-header-section {
    flex-direction: column;
    gap: 12px;

    .order-status {
      align-items: flex-start;
    }
  }

  .order-item-row {
    flex-wrap: wrap;
    gap: 12px;
  }

  .item-details {
    width: 100%;
  }

  .item-quantity,
  .item-subtotal {
    width: 50%;
    min-width: auto;
  }

  .order-actions {
    flex-wrap: wrap;
  }
}
</style>

<template>
  <div class="place-order-container">
    <h1>Place Order</h1>

    <!-- Empty Cart Message -->
    <div v-if="cartStore.items.length === 0" class="empty-state">
      <i class="bi bi-bag"></i>
      <p>Your cart is empty</p>
      <RouterLink to="/product" class="btn btn-primary">
        <i class="bi bi-shop"></i> Continue Shopping
      </RouterLink>
    </div>

    <!-- Order Review -->
    <div v-else>
      <!-- User Info -->
      <div class="order-user-info">
        <h2>Order Summary</h2>
        <p><strong>Customer:</strong> {{ authStore.username }}</p>
      </div>

      <!-- Order Items -->
      <div class="order-items">
        <div class="order-item" v-for="item in cartStore.items" :key="item.product.id">
          <!-- Product Image -->
          <div class="item-image">
            <img :src="item.product.thumbnail" :alt="item.product.title" />
          </div>

          <!-- Product Info -->
          <div class="item-info">
            <h5>{{ item.product.title }}</h5>
            <p class="item-description">{{ item.product.description }}</p>
            <p class="item-price">${{ item.product.price.toFixed(2) }}</p>
          </div>

          <!-- Quantity -->
          <div class="item-quantity">
            <p>Qty: <strong>{{ item.quantity }}</strong></p>
          </div>

          <!-- Subtotal -->
          <div class="item-subtotal">
            <p>${{ (item.product.price * item.quantity).toFixed(2) }}</p>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="order-summary">
        <div class="summary-section">
          <h3>Order Total</h3>
          <div class="summary-row">
            <span>Subtotal:</span>
            <span>${{ cartStore.totalPrice.toFixed(2) }}</span>
          </div>
          <div class="summary-row">
            <span>Shipping:</span>
            <span class="free">Free</span>
          </div>
          <div class="summary-row">
            <span>Tax (10%):</span>
            <span>${{ (cartStore.totalPrice * 0.1).toFixed(2) }}</span>
          </div>
          <div class="summary-row total">
            <span>Total Amount:</span>
            <span>${{ (cartStore.totalPrice * 1.1).toFixed(2) }}</span>
          </div>
        </div>

        <div class="delivery-info">
          <h3>Delivery Details</h3>
          <p><strong>Estimated Delivery:</strong> 3-5 business days</p>
          <p><strong>Status:</strong> <span class="status-pending">Pending</span></p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="order-actions">
        <RouterLink to="/cart" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Back to Cart
        </RouterLink>
        <button @click="confirmOrder" :disabled="isLoading" class="btn btn-success btn-lg">
          <i v-if="!isLoading" class="bi bi-check-circle"></i>
          <i v-else class="bi bi-spinner"></i>
          {{ isLoading ? 'Processing...' : 'Confirm Order' }}
        </button>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'
import { useOrderStore } from '../stores/order'
import { useRouter } from 'vue-router'
import { showToast } from '../utils/toast'

const cartStore = useCartStore()
const authStore = useAuthStore()
const orderStore = useOrderStore()
const router = useRouter()

const isLoading = ref(false)
const orderId = ref(null)

async function confirmOrder() {
  if (cartStore.items.length === 0) {
    showToast.warning('Your cart is empty')
    return
  }

  isLoading.value = true

  try {
    // Format items for the API
    const items = cartStore.items.map(item => ({
      product: item.product,
      quantity: item.quantity
    }))

    // Call the backend order API
    const response = await fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.accessToken}`
      },
      body: JSON.stringify({ items })
    })

    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.error || 'Failed to place order')
    }

    const data = await response.json()
    orderId.value = data.order_id

    // Save order to store
    orderStore.addOrder({
      id: data.order_id,
      items: items,
      total_items: data.total_items,
      total_price: data.total_price,
      status: 'pending',
      created_at: new Date().toISOString()
    })

    // Clear cart
    cartStore.clearCart()

    // Show success toast
    showToast.success(`Order #${data.order_id} placed successfully!`)

    // Redirect after 2 seconds
    setTimeout(() => {
      router.push({ name: 'Home' })
    }, 2000)

  } catch (err) {
    showToast.error(err.message)
  } finally {
    isLoading.value = false
  }
}
</script>

<style lang="scss" scoped>
.place-order-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;

  h1 {
    margin-bottom: 30px;
    font-size: 2rem;
    font-weight: 700;
    color: #333;
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

  .btn {
    padding: 10px 20px;
    font-size: 1rem;
  }
}

.order-user-info {
  background: #f0f7ff;
  border-left: 4px solid #007bff;
  padding: 15px 20px;
  border-radius: 6px;
  margin-bottom: 30px;

  h2 {
    margin: 0 0 10px;
    font-size: 1.3rem;
    color: #333;
  }

  p {
    margin: 5px 0;
    font-size: 0.95rem;
    color: #555;
  }
}

.order-items {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 30px;
}

.order-item {
  display: grid;
  grid-template-columns: 100px 1fr 120px 120px;
  gap: 20px;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
  transition: background-color 0.3s ease;

  &:hover {
    background-color: #f9f9f9;
  }

  &:last-child {
    border-bottom: none;
  }
}

.item-image {
  img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
    background-color: #f5f5f5;
  }
}

.item-info {
  h5 {
    margin: 0 0 8px;
    font-size: 1rem;
    font-weight: 600;
    color: #333;
  }

  .item-description {
    margin: 0 0 8px;
    font-size: 0.85rem;
    color: #666;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .item-price {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #27ae60;
  }
}

.item-quantity {
  text-align: center;

  p {
    margin: 0;
    font-size: 0.95rem;
    color: #555;
  }
}

.item-subtotal {
  text-align: right;

  p {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #333;
  }
}

.order-summary {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 30px;

  .summary-section,
  .delivery-info {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;

    h3 {
      margin: 0 0 15px;
      font-size: 1.1rem;
      font-weight: 600;
      color: #333;
      border-bottom: 2px solid #ddd;
      padding-bottom: 10px;
    }
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 0.95rem;
    border-bottom: 1px solid #eee;

    &.total {
      border-bottom: none;
      font-size: 1.2rem;
      font-weight: 700;
      color: #27ae60;
      padding-top: 10px;
      margin-top: 10px;
      border-top: 2px solid #27ae60;
    }

    .free {
      color: #27ae60;
      font-weight: 600;
    }
  }

  .delivery-info {
    p {
      margin: 8px 0;
      font-size: 0.95rem;
      color: #555;

      .status-pending {
        display: inline-block;
        background-color: #ffc107;
        color: #333;
        padding: 3px 8px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
      }
    }
  }
}

.order-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-bottom: 20px;

  .btn {
    padding: 10px 20px;
    font-size: 0.95rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;

    &.btn-secondary {
      background-color: #6c757d;
      color: white;

      &:hover {
        background-color: #5a6268;
      }
    }

    &.btn-success {
      background-color: #28a745;
      color: white;

      &:hover:not(:disabled) {
        background-color: #218838;
      }

      &:disabled {
        background-color: #9dc584;
        cursor: not-allowed;
        opacity: 0.7;
      }
    }

    &.btn-lg {
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 600;
    }

    i {
      display: inline-block;

      &.bi-spinner {
        animation: spin 1s linear infinite;
      }
    }
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .order-item {
    grid-template-columns: 80px 1fr 60px;
    gap: 10px;
    padding: 15px;
  }

  .item-image {
    img {
      height: 80px;
    }
  }

  .item-quantity,
  .item-subtotal {
    grid-column: 2 / 4;
    display: flex;
    justify-content: space-between;
  }

  .order-summary {
    grid-template-columns: 1fr;
  }

  .order-actions {
    flex-direction: column;
  }
}
</style>

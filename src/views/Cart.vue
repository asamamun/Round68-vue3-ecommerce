<template>
  <div class="cart-container">
    <h1>Shopping Cart</h1>
    
    <!-- Empty Cart Message -->
    <div v-if="cartStore.items.length === 0" class="empty-cart">
      <i class="bi bi-bag"></i>
      <p>Your cart is empty</p>
      <RouterLink to="/product" class="btn btn-primary">
        <i class="bi bi-shop"></i> Continue Shopping
      </RouterLink>
    </div>

    <!-- Cart Items -->
    <div v-else>
      <div class="cart-items">
        <div class="cart-item" v-for="item in cartStore.items" :key="item.product.id">
          <!-- Product Image -->
          <div class="item-image">
            <img :src="item.product.thumbnail" :alt="item.product.title" />
          </div>

          <!-- Product Info -->
          <div class="item-info">
            <h5>{{ item.product.title }}</h5>
            <p class="item-description">{{ item.product.description }}</p>
            <p class="item-price">${{ item.product.price }}</p>
          </div>

          <!-- Quantity Control -->
          <div class="item-quantity">
            <label for="qty">Qty:</label>
            <button @click="decreaseQuantity(item.product.id)" class="qty-btn">
              <i class="bi bi-dash"></i>
            </button>
            <input 
              type="number" 
              v-model.number="item.quantity" 
              @change="updateQty(item.product.id, item.quantity)"
              class="qty-input"
              min="1"
            />
            <button @click="increaseQuantity(item.product.id)" class="qty-btn">
              <i class="bi bi-plus"></i>
            </button>
          </div>

          <!-- Subtotal -->
          <div class="item-subtotal">
            <p>${{ (item.product.price * item.quantity).toFixed(2) }}</p>
          </div>

          <!-- Remove Button -->
          <div class="item-remove">
            <button @click="removeItem(item.product.id)" class="btn-remove" title="Remove from cart">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Cart Summary -->
      <div class="cart-summary">
        <div class="summary-row">
          <span>Subtotal:</span>
          <span>${{ cartStore.totalPrice.toFixed(2) }}</span>
        </div>
        <div class="summary-row">
          <span>Shipping:</span>
          <span>Free</span>
        </div>
        <div class="summary-row">
          <span>Tax (10%):</span>
          <span>${{ (cartStore.totalPrice * 0.1).toFixed(2) }}</span>
        </div>
        <div class="summary-row total">
          <span>Total:</span>
          <span>${{ (cartStore.totalPrice * 1.1).toFixed(2) }}</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="cart-actions">
        <RouterLink to="/product" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Continue Shopping
        </RouterLink>
        <button @click="emptyCart" class="btn btn-outline-danger">
          <i class="bi bi-trash"></i> Empty Cart
        </button>
        <button @click="placeOrder" class="btn btn-success btn-lg">
          <i class="bi bi-bag-check"></i> Place Order
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { showToast } from '../utils/toast'

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()

function increaseQuantity(productId) {
  const item = cartStore.items.find(i => i.product.id === productId)
  if (item) {
    cartStore.updateQuantity(productId, item.quantity + 1)
    showToast.info('Quantity increased')
  }
}

function decreaseQuantity(productId) {
  const item = cartStore.items.find(i => i.product.id === productId)
  if (item && item.quantity > 1) {
    cartStore.updateQuantity(productId, item.quantity - 1)
    showToast.info('Quantity decreased')
  }
}

function updateQty(productId, quantity) {
  if (quantity > 0) {
    cartStore.updateQuantity(productId, quantity)
    showToast.info('Quantity updated')
  }
}

function removeItem(productId) {
  cartStore.removeItem(productId)
  showToast.warning('Item removed from cart')
}

function emptyCart() {
  if (confirm('Are you sure you want to empty your cart?')) {
    cartStore.clearCart()
    showToast.info('Cart emptied')
  }
}

function placeOrder() {
  if (!authStore.isAuthenticated) {
    showToast.warning('Please sign in to place an order')
    router.push({ name: 'Login', query: { redirect: '/place-order' } })
    return
  }
  showToast.success('Proceeding to checkout...')
  router.push({ name: 'PlaceOrder' })
}
</script>

<style lang="scss" scoped>
.cart-container {
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

.empty-cart {
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

.cart-items {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 30px;
}

.cart-item {
  display: grid;
  grid-template-columns: 100px 1fr 150px 120px 120px 60px;
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
    font-size: 1.1rem;
    font-weight: 600;
    color: #27ae60;
  }
}

.item-quantity {
  display: flex;
  align-items: center;
  gap: 8px;

  label {
    font-size: 0.9rem;
    font-weight: 500;
    white-space: nowrap;
  }

  .qty-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;

    &:hover {
      background-color: #f0f0f0;
      border-color: #999;
    }

    &:active {
      background-color: #e0e0e0;
    }
  }

  .qty-input {
    width: 50px;
    padding: 6px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;

    &:focus {
      outline: none;
      border-color: #27ae60;
      box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
    }
  }
}

.item-subtotal {
  text-align: right;

  p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
  }
}

.item-remove {
  text-align: center;

  .btn-remove {
    width: 40px;
    height: 40px;
    padding: 0;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease;

    &:hover {
      background-color: #c82333;
    }

    &:active {
      background-color: #bd2130;
    }
  }
}

.cart-summary {
  background: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  max-width: 400px;
  margin-left: auto;

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
      padding-top: 15px;
    }
  }
}

.cart-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;

  .btn {
    padding: 10px 20px;
    font-size: 0.95rem;
    border-radius: 6px;
    transition: all 0.3s ease;

    &.btn-secondary {
      background-color: #6c757d;
      color: white;

      &:hover {
        background-color: #5a6268;
      }
    }

    &.btn-outline-danger {
      border: 1px solid #dc3545;
      color: #dc3545;
      background: white;

      &:hover {
        background-color: #dc3545;
        color: white;
      }
    }

    &.btn-lg {
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 600;
    }
  }
}

@media (max-width: 1024px) {
  .cart-item {
    grid-template-columns: 80px 1fr 100px 50px;
    gap: 15px;

    .item-quantity {
      grid-column: 3 / 5;
    }

    .item-subtotal,
    .item-remove {
      grid-column: span 1;
    }
  }
}

@media (max-width: 768px) {
  .cart-item {
    grid-template-columns: 1fr;
    gap: 10px;
    padding: 15px;
  }

  .item-image {
    img {
      height: 150px;
    }
  }

  .item-quantity,
  .item-subtotal,
  .item-remove {
    grid-column: auto;
  }

  .cart-summary {
    max-width: 100%;
  }

  .cart-actions {
    flex-direction: column;
  }
}
</style>
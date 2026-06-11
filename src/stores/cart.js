// src/stores/cart.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
  // ── State ────────────────────────────────────────────
  const items = ref([])   // [{ product, quantity }]

  // ── Getters ──────────────────────────────────────────
  const totalItems = computed(() =>
    items.value.reduce((sum, i) => sum + i.quantity, 0)
  )

  const totalPrice = computed(() =>
    items.value.reduce((sum, i) => sum + i.product.price * i.quantity, 0)
  )

  function isInCart(productId) {
    return items.value.some(i => i.product.id === productId)
  }

  // ── Actions ──────────────────────────────────────────
  function addItem(product, quantity = 1) {
    const existing = items.value.find(i => i.product.id === product.id)
    if (existing) {
      existing.quantity += quantity
    } else {
      items.value.push({ product, quantity })
    }
  }

  function removeItem(productId) {
    items.value = items.value.filter(i => i.product.id !== productId)
  }

  function updateQuantity(productId, quantity) {
    if (quantity <= 0) { removeItem(productId); return }
    const item = items.value.find(i => i.product.id === productId)
    if (item) item.quantity = quantity
  }

  function clearCart() {
    items.value = []
  }

  return {
    items, totalItems, totalPrice, isInCart,
    addItem, removeItem, updateQuantity, clearCart
  }
}, {
  persist: true   // requires pinia-plugin-persistedstate
})

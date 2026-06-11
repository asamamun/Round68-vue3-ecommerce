// src/stores/order.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/'

export const useOrderStore = defineStore('order', () => {
  // ── State ────────────────────────────────────────────
  const orders = ref([])           // [{ id, items, total_items, total_price, status, created_at }, ...]
  const loading = ref(false)
  const error = ref(null)

  // ── Getters ──────────────────────────────────────────
  const totalOrders = computed(() => orders.value.length)
  const totalSpent = computed(() =>
    orders.value.reduce((sum, o) => sum + (o.total_price || 0), 0)
  )

  function getOrderById(orderId) {
    return orders.value.find(o => o.id === orderId)
  }

  // ── Actions ──────────────────────────────────────────
  function addOrder(order) {
    orders.value.unshift(order)
  }

  function updateOrder(orderId, updates) {
    const order = getOrderById(orderId)
    if (order) {
      Object.assign(order, updates)
    }
  }

  function removeOrder(orderId) {
    orders.value = orders.value.filter(o => o.id !== orderId)
  }

  async function fetchOrders(authStore) {
    loading.value = true
    error.value = null
    try {
      const response = await fetch(
        `${API_URL}order.php`,
        {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${authStore.accessToken}`
          }
        }
      )

      if (!response.ok) {
        throw new Error('Failed to fetch orders')
      }

      const data = await response.json()
      orders.value = Array.isArray(data) ? data : []
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  function clearOrders() {
    orders.value = []
  }

  return {
    orders, loading, error,
    totalOrders, totalSpent,
    getOrderById,
    addOrder, updateOrder, removeOrder,
    fetchOrders, clearOrders
  }
}, {
  persist: true   // requires pinia-plugin-persistedstate
})

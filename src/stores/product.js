// src/stores/product.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useProductStore = defineStore('product', () => {
  // ── State ────────────────────────────────────────────
  const products = ref([])
  const selectedProduct = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // ── Getters ──────────────────────────────────────────
  const totalProducts = computed(() => products.value.length)

  function getById(id) {
    return products.value.find(p => p.id === Number(id)) ?? null
  }

  // ── Actions ──────────────────────────────────────────
  async function fetchProducts() {
    if (products.value.length) return   // already loaded, skip re-fetch

    loading.value = true
    error.value = null
    try {
      const res = await fetch('https://dummyjson.com/products')
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const data = await res.json()
      products.value = data.products
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  async function fetchProductById(id) {
    // Use cache first
    const cached = getById(id)
    if (cached) { selectedProduct.value = cached; return }

    loading.value = true
    error.value = null
    try {
      const res = await fetch(`https://dummyjson.com/products/${id}`)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      selectedProduct.value = await res.json()
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  return {
    products, selectedProduct, loading, error,
    totalProducts, getById,
    fetchProducts, fetchProductById
  }
})

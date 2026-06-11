// src/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/'

export const useAuthStore = defineStore('auth', () => {
  // ── State ────────────────────────────────────────────
  const accessToken = ref(null)      // JWT access token
  const refreshToken = ref(null)     // JWT refresh token
  const user = ref(null)             // { id, username, email }
  const loading = ref(false)
  const error = ref(null)
  const expiresIn = ref(null)        // Access token expiry in seconds

  // ── Getters ──────────────────────────────────────────
  const isAuthenticated = computed(() => !!accessToken.value)
  const userId = computed(() => user.value?.id ?? null)
  const username = computed(() => user.value?.username ?? '')
  const email = computed(() => user.value?.email ?? '')
  const role = computed(() => user.value?.role ?? 'user')
  const isAdmin = computed(() => role.value === 'admin')
  const token = computed(() => accessToken.value)  // For backwards compatibility

  // ── Actions ──────────────────────────────────────────
  async function login(username, password) {
    loading.value = true
    error.value = null
    try {
      const res = await fetch(`${API_URL}login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      })

      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || 'Login failed')
      }

      const data = await res.json()
      // data: { access_token, refresh_token, expires_in, user: { id, username, email } }
      
      accessToken.value = data.access_token
      refreshToken.value = data.refresh_token
      user.value = data.user
      expiresIn.value = data.expires_in
      error.value = null
      
      return true
    } catch (err) {
      error.value = err.message
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(username, email, password) {
    loading.value = true
    error.value = null
    try {
      const res = await fetch(`${API_URL}register.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, email, password })
      })

      if (!res.ok) {
        const data = await res.json()
        // Handle field-specific errors
        if (data.errors) {
          error.value = Object.values(data.errors)[0]
          return { success: false, errors: data.errors }
        }
        throw new Error(data.error || 'Registration failed')
      }

      const data = await res.json()
      // data: { message, access_token, refresh_token, expires_in, user: { id, username, email } }
      
      accessToken.value = data.access_token
      refreshToken.value = data.refresh_token
      user.value = data.user
      expiresIn.value = data.expires_in
      error.value = null
      
      return { success: true }
    } catch (err) {
      error.value = err.message
      return { success: false }
    } finally {
      loading.value = false
    }
  }

  async function refreshAccessToken() {
    if (!refreshToken.value) return false
    
    try {
      const res = await fetch(`${API_URL}refresh.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: refreshToken.value })
      })

      if (!res.ok) {
        logout()
        return false
      }

      const data = await res.json()
      accessToken.value = data.access_token
      refreshToken.value = data.refresh_token
      expiresIn.value = data.expires_in
      
      return true
    } catch (err) {
      logout()
      return false
    }
  }

  function logout() {
    accessToken.value = null
    refreshToken.value = null
    user.value = null
    expiresIn.value = null
    error.value = null
  }

  function clearError() {
    error.value = null
  }

  // Restore user from persisted storage if available
  function restoreSession() {
    // This will be called on app init to restore from localStorage
    // Pinia's persist plugin handles this automatically
  }

  return {
    // State
    accessToken, refreshToken, user, loading, error, expiresIn,
    
    // Getters
    isAuthenticated, userId, username, email, role, isAdmin, token,
    
    // Actions
    login, register, logout, clearError,
    refreshAccessToken, restoreSession
  }
}, {
  persist: {
    enabled: true,
    strategies: [
      {
        key: 'auth',
        storage: localStorage,
        paths: ['accessToken', 'refreshToken', 'user', 'expiresIn']
      }
    ]
  }
})

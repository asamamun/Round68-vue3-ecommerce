// src/utils/apiClient.js
/**
 * HTTP Client with automatic JWT token management
 * Automatically attaches Authorization header to all requests
 * Handles token refresh on 401 responses
 */

import { useAuthStore } from '../stores/auth'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/'

class ApiClient {
  constructor() {
    this.baseUrl = API_URL
    this.defaultHeaders = {
      'Content-Type': 'application/json'
    }
  }

  /**
   * Get authorization headers with current access token
   */
  getAuthHeaders() {
    const authStore = useAuthStore()
    const headers = { ...this.defaultHeaders }
    
    if (authStore.accessToken) {
      headers['Authorization'] = `Bearer ${authStore.accessToken}`
    }
    
    return headers
  }

  /**
   * Make an API request with automatic token management
   */
  async request(endpoint, options = {}) {
    const url = this.baseUrl + endpoint
    const headers = this.getAuthHeaders()
    
    const config = {
      ...options,
      headers: {
        ...headers,
        ...(options.headers || {})
      }
    }

    try {
      let response = await fetch(url, config)

      // Handle 401 Unauthorized - try to refresh token
      if (response.status === 401) {
        const authStore = useAuthStore()
        
        // Try to refresh the token
        if (await authStore.refreshAccessToken()) {
          // Retry the request with new token
          const newHeaders = this.getAuthHeaders()
          config.headers = {
            ...this.defaultHeaders,
            ...(options.headers || {}),
            ...newHeaders
          }
          response = await fetch(url, config)
        } else {
          // Token refresh failed, logout user
          authStore.logout()
        }
      }

      return response
    } catch (error) {
      console.error('API Request Error:', error)
      throw error
    }
  }

  /**
   * GET request
   */
  async get(endpoint, options = {}) {
    return this.request(endpoint, {
      ...options,
      method: 'GET'
    })
  }

  /**
   * POST request
   */
  async post(endpoint, data, options = {}) {
    return this.request(endpoint, {
      ...options,
      method: 'POST',
      body: JSON.stringify(data)
    })
  }

  /**
   * PUT request
   */
  async put(endpoint, data, options = {}) {
    return this.request(endpoint, {
      ...options,
      method: 'PUT',
      body: JSON.stringify(data)
    })
  }

  /**
   * DELETE request
   */
  async delete(endpoint, options = {}) {
    return this.request(endpoint, {
      ...options,
      method: 'DELETE'
    })
  }

  /**
   * PATCH request
   */
  async patch(endpoint, data, options = {}) {
    return this.request(endpoint, {
      ...options,
      method: 'PATCH',
      body: JSON.stringify(data)
    })
  }
}

export const apiClient = new ApiClient()

/**
 * Composable for using the API client in Vue components
 */
export function useApi() {
  return {
    get: (endpoint, options) => apiClient.get(endpoint, options),
    post: (endpoint, data, options) => apiClient.post(endpoint, data, options),
    put: (endpoint, data, options) => apiClient.put(endpoint, data, options),
    delete: (endpoint, options) => apiClient.delete(endpoint, options),
    patch: (endpoint, data, options) => apiClient.patch(endpoint, data, options),
  }
}

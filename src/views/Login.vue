<template>
  <div class="login-container">
    <div class="login-box">
      <div class="login-header">
        <i class="bi bi-person-lock"></i>
        <h1>Sign In</h1>
        <p>Access your account</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="login-form">
        <!-- Username Field -->
        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <input
            id="username"
            v-model="form.username"
            type="text"
            class="form-control"
            placeholder="Enter your username"
            required
            :disabled="authStore.loading"
          />
          <small class="form-text">Enter the username you registered with</small>
        </div>

        <!-- Password Field -->
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="password-wrapper">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-control"
              placeholder="Enter your password"
              required
              :disabled="authStore.loading"
            />
            <button
              type="button"
              class="toggle-password"
              @click="showPassword = !showPassword"
              :disabled="authStore.loading"
            >
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="btn btn-primary btn-lg w-100"
          :disabled="authStore.loading"
        >
          <i v-if="!authStore.loading" class="bi bi-box-arrow-in-right"></i>
          <i v-else class="bi bi-spinner"></i>
          {{ authStore.loading ? 'Signing In...' : 'Sign In' }}
        </button>
      </form>

      <!-- Divider -->
      <div class="divider">
        <span>Don't have an account?</span>
      </div>

      <!-- Register Link -->
      <RouterLink to="/register" class="btn btn-outline-secondary btn-lg w-100">
        <i class="bi bi-person-plus"></i>
        Create Account
      </RouterLink>

      <!-- Additional Info -->
      <div class="login-footer">
        <p>
          <i class="bi bi-shield-lock"></i>
          Your login is secure and encrypted
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter, useRoute } from 'vue-router'
import { showToast } from '../utils/toast'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({
  username: '',
  password: ''
})

const showPassword = ref(false)

async function handleLogin() {
  // Validate form
  if (!form.username.trim() || !form.password.trim()) {
    showToast.warning('Please enter both username and password')
    return
  }

  // Attempt login
  const success = await authStore.login(form.username, form.password)

  if (success) {
    showToast.success(`Welcome back, ${authStore.username}!`)
    // Get redirect URL from query params or default to home
    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } else {
    showToast.error(authStore.error || 'Login failed')
  }
}
</script>

<style lang="scss" scoped>
.login-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.login-box {
  background: white;
  border-radius: 12px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 100%;
  max-width: 400px;
  animation: slideUp 0.5s ease;
}

.login-header {
  text-align: center;
  margin-bottom: 30px;

  i {
    font-size: 3rem;
    color: #667eea;
    display: block;
    margin-bottom: 15px;
  }

  h1 {
    margin: 0 0 8px;
    font-size: 1.8rem;
    color: #333;
    font-weight: 700;
  }

  p {
    margin: 0;
    font-size: 0.95rem;
    color: #666;
  }
}

.alert {
  padding: 12px 15px;
  margin-bottom: 20px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.9rem;
  animation: slideDown 0.3s ease;

  &.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;

    i {
      color: #dc3545;
      flex-shrink: 0;
    }
  }

  .close-btn {
    background: none;
    border: none;
    color: inherit;
    font-size: 1.5rem;
    cursor: pointer;
    margin-left: auto;
    padding: 0;
    opacity: 0.7;

    &:hover {
      opacity: 1;
    }
  }
}

.login-form {
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;

  &:last-of-type {
    margin-bottom: 25px;
  }
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  font-family: inherit;

  &:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background-color: #f8faff;
  }

  &:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
    opacity: 0.6;
  }
}

.form-text {
  display: block;
  margin-top: 5px;
  font-size: 0.8rem;
  color: #999;
}

.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;

  .form-control {
    padding-right: 40px;
  }

  .toggle-password {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: #667eea;
    font-size: 1.1rem;
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;

    &:hover:not(:disabled) {
      color: #764ba2;
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
}

.btn {
  padding: 12px 20px;
  font-size: 0.95rem;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;

  &.btn-primary {
    background-color: #667eea;
    color: white;

    &:hover:not(:disabled) {
      background-color: #5568d3;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    &:disabled {
      background-color: #9eb3f5;
      cursor: not-allowed;
    }

    i.bi-spinner {
      animation: spin 1s linear infinite;
    }
  }

  &.btn-outline-secondary {
    background-color: white;
    color: #666;
    border: 2px solid #ddd;

    &:hover:not(:disabled) {
      background-color: #f5f5f5;
      border-color: #667eea;
      color: #667eea;
    }

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }

  &.btn-lg {
    padding: 14px 24px;
    font-size: 1rem;
  }

  &.w-100 {
    width: 100%;
  }
}

.divider {
  text-align: center;
  margin: 25px 0;
  font-size: 0.9rem;
  color: #999;

  span {
    background-color: white;
    padding: 0 10px;
    position: relative;
    z-index: 1;
  }

  &::before {
    content: '';
    display: block;
    height: 1px;
    background-color: #ddd;
    position: absolute;
    width: 100%;
    top: 50%;
    left: 0;
    z-index: 0;
  }

  &::before {
    position: relative;
    display: none;
  }
}

.login-footer {
  text-align: center;
  margin-top: 20px;

  p {
    margin: 0;
    font-size: 0.85rem;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;

    i {
      color: #28a745;
    }
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
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

@media (max-width: 480px) {
  .login-box {
    padding: 30px 20px;
    border-radius: 8px;
  }

  .login-header h1 {
    font-size: 1.5rem;
  }

  .form-control {
    font-size: 16px; // Prevents zoom on iOS
  }
}
</style>
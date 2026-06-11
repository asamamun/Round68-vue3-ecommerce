<template>
  <div class="register-container">
    <div class="register-box">
      <div class="register-header">
        <i class="bi bi-person-plus"></i>
        <h1>Create Account</h1>
        <p>Join us today</p>
      </div>

      <!-- Registration Form -->
      <form @submit.prevent="handleRegister" class="register-form">
        <!-- Username Field -->
        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <input
            id="username"
            v-model="form.username"
            type="text"
            class="form-control"
            placeholder="Choose a username (3+ chars)"
            required
            minlength="3"
            :disabled="authStore.loading"
          />
          <small class="form-text">Minimum 3 characters, alphanumeric</small>
        </div>

        <!-- Email Field -->
        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-control"
            placeholder="your@email.com"
            required
            :disabled="authStore.loading"
          />
          <small class="form-text">We'll never share your email</small>
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
              placeholder="Create a strong password (8+ chars)"
              required
              minlength="8"
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
          <small class="form-text">Minimum 8 characters with uppercase, lowercase, and numbers</small>
        </div>

        <!-- Confirm Password Field -->
        <div class="form-group">
          <label for="confirmPassword" class="form-label">Confirm Password</label>
          <div class="password-wrapper">
            <input
              id="confirmPassword"
              v-model="form.confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="form-control"
              placeholder="Confirm your password"
              required
              :disabled="authStore.loading"
            />
            <button
              type="button"
              class="toggle-password"
              @click="showConfirmPassword = !showConfirmPassword"
              :disabled="authStore.loading"
            >
              <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
          <small v-if="passwordMismatch" class="form-text error">
            Passwords do not match
          </small>
        </div>

        <!-- Terms Checkbox -->
        <div class="form-group checkbox-group">
          <input
            id="terms"
            v-model="form.agreeToTerms"
            type="checkbox"
            class="form-checkbox"
            required
            :disabled="authStore.loading"
          />
          <label for="terms" class="checkbox-label">
            I agree to the Terms of Service and Privacy Policy
          </label>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="btn btn-primary btn-lg w-100"
          :disabled="authStore.loading || passwordMismatch || !form.agreeToTerms"
        >
          <i v-if="!authStore.loading" class="bi bi-person-plus"></i>
          <i v-else class="bi bi-spinner"></i>
          {{ authStore.loading ? 'Creating Account...' : 'Create Account' }}
        </button>
      </form>

      <!-- Divider -->
      <div class="divider">
        <span>Already have an account?</span>
      </div>

      <!-- Login Link -->
      <RouterLink to="/login" class="btn btn-outline-secondary btn-lg w-100">
        <i class="bi bi-box-arrow-in-right"></i>
        Sign In
      </RouterLink>

      <!-- Security Info -->
      <div class="register-footer">
        <p>
          <i class="bi bi-shield-lock"></i>
          Your data is encrypted and secure
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { showToast } from '../utils/toast'

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  agreeToTerms: false
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const passwordMismatch = computed(() => 
  form.password && form.confirmPassword && form.password !== form.confirmPassword
)

async function handleRegister() {
  // Validate form
  if (!form.username.trim() || !form.email.trim() || !form.password.trim()) {
    showToast.warning('Please fill in all fields')
    return
  }

  if (form.password.length < 8) {
    showToast.warning('Password must be at least 8 characters')
    return
  }

  if (form.password !== form.confirmPassword) {
    showToast.error('Passwords do not match')
    return
  }

  if (!form.agreeToTerms) {
    showToast.warning('Please agree to the Terms of Service')
    return
  }

  // Attempt registration
  const result = await authStore.register(form.username, form.email, form.password)

  if (result.success) {
    showToast.success('Account created successfully! Redirecting...')
    
    // Redirect after 2 seconds
    setTimeout(() => {
      router.push({ name: 'Home' })
    }, 2000)
  } else {
    // Show field-specific errors if available
    if (result.errors) {
      Object.values(result.errors).forEach(error => {
        showToast.error(error)
      })
    } else {
      showToast.error('Registration failed')
    }
  }
}
</script>

<style lang="scss" scoped>
.register-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.register-box {
  background: white;
  border-radius: 12px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 100%;
  max-width: 450px;
  animation: slideUp 0.5s ease;
}

.register-header {
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

  &.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;

    i {
      color: #28a745;
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

.register-form {
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 18px;

  &:last-of-type {
    margin-bottom: 22px;
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

  &.error {
    color: #dc3545;
  }
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

.checkbox-group {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 20px;
}

.form-checkbox {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  cursor: pointer;
  accent-color: #667eea;
  flex-shrink: 0;
}

.checkbox-label {
  font-size: 0.9rem;
  color: #555;
  cursor: pointer;
  line-height: 1.4;

  &:hover {
    color: #333;
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
      opacity: 0.7;
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
}

.register-footer {
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
  .register-box {
    padding: 30px 20px;
    border-radius: 8px;
  }

  .register-header h1 {
    font-size: 1.5rem;
  }

  .form-control {
    font-size: 16px; // Prevents zoom on iOS
  }

  .checkbox-group {
    gap: 8px;
  }
}
</style>
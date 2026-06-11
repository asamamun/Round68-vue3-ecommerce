<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { useCartStore } from './stores/cart'
import { showToast } from './utils/toast'

const authStore = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const username = computed(() => authStore.username)

function handleLogout() {
  authStore.logout()
  showToast.info(`Goodbye, ${username.value}!`)
  router.push({ name: 'Home' })
}

// remove cart item with toast notification
function removeItem(productId) {
  const item = cartStore.items.find(i => i.product?.id === productId)
  const productTitle = item?.product?.title || 'Item'
  cartStore.removeItem(productId)
  showToast.warning(`${productTitle} removed from cart`)
}
</script>

<template>
  <div class="container">
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Navbar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <!-- <a class="nav-link active" aria-current="page" href="#">Home</a> -->
          <RouterLink to="/" class="nav-link active" aria-current="page">Home</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink to="/product" class="nav-link">Products</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink to="/cart" class="nav-link">Cart <span v-if="cartStore.totalItems > 0">({{ cartStore.totalItems }})</span></RouterLink>
        </li>
        <li v-if="isAuthenticated" class="nav-item">
          <RouterLink to="/orders" class="nav-link">
            <i class="bi bi-receipt"></i> Orders
          </RouterLink>
        </li>
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <!-- Logged In - Show Username Dropdown -->
        <li v-if="isAuthenticated" class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> {{ username }}
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" disabled>
                <i class="bi bi-envelope"></i> {{ authStore.user?.email }}
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="#" @click="handleLogout">
                <i class="bi bi-box-arrow-right"></i> Logout
              </a>
            </li>
          </ul>
        </li>

        <!-- Not Logged In - Show Login/Register -->
        <li v-else class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person"></i> Account
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <RouterLink to="/login" class="dropdown-item">
                <i class="bi bi-box-arrow-in-right"></i> Login
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/register" class="dropdown-item">
                <i class="bi bi-person-plus"></i> Register
              </RouterLink>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<hr>
<div class="row">
  <div class="col-md-4">
    <h3>Cart(<span v-if="cartStore.items.length">{{ cartStore.totalItems  }}</span> <span v-else>0</span>) items</h3>
    <ul v-if="cartStore.items.length">
      <li v-for="item in cartStore.items" :key="item.product?.id || Math.random()">       
        <span v-if="item.product">{{ item.product.title }} - ${{ item.product.price }}</span>
        <span v-else>Product unavailable</span>
        <button @click="removeItem(item.product?.id)" class="btn-remove" title="Remove from cart" v-if="item.product">
          <i class="bi bi-trash"></i>
        </button>
      </li>
    </ul>
  </div>
  <div class="col-md-8">
    <RouterView />
  </div>
</div>


</div>
</template>

<style scoped>
.container {
  padding: 20px;
}

h3 {
  margin-bottom: 20px;
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  margin-bottom: 10px;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  transition: background-color 0.3s ease;
}

li:hover {
  background-color: #e9ecef;
}

.btn-remove {
  padding: 6px 10px;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-remove:hover {
  background-color: #c82333;
}

.btn-remove:active {
  background-color: #bd2130;
}

/* Navbar dropdown styling */
.nav-link {
  display: flex;
  align-items: center;
  gap: 8px;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: #0d6efd;
}

.dropdown-menu-end {
  right: 0;
  left: auto;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.2s ease;
}

.dropdown-item:hover {
  background-color: #f0f0f0;
}

.dropdown-item[disabled] {
  color: #6c757d;
  cursor: default;
  opacity: 0.7;
}

.dropdown-item i {
  width: 18px;
  text-align: center;
}
</style>
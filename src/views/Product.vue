<template>
    <div>
        <h1>Product Page</h1>
        <div class="row" v-if="!productId">
            
            <div class="col-md-4 card" v-for="product in productStore.products" :key="product.id">                
  <img :src="product.thumbnail" class="card-img-top" :alt="product.title">
  <div class="card-body">
    <RouterLink :to="`/product/${product.id}`">
    <h5 class="card-title">{{ product.title }}</h5>
    <p class="card-text">{{ product.description }}</p>
    </RouterLink>
    <a class="btn btn-primary" @click="addToCart(product)"><i class="bi bi-cart3"></i></a>
  </div>
</div>
</div>   
<div class="row" v-else>
               <div class="col-md-12 card" v-if="productStore.selectedProduct">                
  <img :src="productStore.selectedProduct.thumbnail" class="card-img-top" :alt="productStore.selectedProduct.title">
  <div class="card-body">
    <h5 class="card-title">{{ productStore.selectedProduct.title }}</h5>
    <p class="card-text">{{ productStore.selectedProduct.description }}</p>
    <a class="btn btn-primary" @click="addToCart(productStore.selectedProduct)"><i class="bi bi-cart3"></i></a>
    <RouterLink to="/product" class="btn btn-info m-3"><i class="bi bi-backspace"></i></RouterLink>
  </div>
</div>  
</div>

<main>
    <!-- Product list -->
    <template v-if="!productId">
      <h1>All Products</h1>
      <p v-if="productStore.loading">Loading…</p>
      <p v-else-if="productStore.error" class="error">{{ productStore.error }}</p>
      <ul v-else>
        <li v-for="product in productStore.products" :key="product.id">
          <RouterLink :to="`/product/${product.id}`">{{ product.title }}</RouterLink>
          — ${{ product.price }}
          <button @click="addToCart(product)">Add to cart</button>
        </li>
      </ul>
    </template>
 
    <!-- Single product -->
    <template v-else>
      <p v-if="productStore.loading">Loading…</p>
      <template v-else-if="productStore.selectedProduct">
        <h1>{{ productStore.selectedProduct.title }}</h1>
        <p>${{ productStore.selectedProduct.price }}</p>
        <button @click="addToCart(productStore.selectedProduct)">
          Add to cart ({{ cartStore.totalItems }})
        </button>
      </template>
      <RouterLink to="/product">← Back to products</RouterLink>
    </template>
  </main>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProductStore } from '../stores/product'
import { useCartStore } from '../stores/cart'
import { showToast } from '../utils/toast'
 
const route = useRoute()
const productStore = useProductStore()
const cartStore = useCartStore()
 
const productId = computed(() => route.params.id)

// Add item to cart with toast notification
function addToCart(product) {
  cartStore.addItem(product)
  showToast.success(`${product.title} added to cart!`)
}
 
// Fetch on load and when the id param changes
watch(
  productId,
  (id) => {
    if (id) productStore.fetchProductById(id)
    else productStore.fetchProducts()
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>

</style>
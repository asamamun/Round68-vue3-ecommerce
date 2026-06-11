# Developer Reference Guide

## Quick Navigation

### 📁 Project Structure Overview

```
/class08/routing/
├── Frontend (Vue 3)
│   ├── src/
│   │   ├── views/          # Page components
│   │   ├── stores/         # Pinia state management
│   │   ├── router/         # Vue Router configuration
│   │   ├── components/     # Reusable components
│   │   ├── utils/          # Helper functions
│   │   ├── App.vue         # Root component
│   │   └── main.js         # Entry point
│   ├── index.html          # HTML entry
│   ├── vite.config.js      # Vite configuration
│   └── package.json        # Dependencies
│
├── Backend (PHP)
│   ├── apis/
│   │   ├── .env            # Configuration
│   │   ├── bootstrap.php   # App initialization
│   │   ├── db.php          # Database queries
│   │   ├── login.php       # Login endpoint
│   │   ├── register.php    # Register endpoint
│   │   ├── order.php       # Orders endpoints
│   │   ├── refresh.php     # Token refresh
│   │   └── src/
│   │       ├── Auth/       # Authentication logic
│   │       └── Controllers/# Business logic
│   └── db/
│       └── r68_vue3_shop.sql  # Database schema
│
└── Documentation
    ├── README.md
    ├── IMPLEMENTATION_STATUS.md
    ├── TESTING_GUIDE.md
    └── DEVELOPER_REFERENCE.md
```

---

## Core Concepts

### 1. Authentication Flow

```
User → Register/Login → Backend verifies → JWT tokens issued
                         ↓
                    Access Token (15 min)
                    Refresh Token (7 days)
                         ↓
                    Stored in localStorage
                         ↓
User → Protected Route → Check auth guard → Fetch with Bearer token
```

### 2. Authorization (Role-Based Access)

```
User has role: 'user' or 'admin'
                ↓
         Stored in JWT token
                ↓
    Frontend: Use auth.isAdmin
    Backend: Check $payload->role
                ↓
    Admin-only features show/execute
```

### 3. State Management (Pinia)

```
stores/auth.js      → User auth state + methods (login, register, logout)
stores/cart.js      → Shopping cart items
stores/product.js   → Product list and filters
stores/order.js     → Orders state
```

### 4. API Communication Pattern

```javascript
// Frontend → Backend
fetch(apiUrl, {
  method: 'POST/GET/PUT',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`  // For protected endpoints
  },
  body: JSON.stringify(data)
})
.then(r => r.json())
.then(data => showToast.success(data.message))
.catch(err => showToast.error(err.message))
```

---

## Common Tasks

### Add a New Protected Endpoint

**Backend (PHP)**:
```php
// apis/new_endpoint.php
public function newAction(): void {
    $payload = $this->requireAuth();  // Verifies token
    
    if ($payload->role !== 'admin') {  // Check role if needed
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    
    // Your logic here
    http_response_code(200);
    echo json_encode(['success' => true]);
}
```

**Frontend (Vue)**:
```javascript
// In component
const response = await fetch('apis/new_endpoint.php', {
  method: 'GET',
  headers: { 'Authorization': `Bearer ${authStore.accessToken}` }
})

if (response.ok) {
  showToast.success('Action completed')
} else {
  const error = await response.json()
  showToast.error(error.error)
}
```

### Show Different UI Based on Role

```vue
<template>
  <!-- Regular user content -->
  <div v-if="!authStore.isAdmin">
    <p>My Orders: {{ orders.length }}</p>
  </div>
  
  <!-- Admin content -->
  <div v-if="authStore.isAdmin">
    <p>All Orders: {{ totalOrders }}</p>
    <select @change="updateStatus">
      <option>Change Status...</option>
    </select>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
const authStore = useAuthStore()
</script>
```

### Add Toast Notification

```javascript
import { showToast } from '../utils/toast'

// Types: success, error, warning, info
showToast.success('Order placed successfully!')
showToast.error('Failed to place order')
showToast.warning('This action cannot be undone')
showToast.info('Loading orders...')
```

### Protected Route in Router

```javascript
// src/router/index.js
{
  path: '/admin-panel',
  name: 'AdminPanel',
  component: AdminPanel,
  meta: { requiresAuth: true }  // Route guard enforces this
}

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
  } else {
    next()
  }
})
```

---

## API Reference

### Authentication Endpoints

#### POST /login.php
```json
// Request
{ "username": "user", "password": "pass" }

// Response (200)
{
  "access_token": "eyJ...",
  "refresh_token": "eyJ...",
  "expires_in": 900,
  "user": { "id": 1, "username": "user", "email": "user@test.com", "role": "user" }
}

// Error (401)
{ "error": "Invalid username or password" }
```

#### POST /register.php
```json
// Request
{ "username": "newuser", "email": "new@test.com", "password": "pass123" }

// Response (201)
{
  "message": "User created successfully",
  "access_token": "eyJ...",
  "refresh_token": "eyJ...",
  "expires_in": 900,
  "user": { ... }
}

// Error (422)
{ "error": "Username already exists" }
```

#### POST /refresh.php
```json
// Request
{ "refresh_token": "eyJ..." }

// Response (200)
{
  "access_token": "eyJ...",
  "refresh_token": "eyJ...",
  "expires_in": 900
}

// Error (401)
{ "error": "Invalid refresh token" }
```

### Order Endpoints

#### GET /order.php
```
Authorization: Bearer {access_token}

Response (200):
[
  {
    "id": 1,
    "user_id": 1,
    "items": [...],
    "total_items": 3,
    "total_price": "99.97",
    "status": "pending",
    "created_at": "2026-06-11 10:30:00"
  }
]
```

#### POST /order.php
```json
// Request
{
  "items": [
    {
      "product": { "id": 1, "title": "Product", "price": 10.99, "thumbnail": "..." },
      "quantity": 2
    }
  ]
}

// Response (201)
{
  "order_id": 1,
  "total_items": 2,
  "total_price": 21.98
}

// Error (422)
{ "error": "Cart is empty" }
```

#### PUT /order.php
```json
// Request (Admin only)
{
  "order_id": 1,
  "status": "shipped"
}

// Response (200)
{
  "message": "Order status updated successfully",
  "order_id": 1,
  "status": "shipped"
}

// Error (403 - not admin)
{ "error": "Forbidden: Admin access required" }

// Error (404 - not found)
{ "error": "Order not found" }
```

---

## Database Queries

### User Authentication
```php
// JwtAuth.php - login()
$stmt = $this->db->prepare(
  'SELECT id, username, email, password_hash, role FROM users 
   WHERE username = :u AND is_active = 1 LIMIT 1'
);
```

### Fetch User's Orders
```php
// OrderController.php - index() for regular users
$stmt = $this->db->prepare(
  'SELECT id, user_id, items, total_items, total_price, status, created_at
   FROM orders WHERE user_id = :uid ORDER BY created_at DESC'
);
```

### Fetch All Orders (Admin)
```php
// OrderController.php - index() for admins
$stmt = $this->db->prepare(
  'SELECT o.id, o.user_id, u.username, u.email, o.items, o.total_items, 
          o.total_price, o.status, o.created_at
   FROM orders o JOIN users u ON o.user_id = u.id
   ORDER BY o.created_at DESC'
);
```

### Update Order Status (Admin)
```php
// OrderController.php - updateStatus()
$stmt = $this->db->prepare(
  'UPDATE orders SET status = :status WHERE id = :id'
);
$stmt->execute([':status' => $status, ':id' => $orderId]);
```

---

## Configuration Files

### .env (Backend)
```env
APP_URL=http://localhost
DB_HOST=localhost
DB_NAME=r68_vue3_shop
DB_USER=root
DB_PASS=
JWT_ACCESS_SECRET=64_character_secret_here
JWT_REFRESH_SECRET=64_character_secret_here
```

### vite.config.js (Frontend Build)
```javascript
export default {
  server: { port: 5173 },
  // Vite config for Vue 3 + router + styling
}
```

### package.json (Dependencies)
```json
{
  "dependencies": {
    "vue": "^3.x",
    "vue-router": "^4.x",
    "pinia": "^2.x",
    "pinia-plugin-persistedstate": "^3.x",
    "vue3-toastify": "^0.x"
  },
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  }
}
```

---

## Key Files Explained

### src/stores/auth.js
- **Purpose**: Global auth state management
- **State**: accessToken, refreshToken, user, error, loading
- **Methods**: login(), register(), logout(), refreshAccessToken()
- **Computed**: isAuthenticated, userId, username, role, isAdmin

### src/router/index.js
- **Purpose**: URL routing and navigation guards
- **Meta**: requiresAuth flag for protected routes
- **Guard**: beforeEach() checks authentication
- **Redirect**: Unauthenticated users sent to login

### apis/src/Auth/JwtAuth.php
- **Purpose**: JWT token generation and verification
- **Methods**: login(), refresh(), verify(), logout()
- **Payload**: Includes id, username, role in token
- **Secrets**: 64-character keys from .env

### apis/src/Controllers/orderController.php
- **Purpose**: Order business logic
- **Methods**: create(), index(), updateStatus()
- **Authorization**: Checks role for admin-only endpoints
- **Response**: JSON with order data and status codes

---

## Debugging Tips

### Check Authentication State
```javascript
// Browser console
const auth = JSON.parse(localStorage.getItem('auth'))
console.log(auth)
// Shows: accessToken, refreshToken, user, expiresIn
```

### Verify Token Content
```javascript
// Decode JWT (no verification - just view payload)
const token = auth.accessToken
const payload = JSON.parse(atob(token.split('.')[1]))
console.log(payload)
// Shows: iss, sub, iat, exp, username, role
```

### Check API Response
```javascript
// Browser DevTools → Network tab
// Click on API request → Response tab
// Shows raw JSON response from backend
```

### View Database Records
```sql
-- Terminal
mysql -u root r68_vue3_shop

-- Check users
SELECT id, username, role FROM users;

-- Check orders with user info
SELECT o.id, u.username, o.status, o.total_price FROM orders o 
JOIN users u ON o.user_id = u.id;
```

### Monitor Network Requests
```javascript
// Browser DevTools → Network tab
// Filter by "XHR" to see API calls only
// Watch Authorization headers and response status
```

---

## Performance Optimization

### Current Optimizations
- ✅ JWT access tokens (15 min) reduce DB queries
- ✅ Token refresh (7 days) for long sessions
- ✅ Pinia store persists state to localStorage
- ✅ API responses minimal (only needed fields)
- ✅ JSON encoding for order items (compact storage)

### Future Optimizations
- Add pagination to orders list
- Cache product list in store
- Implement service worker for offline support
- Lazy load route components
- Compress images
- Add request debouncing for search

---

## Security Checklist

- [x] JWT secrets 64 characters (minimum 32)
- [x] Passwords hashed with bcrypt
- [x] CORS headers properly configured
- [x] Authorization header validation
- [x] Role-based endpoint access control
- [x] SQL prepared statements (no injection)
- [x] Type validation on backend
- [x] Refresh token rotation
- [x] Token expiry enforcement
- [x] Access denied responses (403)

---

## Common Code Patterns

### Fetch with Error Handling
```javascript
try {
  const response = await fetch(url, { 
    headers: { 'Authorization': `Bearer ${token}` }
  })
  
  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.error || 'Request failed')
  }
  
  const data = await response.json()
  return data
  
} catch (err) {
  showToast.error(err.message)
  throw err
}
```

### Conditional Rendering by Role
```vue
<template>
  <!-- Show to everyone -->
  <button @click="viewOrder">View Details</button>
  
  <!-- Show only to admins -->
  <button v-if="authStore.isAdmin" @click="updateStatus">Update Status</button>
  
  <!-- Show different content -->
  <div v-if="authStore.isAdmin">Admin Panel</div>
  <div v-else>User Dashboard</div>
</template>
```

### Update State and Show Toast
```javascript
async function updateOrder(orderId, status) {
  try {
    const response = await fetch('api/order.php', {
      method: 'PUT',
      headers: { 'Authorization': `Bearer ${token}` },
      body: JSON.stringify({ order_id: orderId, status })
    })
    
    if (!response.ok) throw new Error('Failed to update')
    
    // Update local state
    const order = orders.find(o => o.id === orderId)
    order.status = status
    
    // Show success
    showToast.success(`Order updated to ${status}`)
    
  } catch (err) {
    showToast.error(err.message)
  }
}
```

---

## Troubleshooting

### Problem: 401 Unauthorized
**Causes**:
- Missing Authorization header
- Invalid token format
- Token expired
- Wrong secret key

**Solution**:
- Check token in localStorage
- Refresh page to get new token
- Verify .env secrets match
- Check Bearer prefix format

### Problem: 403 Forbidden
**Causes**:
- User is not admin
- Role not in token
- Role check failed

**Solution**:
- Verify user role in database
- Log out and log in again
- Check role is included in JWT payload

### Problem: CORS Error
**Causes**:
- Missing CORS headers
- Wrong allowed origins
- Preflight request failed

**Solution**:
- Check header('Access-Control-Allow-Origin: *')
- Check header('Access-Control-Allow-Headers: Content-Type, Authorization')
- Allow OPTIONS method

### Problem: Data Type Mismatch
**Causes**:
- Database returns strings instead of numbers
- JSON encoding issues

**Solution**:
```javascript
// Convert on frontend
const data = await response.json()
data.total_price = parseFloat(data.total_price)
data.total_items = parseInt(data.total_items)
```

---

## Testing Commands

### Run Frontend Dev Server
```bash
npm run dev
# Output: http://localhost:5173
```

### Build Frontend
```bash
npm run build
# Creates dist/ folder
```

### Test Backend Endpoint (curl)
```bash
curl -X GET http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Reset Database
```bash
mysql -u root r68_vue3_shop < apis/db/r68_vue3_shop.sql
```

---

## Version Info

- Vue 3.x
- Vue Router 4.x
- Pinia 2.x
- PHP 7.4+
- MySQL 5.7+
- Node.js 16+

---

## Resources

- Vue 3 Docs: https://vuejs.org
- Pinia Store: https://pinia.vuejs.org
- Vue Router: https://router.vuejs.org
- JWT: https://jwt.io
- Bootstrap Icons: https://icons.getbootstrap.com
- Vue3-Toastify: https://github.com/jerrywu001/vue3-toastify

---

**Last Updated**: June 2026
**Maintained By**: Development Team
**Questions**: Check existing docs first, then ask in team chat

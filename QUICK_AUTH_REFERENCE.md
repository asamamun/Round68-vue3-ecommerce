# Quick Auth Reference

## What Was Implemented

✅ Complete JWT authentication system
✅ Login and Register pages with forms
✅ Token persistence to localStorage
✅ Automatic token attachment to API calls
✅ Automatic token refresh on 401
✅ Protected routes with redirects
✅ Field validation and error handling

---

## Key Files

| File | Purpose |
|------|---------|
| `src/stores/auth.js` | Auth state management with Pinia |
| `src/utils/apiClient.js` | HTTP client with auto token management |
| `src/views/Login.vue` | Login form page |
| `src/views/Register.vue` | Registration form page |

---

## Quick Usage

### Login User
```javascript
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const success = await authStore.login('username', 'password')
```

### Register User
```javascript
const result = await authStore.register('username', 'email@example.com', 'password')
if (result.success) {
  // User created and auto-logged in
}
```

### Check if User Authenticated
```javascript
if (authStore.isAuthenticated) {
  // User is logged in
}
```

### Get Current User
```javascript
console.log(authStore.user)      // { id, username, email }
console.log(authStore.username)  // "john_doe"
console.log(authStore.userId)    // 1
```

### Make API Call with Auth
```javascript
import { useApi } from '@/utils/apiClient'

const api = useApi()
const response = await api.get('order.php')
// Authorization header automatically attached!
```

### Logout User
```javascript
authStore.logout()
```

---

## Form Usage

### Login Form
- Username field
- Password field with show/hide toggle
- Error messages display automatically
- Button shows loading state

### Register Form
- Username field (3+ chars)
- Email field
- Password field (8+ chars)
- Confirm password with mismatch validation
- Terms of service checkbox
- Auto-login after registration
- Field-specific error messages

---

## Authentication Flow

1. User visits `/login`
2. Enters credentials and clicks "Sign In"
3. Sent to backend `POST /login.php`
4. Backend verifies and returns tokens
5. Tokens stored in localStorage
6. User redirected to home (or redirect param)

```
User -> Login Form -> /login.php -> JWT Tokens -> localStorage -> Home
```

---

## Token Management

### Storage
```
localStorage.auth = {
  accessToken: "...",
  refreshToken: "...",
  user: { id, username, email },
  expiresIn: 900
}
```

### Lifecycle
1. User logs in → tokens saved
2. API call → token attached automatically
3. 401 response → token refreshed automatically
4. New token → API call retried
5. User logs out → tokens cleared

---

## Protected Routes

### Current Protected Route
- `/place-order` - Requires authentication

### How It Works
```javascript
// In router
{
  path: '/place-order',
  component: PlaceOrder,
  meta: { requiresAuth: true }
}

// In navigation guard
if (to.meta.requiresAuth && !authStore.isAuthenticated) {
  next({ name: 'Login', query: { redirect: to.fullPath } })
}
```

### User Experience
1. Unauthenticated user clicks "Place Order"
2. Redirected to login with `?redirect=/place-order`
3. After login, redirected back to `/place-order`

---

## Token Details

### Access Token (JWT)
- **Valid for**: 15 minutes
- **Used for**: API requests
- **Renewed by**: Refresh token

### Refresh Token
- **Valid for**: 7 days
- **Used for**: Getting new access tokens
- **Stored**: Hashed in database
- **Rotated**: On each refresh

---

## Error Messages

### Login Errors
- "Invalid username or password" - Wrong credentials
- "Please enter both username and password" - Empty fields

### Register Errors
- "Username must be at least 3 characters" - Too short
- "Invalid email address" - Bad email format
- "Password must be at least 8 characters" - Too short
- "Username already taken" - Duplicate username
- "Email already registered" - Duplicate email
- "Passwords do not match" - Mismatch in password fields

---

## Environment Configuration

### Frontend .env
```
VITE_API_URL=http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/
```

### Backend .env (Required)
```
JWT_ACCESS_SECRET=your-secret-key
JWT_REFRESH_SECRET=your-refresh-secret
```

---

## API Endpoints

All endpoints automatically handle CORS and return JSON.

### `POST /login.php`
Login with username and password

### `POST /register.php`
Register new account

### `POST /refresh.php`
Get new access token (auto-called by apiClient)

### `POST /order.php`
Create order (auto-attaches token)

### `GET /order.php`
Get user's orders (auto-attaches token)

---

## Common Tasks

### Redirect After Login
```javascript
async function handleLogin() {
  const success = await authStore.login(username, password)
  if (success) {
    router.push('/')  // Or any route
  }
}
```

### Check Auth Before Action
```javascript
function placeOrder() {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  // Continue with order
}
```

### Show Username in Header
```vue
<template>
  <span v-if="authStore.isAuthenticated">
    Welcome, {{ authStore.username }}!
  </span>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
const authStore = useAuthStore()
</script>
```

### Logout Button
```vue
<button @click="authStore.logout()">Logout</button>
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| User logged out after refresh | Check localStorage is not cleared |
| 401 on every request | Verify JWT_ACCESS_SECRET is correct |
| Login button doesn't work | Check API_URL in .env |
| Tokens not saving | Check if persist plugin is enabled |
| CORS errors | Verify backend has CORS headers |

---

## Next: Create Refresh Endpoint

You still need to create `/apis/refresh.php` endpoint:

```php
<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Auth\JwtAuth;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// DB connection...
$pdo = new PDO(...);

$body = json_decode(file_get_contents('php://input'), true);
$refreshToken = $body['refresh_token'] ?? '';

if (!$refreshToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Refresh token required']);
    exit;
}

$auth = new JwtAuth($pdo);
$result = $auth->refresh($refreshToken);

if (!$result) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired refresh token']);
    exit;
}

http_response_code(200);
echo json_encode($result);
```

---

## Summary

- ✅ Login/Register pages created
- ✅ Auth store with token management
- ✅ API client with auto token attachment
- ✅ Protected routes configured
- ⏳ Create `/apis/refresh.php` endpoint

The auth system is ready to use! All API calls will automatically include the JWT token.

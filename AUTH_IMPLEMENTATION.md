# Authentication Implementation Guide

## Overview

Implemented a complete JWT-based authentication system with login, registration, and token management for your Vue 3 + PHP shop application.

---

## Architecture

### Backend (PHP)
- JWT tokens with access/refresh rotation
- 15-minute access tokens
- 7-day refresh tokens
- Secure password hashing (Argon2ID/bcrypt)
- Token refresh endpoint

### Frontend (Vue 3)
- Pinia store for auth state management
- Automatic token persistence to localStorage
- HTTP client with auto token attachment
- Token refresh on 401 responses
- Login/Register forms with validation

---

## Files Created/Modified

### Created Files

1. **`src/views/Login.vue`**
   - Full login form with validation
   - Username and password fields
   - Show/hide password toggle
   - Error handling and display
   - Auto-redirect after successful login
   - Links to register

2. **`src/views/Register.vue`**
   - Full registration form
   - Username, email, password fields
   - Password confirmation validation
   - Terms of service checkbox
   - Field validation with helpful messages
   - Auto-login after registration
   - Links to login

3. **`src/utils/apiClient.js`**
   - HTTP client with automatic JWT token management
   - Auto-attach Authorization headers
   - Auto token refresh on 401
   - Methods: get(), post(), put(), delete(), patch()
   - Composable `useApi()` for use in components

### Modified Files

1. **`src/stores/auth.js`**
   - Refactored to use local storage persistence
   - Separate `accessToken` and `refreshToken` state
   - Updated `login()` and new `register()` methods
   - Token refresh functionality
   - Proper error handling

2. **`src/views/PlaceOrder.vue`**
   - Updated to use `authStore.accessToken` instead of `authStore.user.token`

3. **`src/stores/order.js`**
   - Updated `fetchOrders()` to accept authStore parameter

---

## Token Structure

### Login Response (from backend)
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_in": 900,
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com"
  }
}
```

### Token Payloads

**Access Token (JWT payload):**
```json
{
  "iss": "http://localhost/...",  // Issuer
  "sub": 1,                        // User ID
  "iat": 1708956234,              // Issued at
  "exp": 1708957134,              // Expires at (15 min)
  "username": "john_doe"
}
```

**Refresh Token (JWT payload):**
```json
{
  "sub": 1,                        // User ID
  "iat": 1708956234,
  "exp": 1709560834               // Expires at (7 days)
}
```

---

## Auth Store Structure

### State
```javascript
accessToken      // JWT access token for API calls
refreshToken     // JWT refresh token for getting new access tokens
user            // { id, username, email }
loading         // Boolean indicating async operations
error           // Error message if any
expiresIn       // Access token expiry time in seconds
```

### Getters
```javascript
isAuthenticated  // Computed: !!accessToken
userId          // Computed: user?.id
username        // Computed: user?.username
email           // Computed: user?.email
token           // Computed: accessToken (backwards compatibility)
```

### Actions
```javascript
login(username, password)           // → returns boolean
register(username, email, password) // → returns { success, errors }
logout()                            // Clear all auth state
refreshAccessToken()                // Get new access token using refresh token
clearError()                        // Clear error message
restoreSession()                    // Restore from localStorage (auto-called)
```

---

## Usage Examples

### In Components

#### Login Usage
```vue
<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

async function handleLogin() {
  const success = await authStore.login('username', 'password')
  if (success) {
    router.push('/') // Redirect to home
  }
}
</script>
```

#### Making API Calls with Auth
```vue
<script setup>
import { useApi } from '@/utils/apiClient'

const api = useApi()

// Get user's orders
const response = await api.get('order.php')
const orders = await response.json()

// Create new order
const response = await api.post('order.php', { items: [...] })
const order = await response.json()
</script>
```

#### Direct Fetch with Token
```javascript
// Access the token from store
const authStore = useAuthStore()
const token = authStore.accessToken

fetch('/api/endpoint', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
```

---

## Authentication Flow

### Login Flow
1. User enters username and password
2. Click "Sign In"
3. `POST /login.php` with credentials
4. Backend verifies password
5. Backend returns JWT tokens + user data
6. Store saves tokens to localStorage
7. Redirect to home or return URL

### Registration Flow
1. User fills registration form
2. Click "Create Account"
3. `POST /register.php` with credentials
4. Backend validates input and checks uniqueness
5. Backend creates user account
6. Backend auto-logs in user (returns tokens)
7. Store saves tokens
8. Redirect to home

### Token Refresh Flow
1. API call returns 401 Unauthorized
2. apiClient automatically calls `refreshAccessToken()`
3. `POST /refresh.php` with refresh token
4. Backend verifies refresh token and issues new access token
5. New token stored in store
6. Original API call retried with new token
7. If refresh fails, user is logged out

### Protected Route Access
1. User tries to access `/place-order`
2. Router guard checks `authStore.isAuthenticated`
3. If not authenticated, redirect to `/login?redirect=/place-order`
4. User logs in
5. After login, redirected back to `/place-order`

---

## Token Persistence

Tokens are automatically saved to localStorage via Pinia persist plugin:
```javascript
// Stored keys in localStorage:
auth.accessToken
auth.refreshToken
auth.user
auth.expiresIn
```

On app reload:
- Pinia automatically restores tokens from localStorage
- User remains logged in if tokens are valid

---

## Security Features

✅ **Password Hashing**
- Argon2ID (or bcrypt fallback) for password hashing
- Passwords never stored in plaintext

✅ **Token Security**
- JWT tokens with expiration
- Refresh token stored hashed in database
- Refresh token rotation on each use

✅ **CORS Headers**
- Proper CORS headers set on all endpoints
- Credentials handled correctly

✅ **HTTP Headers**
- Content-Type validation
- Authorization header verification
- Preflight requests handled

✅ **XSS Protection**
- Tokens stored in localStorage (not sessionStorage)
- Vue sanitizes all template outputs

✅ **Token Rotation**
- New refresh token issued on each refresh
- Old refresh token invalidated in database

---

## API Endpoints

### Login
```
POST /apis/login.php
Content-Type: application/json

{
  "username": "john_doe",
  "password": "password123"
}

Response 200:
{
  "access_token": "...",
  "refresh_token": "...",
  "expires_in": 900,
  "user": { "id": 1, "username": "john_doe", "email": "john@example.com" }
}

Response 401:
{
  "error": "Invalid username or password"
}
```

### Register
```
POST /apis/register.php
Content-Type: application/json

{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "SecurePass123"
}

Response 201:
{
  "message": "Account created successfully",
  "access_token": "...",
  "refresh_token": "...",
  "expires_in": 900,
  "user": { "id": 1, "username": "john_doe", "email": "john@example.com" }
}

Response 409:
{
  "errors": {
    "username": "Username already taken",
    "email": "Email already registered"
  }
}
```

### Refresh Token
```
POST /apis/refresh.php
Content-Type: application/json

{
  "refresh_token": "..."
}

Response 200:
{
  "access_token": "...",
  "refresh_token": "...",
  "expires_in": 900
}

Response 401:
{
  "error": "Invalid or expired refresh token"
}
```

---

## Environment Variables

### Frontend (.env)
```
BASE_URL=http://localhost:5173
VITE_API_URL=http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/
```

### Backend (.env)
```
DB_HOST=localhost
DB_NAME=shop
DB_USER=root
DB_PASS=

JWT_ACCESS_SECRET=your-super-secret-key-here
JWT_REFRESH_SECRET=your-refresh-secret-key-here
APP_URL=http://localhost/...
```

---

## Frontend Routes

### Public Routes
- `/` - Home page (no auth required)
- `/product/:id` - Product page (no auth required)
- `/cart` - Shopping cart (no auth required)
- `/login` - Login page (no auth required)
- `/register` - Register page (no auth required)

### Protected Routes
- `/place-order` - Place order (auth required)
  - Redirects to `/login?redirect=/place-order` if not authenticated

---

## Error Handling

### Login/Register Errors
```javascript
// In components, check authStore.error
if (authStore.error) {
  console.error('Auth error:', authStore.error)
  // Error already displayed to user
}
```

### API Errors
```javascript
const response = await api.post('order.php', { items })

if (!response.ok) {
  const error = await response.json()
  console.error('Order error:', error.error)
}
```

---

## Testing Checklist

- [ ] User can register with valid data
- [ ] Register prevents duplicate username/email
- [ ] Register validates password length (8+ chars)
- [ ] Register validates email format
- [ ] User can login with correct credentials
- [ ] Login fails with incorrect credentials
- [ ] Tokens stored in localStorage after login
- [ ] Tokens cleared from localStorage on logout
- [ ] Protected routes redirect unauthenticated users to login
- [ ] User redirected to original page after login
- [ ] Access token used in API requests
- [ ] 401 responses trigger token refresh
- [ ] New token used for retried requests
- [ ] Failed token refresh logs out user
- [ ] User remains logged in after page reload
- [ ] Password visibility toggle works
- [ ] Form validation shows helpful errors

---

## Next Steps (Optional)

1. **Password Reset** - Add forgot password flow
2. **Email Verification** - Verify emails on registration
3. **2FA** - Two-factor authentication
4. **Social Login** - Google/GitHub login integration
5. **Rate Limiting** - Prevent brute force attacks
6. **Session Management** - Logout all devices, device tracking
7. **Account Settings** - Change password, profile updates
8. **Audit Logs** - Track login/logout activities

---

## Key Differences from Previous Setup

| Aspect | Before | Now |
|--------|--------|-----|
| Token Storage | In user object | Separate accessToken/refreshToken |
| Token Management | Manual | Automatic via apiClient |
| Refresh Logic | None | Automatic on 401 |
| API Calls | Manual header attachment | Automatic via useApi() |
| Error Handling | Basic | Detailed field-level errors |
| Persistence | Basic | Pinia persist plugin |
| Form Validation | None | Complete client-side validation |

---

## Support & Debugging

### Common Issues

**Issue: User logged out after page refresh**
- Solution: Check if tokens are being saved to localStorage
- Check: `localStorage.getItem('auth')`

**Issue: 401 errors on every request**
- Solution: Token might be expired
- Check: Refresh endpoint is working correctly
- Check: Refresh token is valid in database

**Issue: CORS errors**
- Solution: Ensure backend has proper CORS headers
- Check: `cors.php` is included in all endpoints

**Issue: "Invalid token" errors**
- Solution: Check if JWT secrets match
- Check: `.env` has same JWT_ACCESS_SECRET as backend

---

## Security Best Practices

1. ✅ Never hardcode API URLs or secrets
2. ✅ Always use HTTPS in production
3. ✅ Rotate JWT secrets regularly
4. ✅ Implement rate limiting on auth endpoints
5. ✅ Log authentication failures
6. ✅ Use strong password requirements
7. ✅ Implement CSRF protection
8. ✅ Validate tokens on every protected endpoint

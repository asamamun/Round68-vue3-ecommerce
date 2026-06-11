# Environment Configuration Guide

## Overview

All API URLs are now configured through environment variables instead of being hardcoded. This is a professional best practice that allows easy deployment to different environments.

---

## Configuration Files

### Frontend - `.env` (Root Directory)

**Location**: `class08/routing/.env`

**Content**:
```env
VITE_API_URL=http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/
```

**How to Change**:
1. Open `.env` file in project root
2. Update `VITE_API_URL` with your API URL
3. Save file
4. Restart dev server (or it auto-refreshes)

### Backend - `apis/.env`

**Location**: `class08/routing/apis/.env`

**Content**:
```env
APP_URL=http://localhost

DB_HOST=localhost
DB_NAME=r68_vue3_shop
DB_USER=root
DB_PASS=

JWT_ACCESS_SECRET=e2f5c8b1a9d4f7e3c6b8a1d9f2e5c8b1a9d4f7e3c6b8a1d9f2e5c8b1a9d4f7
JWT_REFRESH_SECRET=d9c8e7f6a5b4c3d2e1f0a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d4e3f2a1b0c
```

---

## How API_URL is Used

### Frontend Files Using `VITE_API_URL`

All frontend API calls now use the configured URL:

```javascript
// src/stores/auth.js
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/'

// Usage in API calls
fetch(`${API_URL}login.php`, { ... })
fetch(`${API_URL}register.php`, { ... })
fetch(`${API_URL}order.php`, { ... })
```

### Files Updated

✅ `src/stores/auth.js` - Login, register, token refresh
✅ `src/stores/order.js` - Fetch orders
✅ `src/views/PlaceOrder.vue` - Create order
✅ `src/views/Orders.vue` - Fetch and update orders
✅ `src/utils/apiClient.js` - API client utility

### How It Works

1. **Development**: Vite reads `.env` file
2. **Build**: Variables replaced at compile time
3. **Runtime**: `import.meta.env.VITE_API_URL` is replaced with the actual URL
4. **Fallback**: If not set, uses default localhost URL

---

## Environment-Specific URLs

### Local Development
```env
VITE_API_URL=http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/
```

### Staging Server
```env
VITE_API_URL=https://staging.yourdomain.com/round68/VUE3/R68-Vue3/class08/routing/apis/
```

### Production
```env
VITE_API_URL=https://api.yourdomain.com/
```

### Docker Container
```env
VITE_API_URL=http://backend-service:8000/apis/
```

---

## Deployment Process

### Step 1: Update `.env`
```bash
# Before deploying, update .env with production URL
VITE_API_URL=https://api.yourdomain.com/apis/
```

### Step 2: Build
```bash
npm run build
# Creates dist/ folder with API_URL baked in
```

### Step 3: Deploy
```bash
# Upload dist/ folder to web server
# API calls now use production URL
```

---

## No More Hardcoding!

**Before** (❌ Unprofessional):
```javascript
const response = await fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
  method: 'GET'
})
```

**After** (✅ Professional):
```javascript
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/...'
const response = await fetch(`${API_URL}order.php`, {
  method: 'GET'
})
```

---

## Vite Environment Variables

### Naming Convention
- Must start with `VITE_` to be accessible in frontend
- `VITE_API_URL` → `import.meta.env.VITE_API_URL`
- `VITE_APP_NAME` → `import.meta.env.VITE_APP_NAME`

### Type Checking
```javascript
// These are strings at runtime
console.log(typeof import.meta.env.VITE_API_URL)  // "string"

// They are replaced during build, not stored as variables
```

### Accessing in Components
```vue
<script setup>
const API_URL = import.meta.env.VITE_API_URL

// Or directly
console.log(import.meta.env.VITE_API_URL)
</script>
```

---

## Security Notes

✅ **Safe to Commit**: `.env` file is in `.gitignore` for production secrets
✅ **Frontend Exposure**: API_URL is visible to users (it's a public URL anyway)
✅ **Backend Secrets**: JWT secrets in `apis/.env` are server-side only
✅ **No API Keys**: Current setup doesn't store sensitive keys in frontend

---

## Troubleshooting

### Issue: API_URL Not Updating
**Solution**:
1. Save `.env` file
2. Restart dev server: `npm run dev`
3. Clear browser cache
4. Hard refresh: `Ctrl+Shift+R`

### Issue: Wrong URL in Production
**Solution**:
1. Verify `.env` before building
2. Rebuild with correct URL
3. Clear build cache: `rm -rf dist/`
4. Redeploy

### Issue: CORS Error After Changing URL
**Solution**:
1. Verify new URL is correct
2. Check `Access-Control-Allow-Origin` header on backend
3. Ensure trailing slash in URL: `.../apis/`

---

## Best Practices

✅ **Do**:
- Use environment variables for all API URLs
- Keep `.env` in `.gitignore`
- Document all available variables
- Use meaningful variable names (VITE_API_URL, not VITE_URL)
- Include fallback values for development

❌ **Don't**:
- Hardcode URLs in components
- Commit `.env` with production secrets
- Use environment variables for non-sensitive URLs in comments
- Change URL at runtime without rebuild (for SPA apps)

---

## API Endpoint Configuration

### All Endpoints Use Same Base URL

```javascript
const API_URL = import.meta.env.VITE_API_URL

// Login
fetch(`${API_URL}login.php`)

// Register
fetch(`${API_URL}register.php`)

// Orders
fetch(`${API_URL}order.php`)

// Token Refresh
fetch(`${API_URL}refresh.php`)
```

### Adding New Endpoints

When adding new API endpoints, always use:
```javascript
fetch(`${API_URL}new-endpoint.php`, { ... })
```

Never hardcode the URL.

---

## Testing Different URLs

### Quick Test in Browser Console
```javascript
// Check current API URL
console.log(import.meta.env.VITE_API_URL)

// Test API connection
fetch(import.meta.env.VITE_API_URL + 'login.php')
  .then(r => console.log(r.status))
```

### Mock API Server
For local development without backend:
```env
VITE_API_URL=http://mockapi.local/
```

---

## Version Control

### `.gitignore` (Ensure `.env` is ignored)
```
.env
.env.local
.env.*.local
```

### What Gets Committed
```
✅ .env.example (template)
✅ Code files (auth.js, Orders.vue, etc.)
✅ vite.config.js
✅ package.json
❌ .env (local config)
```

### Create `.env.example`
```env
# Copy of .env without sensitive values
VITE_API_URL=http://your-api-url-here/apis/
```

---

## CI/CD Integration

### GitHub Actions Example
```yaml
- name: Build with Production URL
  env:
    VITE_API_URL: https://api.yourdomain.com/apis/
  run: npm run build
```

### Docker Example
```dockerfile
ENV VITE_API_URL=http://backend:8000/apis/
RUN npm run build
```

---

**Last Updated**: June 2026
**Status**: ✅ Fully Configured
**Professional Level**: Enterprise-grade environment management

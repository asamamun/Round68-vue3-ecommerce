# Testing Guide - Vue3 E-Commerce Application

## Quick Start

### Prerequisites
- XAMPP running (Apache + MySQL)
- Node.js and npm installed
- Database: `r68_vue3_shop` created
- Frontend: Running on `http://localhost:5173`
- Backend: Running on `http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/`

### Starting the Application

```bash
# Terminal 1: Start the frontend dev server
npm run dev
# Output: http://localhost:5173

# Terminal 2: Keep XAMPP running
# - Apache: http://localhost
# - MySQL: running on port 3306
```

---

## Test Scenarios

### ✅ Test 1: User Registration

**Objective**: Verify new users can register and receive tokens

**Steps**:
1. Navigate to `http://localhost:5173/register`
2. Fill in the form:
   - Username: `testuser1`
   - Email: `testuser1@test.com`
   - Password: `password123`
   - Confirm Password: `password123`
3. Click "Register"

**Expected Results**:
- ✅ Toast success message: "Registration successful"
- ✅ Redirected to home page
- ✅ Username appears in navbar dropdown
- ✅ User stored in database with role='user'
- ✅ Tokens stored in localStorage

**Verify in Browser Console**:
```javascript
// Check localStorage for tokens
console.log(JSON.parse(localStorage.getItem('auth')))
// Should show: { accessToken, refreshToken, user: {id, username, email, role} }
```

---

### ✅ Test 2: User Login

**Objective**: Verify existing users can login with stored credentials

**Steps**:
1. Navigate to `http://localhost:5173/login`
2. Enter credentials:
   - Username: `testuser1`
   - Password: `password123`
3. Click "Login"

**Expected Results**:
- ✅ Toast success message: "Login successful"
- ✅ Redirected to home page
- ✅ Username appears in navbar
- ✅ "Orders" link appears in navbar
- ✅ Access token valid for 15 minutes

**Verify in Database**:
```sql
SELECT id, username, email, role FROM users WHERE username='testuser1';
-- Should show: 1 | testuser1 | testuser1@test.com | user
```

---

### ✅ Test 3: View Orders as Regular User

**Objective**: Verify users see only their own orders

**Steps**:
1. Login as regular user (testuser1)
2. Add some items to cart from `/product`
3. Go to `/cart` and click "Proceed to Checkout"
4. Review order at `/place-order` and click "Place Order"
5. Click "Orders" in navbar

**Expected Results**:
- ✅ Redirected to `/orders` page
- ✅ Header shows "My Orders"
- ✅ NO "All Orders" badge
- ✅ NO customer info (username/email) shown
- ✅ Order cards show: ID, date, status, items, total
- ✅ Status dropdown NOT visible (not an admin)
- ✅ Toast message if no orders: "No orders found"

**Verify in Database**:
```sql
SELECT o.id, o.user_id, u.username, o.status, o.created_at 
FROM orders o 
JOIN users u ON o.user_id = u.id 
WHERE u.username='testuser1';
```

---

### ✅ Test 4: Admin View All Orders

**Objective**: Verify admins can see all orders and customer info

**Prerequisites**: 
- Create an admin user in database:
```sql
UPDATE users SET role='admin' WHERE id=1;
-- OR
INSERT INTO users (username, email, password_hash, role) 
VALUES ('admin1', 'admin@test.com', '$2y$10$...', 'admin');
```

**Steps**:
1. Login as admin user
2. Click "Orders" in navbar

**Expected Results**:
- ✅ Header shows "All Orders" with yellow badge
- ✅ "Admin View" badge displayed
- ✅ Customer info visible for each order: username (email)
- ✅ See orders from multiple users
- ✅ Status dropdown visible on each order card

---

### ✅ Test 5: Admin Update Order Status

**Objective**: Verify admins can change order status

**Prerequisites**: 
- Logged in as admin
- At least one order exists in database

**Steps**:
1. Navigate to `/orders`
2. Find an order card with status "pending"
3. Click status dropdown "Change Status..."
4. Select "Mark Processing"

**Expected Results**:
- ✅ Toast success: "Order #X status updated to Processing"
- ✅ Badge color changes to blue (Processing)
- ✅ UI updates without page refresh
- ✅ Database updated: `SELECT status FROM orders WHERE id=X;`

**Test All Status Transitions**:
- pending → processing → shipped → delivered ✅
- any → cancelled ✅
- Option should not show current status

---

### ✅ Test 6: Non-Admin Cannot Update Status

**Objective**: Verify status endpoint is admin-only

**Prerequisites**:
- Two test cases:

**Case A: Regular user trying to update**:
1. Login as regular user
2. Open browser console
3. Execute:
```javascript
const token = JSON.parse(localStorage.getItem('auth')).accessToken
fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({ order_id: 1, status: 'shipped' })
})
.then(r => r.json())
.then(d => console.log(d))
```

**Expected Result**:
- ✅ Response: `{ error: 'Forbidden: Admin access required' }`
- ✅ HTTP Status: 403

**Case B: Non-authenticated request**:
1. Execute same fetch WITHOUT authorization header
2. Expected: `{ error: 'Unauthorised: No Authorization header provided' }`
3. HTTP Status: 401

---

### ✅ Test 7: Shopping Cart Flow

**Objective**: Verify complete shopping experience from browse to order

**Steps**:
1. Navigate to `/product`
2. Browse products (3-4 items)
3. Click "Add to Cart" on 3 different items

**Expected Results**:
- ✅ Toast for each add: "[Product Name] added to cart!"
- ✅ Cart count updates in navbar
- ✅ Products stored in cart store

**4. Go to `/cart`**:
- ✅ All 3 products displayed
- ✅ Quantities and subtotals correct
- ✅ Total amount calculated properly

**5. Remove one item**:
- ✅ Toast warning: "[Product Name] removed from cart"
- ✅ Item removed from list
- ✅ Total recalculated

**6. Clear cart**:
- ✅ All items removed
- ✅ Empty cart message displayed
- ✅ "Start Shopping" button visible

---

### ✅ Test 8: Place Order Flow

**Objective**: Verify complete checkout and order creation

**Steps**:
1. Add items to cart from `/product`
2. Go to `/cart`
3. Click "Proceed to Checkout"

**Expected (if not logged in)**:
- ✅ Toast warning or redirect to login
- ✅ Login, then try again

**4. At `/place-order`**:
- ✅ Order review showing all items
- ✅ Item images, prices, quantities visible
- ✅ Total calculation correct
- ✅ "Place Order" button enabled

**5. Click "Place Order"**:
- ✅ Toast success: "Order placed successfully!"
- ✅ Order ID shown in toast
- ✅ Cart cleared
- ✅ Redirected to `/orders`
- ✅ New order visible in list

**Verify in Database**:
```sql
SELECT * FROM orders ORDER BY created_at DESC LIMIT 1;
-- Should show: new order with user_id, items (JSON), total_price, status='pending'
```

---

### ✅ Test 9: Token Expiry & Refresh

**Objective**: Verify token refresh mechanism

**Prerequisites**:
- Understand JWT tokens: access (15 min), refresh (7 days)

**Steps**:
1. Login normally
2. Note token expiry times in console:
```javascript
const auth = JSON.parse(localStorage.getItem('auth'))
console.log('Token expires in 15 minutes')
```

**3. Simulate expired token** (optional):
- Manually edit localStorage and set `expiresIn` to 0
- Try accessing protected endpoint
- Should get "Unauthorised: Invalid or expired token"

**4. Refresh token flow**:
- Backend automatically refreshes before expiry
- Or call refresh endpoint:
```javascript
fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/refresh.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ refresh_token: '...' })
})
```

**Expected**:
- ✅ New access token issued
- ✅ Refresh token rotated
- ✅ Session continues seamlessly

---

### ✅ Test 10: Error Handling

**Objective**: Verify proper error messages

**Test Cases**:

**1. Invalid Login Credentials**:
- Login with wrong password
- ✅ Toast error: "Invalid username or password"

**2. Duplicate Registration**:
- Register with existing username
- ✅ Toast error: "Username already exists"

**3. Empty Cart Checkout**:
- Clear cart, try `/place-order`
- ✅ Toast: "Your cart is empty"

**4. Unauthorized Order Status Update**:
- Regular user tries to update order status
- ✅ Backend returns 403 Forbidden

**5. Invalid Status Value**:
- Admin tries invalid status
- ✅ Toast error: "Invalid status"

---

## Browser Console Testing

### Check Authentication State
```javascript
const authStore = useAuthStore()
console.log('Is Authenticated:', authStore.isAuthenticated)
console.log('Username:', authStore.username)
console.log('Role:', authStore.role)
console.log('Is Admin:', authStore.isAdmin)
console.log('Access Token:', authStore.accessToken)
```

### Check Cart State
```javascript
const cartStore = useCartStore()
console.log('Cart Items:', cartStore.items)
console.log('Cart Count:', cartStore.itemCount)
console.log('Cart Total:', cartStore.total)
```

### Check Order State
```javascript
const orderStore = useOrderStore()
console.log('Current Orders:', orderStore.orders)
```

### Make Manual API Calls
```javascript
// Test order fetch as admin
const token = JSON.parse(localStorage.getItem('auth')).accessToken
fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
  method: 'GET',
  headers: { 'Authorization': `Bearer ${token}` }
})
.then(r => r.json())
.then(d => console.log(d))
```

---

## Database Verification Queries

### Check Users
```sql
SELECT id, username, email, role, created_at FROM users;
```

### Check Orders
```sql
SELECT o.id, o.user_id, u.username, o.status, o.total_price, o.created_at 
FROM orders o 
JOIN users u ON o.user_id = u.id 
ORDER BY o.created_at DESC;
```

### Check Order Items
```sql
SELECT id, items FROM orders LIMIT 1;
-- Items column contains JSON: [{"id":1,"title":"Product","price":10.99,"quantity":2}]
```

### Verify Admin User
```sql
SELECT id, username, role FROM users WHERE role='admin';
```

---

## Common Issues & Fixes

### Issue: "401 Unauthorised" on orders endpoint
**Cause**: Token expired or not sent in header
**Fix**: 
- Refresh page (triggers token refresh)
- Login again
- Check Authorization header is present

### Issue: "403 Forbidden" on status update
**Cause**: User is not admin or role not in token
**Fix**:
- Verify user role in database: `SELECT role FROM users WHERE id=X;`
- Regenerate token by logging out and back in
- Check JWT_ACCESS_SECRET is correct

### Issue: Cart items not showing
**Cause**: Cart store not persisting
**Fix**:
- Check browser localStorage enabled
- Clear localStorage and refresh
- Add items again

### Issue: Toast notifications not showing
**Cause**: Toast not imported or configured
**Fix**:
- Check `src/main.js` has toast setup
- Import `showToast` from `src/utils/toast.js`
- Check component uses `showToast.success()` etc.

### Issue: Orders page showing old data
**Cause**: Cache or stale API response
**Fix**:
- Hard refresh: Ctrl+Shift+R
- Clear browser cache
- Check API endpoint returns fresh data

---

## Performance Testing

### Measure API Response Times
```javascript
console.time('Fetch Orders')
fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
  method: 'GET',
  headers: { 'Authorization': `Bearer ${token}` }
})
.then(r => r.json())
.then(d => { console.timeEnd('Fetch Orders'); return d })
.then(d => console.log(`Received ${d.length} orders`))
```

### Check Network Tab
1. Open DevTools → Network tab
2. Reload page
3. Check:
   - API response times < 500ms
   - Bundle size < 500KB
   - No failed requests (red)

---

## Load Testing (Optional)

### Create Multiple Orders
```javascript
// Run in browser console of logged-in user
for(let i = 0; i < 5; i++) {
  setTimeout(() => {
    fetch('http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/order.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${JSON.parse(localStorage.getItem('auth')).accessToken}`
      },
      body: JSON.stringify({
        items: [
          { product: { id: 1, title: 'Test', price: 10, thumbnail: '' }, quantity: 1 }
        ]
      })
    }).then(r => r.json()).then(d => console.log(`Order created:`, d))
  }, i * 1000)
}
```

---

## Sign-Off

- [ ] All 10 test scenarios passed
- [ ] No console errors
- [ ] No unhandled promise rejections
- [ ] Database queries returning expected results
- [ ] Performance acceptable (< 500ms API responses)
- [ ] Security checks passed (401/403 errors working)
- [ ] Ready for production deployment

---

**Test Date**: _______________
**Tested By**: _______________
**Status**: ✅ READY FOR PRODUCTION

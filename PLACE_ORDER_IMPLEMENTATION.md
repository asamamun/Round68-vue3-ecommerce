# Place Order Feature - Implementation Summary

## Overview
Implemented a complete place order workflow for authenticated users. The feature includes frontend components, stores, routing with auth guards, and backend API integration.

---

## Changes Made

### 1. Frontend Components

#### New View: `src/views/PlaceOrder.vue`
- Display cart items in read-only review mode
- Show customer username from auth store
- Display order summary with:
  - Subtotal
  - Free shipping
  - Tax calculation (10%)
  - Total amount
- Show delivery info and estimated delivery time
- Confirm Order button with loading state
- Error and success messages
- Authenticated users only

**Key Features:**
- Validates auth before showing order confirmation
- Sends cart data to backend API with JWT token
- Clears cart after successful order
- Displays order ID on success
- Auto-redirect to Home after 2 seconds
- Responsive design for mobile/tablet

---

### 2. State Management

#### New Store: `src/stores/order.js`
- Pinia store for managing order history
- State:
  - `orders`: Array of user's orders
  - `loading`: Async state indicator
  - `error`: Error message handling

- Getters:
  - `totalOrders`: Count of orders
  - `totalSpent`: Sum of all order amounts
  - `getOrderById()`: Retrieve specific order

- Actions:
  - `addOrder()`: Add new order to history
  - `updateOrder()`: Update order details
  - `removeOrder()`: Remove order from list
  - `fetchOrders()`: Fetch user's orders from backend
  - `clearOrders()`: Reset order history

---

### 3. Routing

#### Updated: `src/router/index.js`
- Imported `PlaceOrder` component
- Added new route:
  ```javascript
  {
    path: '/place-order',
    name: 'PlaceOrder',
    component: PlaceOrder,
    meta: { requiresAuth: true }  // ← Protected route
  }
  ```
- Route guard checks `authStore.isAuthenticated`
- Redirects unauthenticated users to Login with redirect parameter

---

### 4. Updated Views

#### Modified: `src/views/Cart.vue`
- Updated `placeOrder()` function:
  - Checks if user is authenticated
  - Redirects to Login if not authenticated (with redirect to /place-order)
  - Redirects to PlaceOrder component if authenticated
  - Removed static alert, now routes properly

---

### 5. Backend API

#### New Endpoint: `apis/order.php`
- Router file that handles both POST and GET requests
- Instantiates `OrderController` with DB and JWT auth
- Routes:
  - **POST /order.php** → `OrderController::create()`
    - Creates new order from cart items
    - Requires Authorization header with JWT token
    - Validates items and calculates totals
    - Returns: `{ order_id, total_items, total_price }`
  
  - **GET /order.php** → `OrderController::index()`
    - Fetches all orders for authenticated user
    - Requires Authorization header
    - Returns array of orders with items, totals, status, dates

#### Existing: `apis/src/Controllers/OrderController.php`
- Already implements order creation and retrieval logic
- Uses JWT auth to verify user
- Stores order data with cart snapshot as JSON
- Validates and sanitizes item data

---

## Database Schema
Using existing schema from `apis/r68_vue3_shop.sql`:

```sql
CREATE TABLE orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    items JSON NOT NULL,  -- Cart snapshot
    total_items SMALLINT UNSIGNED,
    total_price DECIMAL(10, 2),
    status ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'),
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

---

## Authentication Flow

1. User adds items to cart
2. User clicks "Place Order" in Cart view
3. If not authenticated:
   - Redirected to Login page with `redirect=/place-order`
4. If authenticated:
   - Routed to PlaceOrder view
   - JWT token from `authStore.user.token` is used in request headers
5. PlaceOrder component:
   - Calls `POST /order.php` with Authorization header
   - Backend verifies JWT and extracts user ID
   - Order created with user_id from JWT payload
6. On success:
   - Order cleared from cart
   - Order ID displayed
   - Auto-redirect to Home

---

## API Requests

### Create Order
```bash
POST http://localhost/apis/order.php
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json

{
  "items": [
    {
      "product": {
        "id": 1,
        "title": "Product Name",
        "price": 29.99,
        "thumbnail": "..."
      },
      "quantity": 2
    }
  ]
}
```

**Response (201):**
```json
{
  "order_id": 42,
  "total_items": 2,
  "total_price": 59.98
}
```

### Get User Orders
```bash
GET http://localhost/apis/order.php
Authorization: Bearer <JWT_TOKEN>
```

**Response (200):**
```json
[
  {
    "id": 42,
    "items": [...],
    "total_items": 2,
    "total_price": 59.98,
    "status": "pending",
    "created_at": "2024-01-15T10:30:00"
  }
]
```

---

## User Experience

### Authentication Required
- ✅ Protected route `/place-order`
- ✅ Unauthenticated users redirected to Login
- ✅ Auto-redirect back to place order after login

### Order Placement
- ✅ Review cart items before confirming
- ✅ See order total with tax calculation
- ✅ Loading indicator during submission
- ✅ Error handling with user-friendly messages
- ✅ Success message with order ID
- ✅ Auto-redirect to home

### Cart Management
- ✅ Cart cleared after successful order
- ✅ Cart preserved if order fails
- ✅ Continue Shopping option available

---

## Files Modified/Created

### Created:
- `src/views/PlaceOrder.vue` - Order confirmation page
- `src/stores/order.js` - Order state management
- `apis/order.php` - API router endpoint

### Modified:
- `src/router/index.js` - Added route with auth guard
- `src/views/Cart.vue` - Updated place order button logic

### Already Existed:
- `apis/src/Controllers/OrderController.php` - Order business logic
- `apis/r68_vue3_shop.sql` - Database schema
- `src/stores/auth.js` - Authentication store

---

## Testing Checklist

- [ ] User can view place order page when authenticated
- [ ] Unauthenticated users are redirected to login
- [ ] Order items display correctly on place order page
- [ ] Order total calculates correctly with tax
- [ ] Order submission works with valid JWT token
- [ ] Cart clears after successful order
- [ ] Error message displays on failed order
- [ ] Order ID displays on success
- [ ] Auto-redirect to home after 2 seconds
- [ ] User can retrieve their orders via GET /order.php

---

## Next Steps (Optional Enhancements)

1. **Orders History Page** - Display user's previous orders
2. **Order Status Tracking** - Show order status (pending, processing, shipped, etc.)
3. **Payment Integration** - Add payment method selection and processing
4. **Email Confirmation** - Send confirmation email after order
5. **Order Cancellation** - Allow users to cancel pending orders
6. **Address Management** - Add shipping address form to order
7. **Order Notifications** - Real-time status updates

---

## Notes

- JWT token is sent in `Authorization: Bearer <token>` header
- Backend uses `$payload->sub` to extract user ID from JWT
- Cart items are stored as JSON snapshot in database
- Totals are denormalized for quick reads
- Order status defaults to 'pending'
- Soft-delete not implemented; consider adding `is_deleted` column if needed

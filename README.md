# Vue 3 E-Commerce Application

A full-stack e-commerce platform built with Vue 3, Vite, and PHP with JWT authentication, role-based access control, shopping cart, and order management.

---

## 🚀 Quick Start

### Prerequisites
- **Node.js** 16+ with npm
- **XAMPP** (Apache + MySQL) or equivalent
- **PHP** 7.4+
- **Composer** for PHP dependencies

### Installation

#### 1. Backend Setup

```bash
# Navigate to APIs directory
cd apis

# Install PHP dependencies
composer install

# Create .env file with configuration
# Copy the template below and create apis/.env
```

**Create `apis/.env` file:**
```env
APP_URL=http://localhost

DB_HOST=localhost
DB_NAME=r68_vue3_shop
DB_USER=root
DB_PASS=

JWT_ACCESS_SECRET=e2f5c8b1a9d4f7e3c6b8a1d9f2e5c8b1a9d4f7e3c6b8a1d9f2e5c8b1a9d4f7
JWT_REFRESH_SECRET=d9c8e7f6a5b4c3d2e1f0a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d4e3f2a1b0c
```

**Important**: JWT secrets must be at least 64 characters for security.

#### 2. Database Setup

```bash
# Import the database schema
mysql -u root r68_vue3_shop < apis/db/r68_vue3_shop.sql
```

Or create manually:
1. Open phpMyAdmin
2. Create database: `r68_vue3_shop`
3. Import: `apis/db/r68_vue3_shop.sql`

#### 3. Frontend Setup

```bash
# Install Node dependencies
npm install

# Create .env.local file in project root
# Copy the template below
```

**Create `.env.local` file in root directory:**
```env
VITE_API_URL=http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/
```

#### 4. Start Development Server

```bash
# Terminal 1: Start XAMPP (Apache + MySQL)
# Ensure both services are running

# Terminal 2: Start Vue dev server
npm run dev
```

**Output**:
```
VITE v4.x ready in xxx ms

➜  Local:   http://localhost:5173/
➜  press h to show help
```

---

## 🏗️ Project Structure

```
/class08/routing/
├── src/
│   ├── views/              # Page components
│   │   ├── Home.vue
│   │   ├── Product.vue
│   │   ├── Cart.vue
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   ├── PlaceOrder.vue
│   │   └── Orders.vue
│   ├── stores/             # Pinia state management
│   │   ├── auth.js
│   │   ├── product.js
│   │   ├── cart.js
│   │   └── order.js
│   ├── router/
│   │   └── index.js        # Vue Router with auth guards
│   ├── utils/
│   │   ├── apiClient.js
│   │   └── toast.js        # Toast notifications
│   ├── App.vue
│   └── main.js
│
├── apis/                   # PHP Backend
│   ├── .env               # Backend configuration (CREATE THIS)
│   ├── bootstrap.php
│   ├── login.php
│   ├── register.php
│   ├── order.php
│   ├── refresh.php
│   ├── src/
│   │   ├── Auth/
│   │   │   └── JwtAuth.php
│   │   └── Controllers/
│   │       └── orderController.php
│   └── db/
│       └── r68_vue3_shop.sql
│
├── .env.local             # Frontend env (CREATE THIS)
├── vite.config.js
├── package.json
└── index.html
```

---

## ⚙️ Environment Configuration

### Frontend (`.env.local`)

Place this file in the **root directory** (same level as `package.json`):

```env
VITE_API_URL=http://localhost/round68/VUE3/R68-Vue3/class08/routing/apis/
```

This URL is used in `src/stores/auth.js` to communicate with the backend.

### Backend (`apis/.env`)

Place this file in the **apis directory**:

```env
APP_URL=http://localhost

# Database Configuration
DB_HOST=localhost
DB_NAME=r68_vue3_shop
DB_USER=root
DB_PASS=

# JWT Secrets (MUST be 64+ characters for security)
JWT_ACCESS_SECRET=e2f5c8b1a9d4f7e3c6b8a1d9f2e5c8b1a9d4f7e3c6b8a1d9f2e5c8b1a9d4f7
JWT_REFRESH_SECRET=d9c8e7f6a5b4c3d2e1f0a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d4e3f2a1b0c
```

**Database Defaults**:
- Host: `localhost`
- User: `root` (default XAMPP)
- Password: `` (empty)
- Database: `r68_vue3_shop`

---

## 📝 Available Scripts

### Development
```bash
npm run dev      # Start dev server on http://localhost:5173
```

### Production
```bash
npm run build    # Build for production
npm run preview  # Preview production build
```

---

## 🔐 Authentication

### User Registration & Login

1. Navigate to `/register`
2. Create new account with username, email, password
3. Login at `/login`
4. JWT tokens stored in localStorage
5. Username appears in navbar

### Token Management

- **Access Token**: 15 minutes expiry
- **Refresh Token**: 7 days expiry
- **Auto-refresh**: Happens automatically before expiry
- **Logout**: Clears tokens and user data

---

## 👥 Role-Based Access

### User Roles
- **User** (default): Can browse products, add to cart, place orders, view own orders
- **Admin**: Can view all orders and change order status

### Protected Routes
- `/place-order` - Requires login
- `/orders` - Requires login

### Admin Features
- View all orders with customer information
- Update order status (pending → processing → shipped → delivered)
- Download/print invoices

---

## 🛒 Shopping Flow

1. **Browse Products** → `/product`
   - View product list
   - Filter by category (optional)
   - Click product for details

2. **Add to Cart**
   - Click "Add to Cart" button
   - Toast notification confirms
   - Cart count updates

3. **Review Cart** → `/cart`
   - View all items
   - Adjust quantities
   - Remove items
   - See total price

4. **Checkout** → `/place-order`
   - Review order before purchase (login required)
   - Click "Place Order"
   - Order stored in database

5. **View Orders** → `/orders`
   - See order history
   - View order items and totals
   - **Admin only**: Change order status
   - Click "Invoice" to view/print invoice

---

## 📄 Invoice Feature

### View Invoice
- Go to Orders page (`/orders`)
- Click "Invoice" button on any order
- Professional invoice modal opens

### Print or Download
- Click "Print Invoice" button
- Browser print dialog opens
- Select printer or "Save as PDF"
- Adjust settings and print/save

### Invoice Includes
- Order number and date
- Customer information
- Order items with images and prices
- Total calculations
- Company branding

---

## 🔗 API Endpoints

### Authentication
- `POST /apis/login.php` - User login
- `POST /apis/register.php` - User registration
- `POST /apis/refresh.php` - Refresh access token

### Orders
- `GET /apis/order.php` - Get user orders (or all if admin)
- `POST /apis/order.php` - Create new order
- `PUT /apis/order.php` - Update order status (admin only)

**All endpoints require `Authorization: Bearer {token}` header** (except login/register)

---

## 🎨 UI Components & Features

### Toast Notifications
- Success (2.5s): Order placed, login successful
- Error (3.5s): Failed operations
- Warning (3s): Destructive actions
- Info (2.5s): General information

### Navigation
- Navbar with shopping cart
- Authentication dropdown with user profile
- "Orders" link (when logged in)
- Responsive mobile menu

### Status Badges
- Pending (Yellow)
- Processing (Blue)
- Shipped (Purple)
- Delivered (Green)
- Cancelled (Red)

---

## 🧪 Testing

### Create Test Users
1. Register: `testuser` / `test@email.com` / `password123`
2. Register admin: `admin` / `admin@email.com` / `password123`
3. Promote to admin in database:
   ```sql
   UPDATE users SET role='admin' WHERE username='admin';
   ```

### Test Workflows
1. Register and login
2. Add items to cart
3. Place order
4. View orders
5. (As admin) Change order status
6. Print invoice

---

## 🐛 Troubleshooting

### Issue: "Cannot GET /apis/..."
**Solution**: Check `.env.local` `VITE_API_URL` points to correct backend URL

### Issue: "401 Unauthorised" on protected endpoints
**Solution**: 
- Login again
- Check `.env.local` is properly set
- Clear browser cache

### Issue: "CORS policy blocked"
**Solution**: Backend CORS headers are configured, ensure API URL includes trailing slash

### Issue: Database connection failed
**Solution**:
- Check MySQL is running
- Verify `apis/.env` DB credentials
- Run database import: `mysql -u root r68_vue3_shop < apis/db/r68_vue3_shop.sql`

### Issue: Images not loading
**Solution**: Product images use external URLs, check internet connection

---

## 📚 Documentation Files

- **IMPLEMENTATION_STATUS.md** - Complete feature list and status
- **TESTING_GUIDE.md** - Detailed testing procedures
- **DEVELOPER_REFERENCE.md** - Code patterns and API reference
- **INVOICE_FEATURE.md** - Invoice modal documentation
- **AUTH_IMPLEMENTATION.md** - Authentication details
- **QUICK_AUTH_REFERENCE.md** - Quick auth reference

---

## 🚀 Deployment

### Before Deploying
- [ ] `.env` files configured with production URLs
- [ ] Database backed up
- [ ] JWT secrets are strong (64+ characters)
- [ ] CORS origins updated for production domain
- [ ] All tests passing
- [ ] No console errors

### Production Build
```bash
npm run build
# Creates dist/ folder ready for deployment
```

---

## 📦 Dependencies

### Frontend
- `vue@3.x` - Progressive JavaScript framework
- `vue-router@4.x` - Client-side routing
- `pinia@2.x` - State management
- `vue3-toastify@0.x` - Toast notifications
- `bootstrap@5.x` - CSS framework (via CDN)

### Backend
- `firebase/php-jwt` - JWT token handling
- `PHP 7.4+` - Server-side language
- `MySQL 5.7+` - Database

---

## 🔒 Security

✅ JWT tokens with 64-character secrets
✅ Password hashing with bcrypt
✅ Protected routes with auth guards
✅ Role-based access control
✅ CORS headers configured
✅ SQL prepared statements

---

## 📞 Support

For issues or questions:
1. Check documentation files (TESTING_GUIDE.md, DEVELOPER_REFERENCE.md)
2. Check browser console for errors
3. Check network tab in DevTools
4. Verify `.env` files are correctly configured

---

## 📄 License

This project is provided as-is for educational purposes.

---

**Last Updated**: June 2026
**Status**: ✅ Production Ready
**Version**: 1.0

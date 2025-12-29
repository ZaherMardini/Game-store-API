## 🎮 Game Store E-commerce API
A RESTful e-commerce backend API built with Laravel, focused on real-world business logic such as guest cart handling, cart merging on authentication, transactional checkout, and price snapshotting.
This project is designed as a backend-only system intended to be consumed by web or mobile clients.

## API Documentation You can explore the full API documentation here: [View on Postman](https://documenter.getpostman.com/view/50474404/2sBXVbGDgv)

## 🏗 Architecture

- Backend-only REST API (no views)
- Laravel + Eloquent ORM
- Sanctum authentication
- Stateless API design
- Service-layer business logic (cart & checkout)
- Transaction-safe operations

## 🔐 Authentication

The API supports both **guest users** and **authenticated users**.

- Guests interact with the system using a temporary `guest_token`
- Authenticated users are managed via Laravel Sanctum
- Certain endpoints (browsing products, categories) are publicly accessible
- Sensitive operations (checkout, orders) require authentication

## 🛒 Cart Lifecycle

The cart system supports a full guest-to-user lifecycle:

### Guest Cart
- A cart is created for guests using a temporary `guest_token`
- Guests can add and remove items without authentication

### Cart Merge on Login
When a guest user logs in:
- If the user has no existing cart, the guest cart is assigned to the user
- If the user already has a cart:
  - Matching products have their quantities merged
  - New products are transferred
  - Duplicate cart items are prevented
- The guest cart is removed after a successful merge

"Design option": scheduled jobs to remove unused temp carts from the database after X days for example.

This logic ensures a seamless user experience similar to real-world e-commerce platforms.


## 💳 Checkout Workflow

The checkout process is handled inside a transactional service to ensure data consistency.

Workflow:
1. Validate cart items and quantities
2. Create a new order in a pending state
3. Convert cart items into order items
4. Capture product prices at the time of purchase
5. Calculate and persist the final order total
6. Commit the transaction atomically

If any step fails, the entire checkout is rolled back.

## 💰 Price Snapshotting

To preserve historical accuracy:
- Product prices are copied into `order_items.price_when_purchased`
- Order totals are calculated using these snapshot prices
- Future price changes do not affect existing orders

This ensures order data remains consistent and auditable.

## 🧪 Testing Strategy

The project focuses on testing **critical business flows** rather than superficial endpoint coverage.

Key tested scenarios include:
- Guest cart merging into an authenticated user cart
- Prevention of duplicate cart items
- Quantity synchronization during merges

Tests are implemented as feature tests using an isolated in-memory SQLite database.


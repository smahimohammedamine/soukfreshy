# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project
SoukFreshy — French-language Algerian fresh produce marketplace (prices in DA). XAMPP (Apache + MySQL/MariaDB). URLs: `http://localhost/soukfreshy/` (consumer) · `http://localhost/soukfreshy/user/farmer-dashboard.php` (farmer) · `http://localhost/soukfreshy/admin/` (admin).

## Development setup
- Requires XAMPP running Apache + MySQL
- Import `soukfreshy.sql` via phpMyAdmin (`SOURCE soukfreshy.sql`) to create/reset the schema
- `api/config/db.php` connects as `root` with no password to database `soukfreshy` — change before production
- No build step, no npm, no compilation — edit files directly and reload the browser

## Architecture

Three separate interfaces, each with its own auth session:

| Interface | Entry point | Session key |
|-----------|-------------|-------------|
| Consumer SPA | `index.php` + `user/script.js` + `user/auth.js` | `$_SESSION['user_id']` + role `consumer` |
| Farmer Dashboard | `user/farmer-dashboard.php` + `user/farmer-dashboard.js` | `$_SESSION['user_id']` + role `farmer` |
| Admin Panel | `admin/index.php` + `admin/admin.js` | `$_SESSION['admin_id']` |

**Consumer SPA** — single-page app; all navigation is via `showPage(name)` in `user/script.js`. Pages: `home`, `shop`, `product`, `cart`, `orders`, `wishlist`, `profile`, `boxes`. GSAP handles splash and transitions. `$ = id => document.getElementById(id)` is the DOM helper throughout.

**Farmer Dashboard** — a standalone PHP page (PHP redirects to `/?login=farmer` if not authenticated). Farmer identity is injected via a hidden `#farmer-data` element with `data-id`, `data-name`, `data-wilaya` attributes read by `farmer-dashboard.js`.

**Admin Panel** — login screen toggled via JS (`#login-screen` / `#app`). `showSection(name)` controls panel navigation. All API routes are collected in a top-level `API` constant in `admin/admin.js`.

## Backend API

All endpoints: return JSON, call `session_start()`, use PDO via `getDB()`. Auth guard pattern — check session at top, `http_response_code(401)` + `exit` if missing.

| Path | Notes |
|------|-------|
| `api/config/db.php` | `getDB()` — singleton PDO |
| `api/auth/` | `login.php`, `register.php`, `logout.php`, `session.php` |
| `api/products.php` | Public product listing (no auth) |
| `api/cart/` | `get.php`, `update.php`, `clear.php` (consumer) |
| `api/orders/checkout.php` | Places order; calculates fees from `settings` table; generates `SF-XXX` order number in PHP (not DB trigger — MariaDB can't UPDATE same table in trigger) |
| `api/orders/list.php` | Consumer order history |
| `api/wishlist/` | `get.php`, `update.php` |
| `api/reviews/` | `get.php`, `submit.php`, `delete.php` |
| `api/weekly-boxes.php` | Public listing; `?category=weekly\|season\|all`; auto-filters season boxes by date window |
| `api/boxes/` | `subscribe.php`, `my-subscriptions.php` (consumer) |
| `api/farmer/` | `products.php`, `product-save.php`, `product-delete.php` (farmer role) |
| `api/admin/auth/` | `login.php`, `logout.php`, `session.php` |
| `api/admin/` | `dashboard.php`, `orders.php`, `products.php`, `users.php`, `categories.php`, `weekly-boxes.php`, `settings.php`, `admins.php`, `notifications.php`, `client-of-week.php` |

## Database

Schema source: `soukfreshy.sql`. All tables use InnoDB + utf8mb4.

Key tables and their purpose:

| Table | Notes |
|-------|-------|
| `users` | consumers + farmers; `role ENUM('consumer','farmer')`; bcrypt passwords |
| `admin_users` | separate table for admin accounts; bcrypt passwords |
| `products` | `farmer_id NULL` = platform product; `farmer_id SET` = farmer-submitted; `image` stores URL or base64 data-URI |
| `categories` | slugs: `vegetables`, `fruits`, `herbs` |
| `orders` | `order_number` is `SF-NNN` set by `checkout.php` after insert (not a DB trigger) |
| `order_items` | price snapshots at order time; `product_id` or `box_id` (one is NULL) |
| `cart_items` | `product_id` or `box_id` (one is NULL per row) |
| `wishlist_items` | — |
| `product_reviews` | — |
| `weekly_boxes` | `category ENUM('weekly','season')`; season boxes have `available_from`/`available_until` date window; `products` column is JSON array |
| `client_of_week` | weekly top-buyer award; `week_start` = last Monday |
| `settings` | key/value store for `commission_rate`, `delivery_fee`, `free_delivery_minimum`, `service_fee` — read by `checkout.php` at order time |

## Tech stack
- Tailwind CSS (CDN, configured inline in `index.php`), GSAP 3.12.2, Font Awesome 6.5.0, Google Fonts (Playfair Display + Nunito)
- Admin panel uses plain CSS (`admin/style.css`) — no Tailwind
- Farmer dashboard uses `user/farmer-dashboard.css`
- Consumer SPA uses `user/styles.css` + `user/auth.css`

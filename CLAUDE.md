# CLAUDE.md

## Project
SoukFreshy — French-language Algerian fresh produce marketplace (prices in DA). XAMPP (Apache + MySQL). URLs: `http://localhost/soukfreshy/user/` · `http://localhost/soukfreshy/admin/`.

## Architecture
No build tools. Three interfaces:

| Interface | Entry point |
|-----------|-------------|
| Consumer SPA | `index.php` + `user/script.js` + `user/auth.js` |
| Farmer Dashboard | `user/farmer-dashboard.php` + `user/farmer-dashboard.js` |
| Admin Panel | `admin/index.php` + `admin/admin.js` |

SPA routing via `showPage(name)`. Styles in `user/styles.css`.

## Backend API
PHP endpoints under `api/` — all return JSON, use `session_start()`, PDO.

| Folder | Endpoints |
|--------|-----------|
| `api/config/db.php` | `getDB()` helper |
| `api/cart/` | `get.php`, `update.php`, `clear.php` |
| `api/wishlist/` | `get.php`, `update.php` |
| `api/reviews/` | `get.php`, `submit.php`, `delete.php` |
| `api/orders/` | `place.php`, `list.php` |
| `api/auth/` | `login.php`, `register.php`, `logout.php`, `session.php` |

## Database
Schema: `soukfreshy.sql`. Key tables: `users`, `products`, `categories`, `product_reviews`, `orders`, `order_items`, `cart_items`, `wishlist_items`. Orders trigger auto-generates `SF-XXX` numbers.

Roles: `consumer` / `farmer` in `users`; `admin` in `admin_users`. Passwords: bcrypt via `password_hash()`.

## Tech Stack
- Tailwind CSS (CDN), GSAP 3.12.2, Font Awesome 6.5.0, Google Fonts (Playfair Display + Nunito)
- No npm, no compilation

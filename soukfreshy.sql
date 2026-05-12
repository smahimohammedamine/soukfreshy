-- ================================================================
--  SOUKFRESHY — Database Schema
--  Engine : InnoDB
--  Charset: utf8mb4 / utf8mb4_unicode_ci
--  Usage  : SOURCE soukfreshy.sql  (in phpMyAdmin or mysql CLI)
-- ================================================================

CREATE DATABASE IF NOT EXISTS soukfreshy
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE soukfreshy;

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------
-- 1. CATEGORIES
--    Matches the three categories used in script.js / admin.js
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id       TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug     VARCHAR(50)      NOT NULL UNIQUE,   -- 'vegetables','fruits','herbs'
  name_fr  VARCHAR(100)     NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categories (slug, name_fr) VALUES
  ('vegetables', 'Légumes'),
  ('fruits',     'Fruits'),
  ('herbs',      'Herbes aromatiques');

-- ----------------------------------------------------------------
-- 2. USERS  (consumers + farmers)
--    Mirrors localStorage soukfreshy_users_v1 and the session object.
--    Passwords must be stored as bcrypt hashes (password_hash() in PHP).
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  full_name  VARCHAR(150)    NOT NULL,
  phone      VARCHAR(20)     NOT NULL UNIQUE,
  email      VARCHAR(180)             DEFAULT NULL UNIQUE,
  password   VARCHAR(255)    NOT NULL,          -- bcrypt via password_hash()
  role       ENUM('consumer','farmer') NOT NULL DEFAULT 'consumer',
  wilaya     VARCHAR(100)             DEFAULT NULL,   -- farmer: province
  commune    VARCHAR(100)             DEFAULT NULL,   -- farmer: municipality
  is_active  TINYINT(1)      NOT NULL DEFAULT 1,
  created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 3. ADMIN USERS  (separate from consumers/farmers)
--    The single admin account that operates the admin/ panel.
--    Replace the placeholder hash before going live.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username   VARCHAR(80)      NOT NULL UNIQUE,
  password   VARCHAR(255)     NOT NULL,          -- bcrypt hash
  created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Placeholder row — update password hash before first use
INSERT IGNORE INTO admin_users (username, password)
VALUES ('admin', '$2y$12$CHANGE_THIS_HASH_BEFORE_USE_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

-- ----------------------------------------------------------------
-- 4. PRODUCTS
--    farmer_id NULL  → platform catalogue product (seeded below)
--    farmer_id set   → farmer-submitted product (from farmer-dashboard)
--    image stores a URL or a base64 data-URI (upload case)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  farmer_id     INT UNSIGNED              DEFAULT NULL,
  category_id   TINYINT UNSIGNED NOT NULL,
  name          VARCHAR(150)     NOT NULL,
  description   TEXT                      DEFAULT NULL,
  price         DECIMAL(10,2)    NOT NULL,
  pricing_type  ENUM('kg','custom') NOT NULL DEFAULT 'kg',
  pricing_label VARCHAR(50)      NOT NULL DEFAULT 'kg',   -- 'kg', 'bouquet', …
  available_qty INT UNSIGNED     NOT NULL DEFAULT 0,
  region        VARCHAR(100)              DEFAULT NULL,   -- Algerian wilaya name
  image         TEXT                      DEFAULT NULL,
  availability  ENUM('available','limited','out') NOT NULL DEFAULT 'available',
  rating        DECIMAL(2,1)     NOT NULL DEFAULT 0.0,
  review_count  INT UNSIGNED     NOT NULL DEFAULT 0,
  is_active     TINYINT(1)       NOT NULL DEFAULT 1,
  created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_category     (category_id),
  KEY idx_products_farmer       (farmer_id),
  KEY idx_products_availability (availability),
  CONSTRAINT fk_products_farmer   FOREIGN KEY (farmer_id)   REFERENCES users(id)       ON DELETE SET NULL,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)  ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed: platform catalogue (mirrors PRODUCTS array in script.js) ──
INSERT IGNORE INTO products
  (id, farmer_id, category_id, name, description, price, pricing_type, pricing_label, available_qty, region, image, availability, rating, review_count)
VALUES
  (1,  NULL, 1, 'Tomates fraîches',        'Tomates rouges et juteuses récoltées ce matin dans les serres de Mostaganem. Idéales pour les salades, les sauces et la cuisine algérienne traditionnelle. Cultivées sans pesticides excessifs.',                                        120.00, 'kg',     'kg',      85,  'Mostaganem', 'https://images.unsplash.com/photo-1546470427-0d16e7b69b4d?w=500&q=80',  'available', 4.8, 142),
  (2,  NULL, 1, 'Pommes de terre',          'Pommes de terre de qualité supérieure, cultivées dans les terres fertiles d\'Aïn Defla. Parfaites pour la friture, les ragoûts et la chorba traditionnelle.',                                                                           80.00, 'kg',     'kg',      200, 'Aïn Defla',  'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=500&q=80', 'available', 4.5, 98),
  (3,  NULL, 1, 'Carottes de la Mitidja',   'Carottes orangées et croquantes de la plaine de la Mitidja. Riches en bêta-carotène, récoltées à la main pour préserver leur fraîcheur.',                                                                                               90.00, 'kg',     'kg',      120, 'Blida',       'https://images.unsplash.com/photo-1447175008436-054170c2e979?w=500&q=80', 'available', 4.6, 76),
  (4,  NULL, 1, 'Courgettes',               'Courgettes tendres et savoureuses des collines de Médéa. Parfaites pour les tajines, les farces et les plats mijotés du quotidien.',                                                                                                    100.00, 'kg',    'kg',      15,  'Médéa',       'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=500&q=80',  'limited',   4.4, 54),
  (5,  NULL, 1, 'Piments rouges',           'Piments rouges ardents d\'Annaba, idéaux pour préparer la harissa maison. Relevés et parfumés, ils subliment toutes vos préparations culinaires.',                                                                                      150.00, 'kg',    'kg',      60,  'Annaba',      'https://images.unsplash.com/photo-1585400823485-b5fc3e49d98e?w=500&q=80', 'available', 4.7, 89),
  (6,  NULL, 1, 'Oignons dorés',            'Oignons dorés de Relizane, base incontournable de toute la cuisine algérienne. Saveur intense et légèrement sucrée, conservation longue durée.',                                                                                         70.00, 'kg',    'kg',      300, 'Relizane',    'https://images.unsplash.com/photo-1508747703725-719777637510?w=500&q=80', 'available', 4.3, 112),
  (7,  NULL, 2, 'Oranges de Blida',         'Les célèbres oranges de Blida, réputées dans tout le pays pour leur douceur incomparable et leur parfum enivrant. Riches en vitamine C et en antioxydants.',                                                                            130.00, 'kg',    'kg',      180, 'Blida',       'https://images.unsplash.com/photo-1547036967-23d11aacaee0?w=500&q=80',  'available', 4.9, 203),
  (8,  NULL, 2, 'Pommes de Batna',          'Pommes croquantes et sucrées cultivées en altitude dans les Aurès. Le froid des nuits de Batna leur confère une saveur et une texture incomparables.',                                                                                   180.00, 'kg',    'kg',      95,  'Batna',       'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=500&q=80',  'available', 4.7, 135),
  (9,  NULL, 2, 'Raisins de Mostaganem',    'Raisins blancs et noirs de Mostaganem, récoltés à parfaite maturité dans les vignobles historiques de la région. Doux, charnus et juteux à souhait.',                                                                                   200.00, 'kg',    'kg',      8,   'Mostaganem', 'https://images.unsplash.com/photo-1537640538966-79f369143f8f?w=500&q=80', 'limited',   4.8, 167),
  (10, NULL, 2, 'Figues de Béjaïa',         'Figues fraîches de Kabylie, un véritable trésor de Béjaïa. Naturellement sucrées, très nutritives et séchées à l\'ancienne selon les traditions berbères.',                                                                             220.00, 'kg',    'kg',      0,   'Béjaïa',      'https://images.unsplash.com/photo-1601004890684-d8cbf643f5f2?w=500&q=80', 'out',       4.9, 88),
  (11, NULL, 3, 'Menthe fraîche',            'Menthe fraîche et parfumée de la plaine de la Mitidja. Indispensable pour le thé traditionnel algérien, les salades et de nombreuses recettes populaires.',                                                                              60.00, 'custom','bouquet', 50,  'Mitidja',     'https://images.unsplash.com/photo-1628556270448-4d4e4148e1b1?w=500&q=80', 'available', 4.6, 241),
  (12, NULL, 3, 'Khobiza sauvage',           'Khobiza (mauve sauvage) cueillie à la main dans les champs de Tlemcen. Utilisée en soupe, en salade ou en accompagnement, c\'est un ingrédient phare de la cuisine oranaise.',                                                         50.00, 'custom','bouquet', 12,  'Tlemcen',     'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=500&q=80',  'limited',   4.4, 63);

-- ----------------------------------------------------------------
-- 5. PRODUCT REVIEWS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_reviews (
  id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED     NOT NULL,
  user_id    INT UNSIGNED     NOT NULL,
  rating     TINYINT UNSIGNED NOT NULL,   -- 1–5
  comment    TEXT                         DEFAULT NULL,
  created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_review_user_product (user_id, product_id),
  KEY idx_review_product (product_id),
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT chk_rating         CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 6. ORDERS
--    order_number (SF-001 format) is set automatically by trigger.
--    consumer_name is a snapshot in case the account is later deleted.
--    commission_rate is snapshotted from settings at order creation time.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  order_number     VARCHAR(20)            DEFAULT NULL UNIQUE,
  consumer_id      INT UNSIGNED           DEFAULT NULL,
  consumer_name    VARCHAR(150)  NOT NULL,
  consumer_phone   VARCHAR(20)            DEFAULT NULL,
  wilaya           VARCHAR(100)  NOT NULL,
  commune          VARCHAR(100)  NOT NULL DEFAULT '',
  delivery_address TEXT                   DEFAULT NULL,
  subtotal         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  delivery_fee     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  service_fee      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  commission_rate  DECIMAL(5,2)  NOT NULL DEFAULT 10.00,
  commission_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status           ENUM('new','prep','delivered','cancelled') NOT NULL DEFAULT 'new',
  created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_orders_status     (status),
  KEY idx_orders_consumer   (consumer_id),
  KEY idx_orders_created_at (created_at),
  CONSTRAINT fk_orders_consumer FOREIGN KEY (consumer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- order_number (SF-001 format) is set by the application after INSERT
-- using: UPDATE orders SET order_number = CONCAT('SF-', LPAD(id, 3, '0')) WHERE id = :id
-- A AFTER INSERT trigger cannot UPDATE the same table in MariaDB (error 1442).

-- ----------------------------------------------------------------
-- 7. ORDER ITEMS
--    product_name, unit_price, pricing_label are snapshotted so the
--    order record stays accurate even if the product is later edited.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  order_id      INT UNSIGNED  NOT NULL,
  product_id    INT UNSIGNED           DEFAULT NULL,   -- NULL if product deleted
  farmer_id     INT UNSIGNED           DEFAULT NULL,
  product_name  VARCHAR(150)  NOT NULL,
  unit_price    DECIMAL(10,2) NOT NULL,
  pricing_label VARCHAR(50)   NOT NULL DEFAULT 'kg',
  qty           INT UNSIGNED  NOT NULL,
  line_total    DECIMAL(10,2) NOT NULL,                -- unit_price × qty
  PRIMARY KEY (id),
  KEY idx_items_order   (order_id),
  KEY idx_items_product (product_id),
  KEY idx_items_farmer  (farmer_id),
  CONSTRAINT fk_items_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  CONSTRAINT fk_items_farmer  FOREIGN KEY (farmer_id)  REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 8. CART ITEMS  (server-side cart, replaces localStorage cart)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart_items (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  qty        INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cart_user_product (user_id, product_id),
  CONSTRAINT fk_cart_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 9. WISHLIST
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlist_items (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_wish_user_product (user_id, product_id),
  CONSTRAINT fk_wish_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_wish_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 10. PLATFORM SETTINGS
--     Mirrors the `settings` object in admin.js.
--     Keys are fixed strings; values are always stored as strings.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  setting_key   VARCHAR(80)  NOT NULL,
  setting_value VARCHAR(255) NOT NULL,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
  ('commission_rate',        '10'),     -- percentage applied to order subtotal
  ('delivery_fee',           '200'),    -- DA, charged when subtotal < free_delivery_minimum
  ('free_delivery_minimum',  '2000'),   -- DA, threshold for free delivery
  ('service_fee',            '50'),     -- DA per order
  ('service_fee_mode',       'fixed');  -- 'fixed' | 'percent'

-- ----------------------------------------------------------------
-- 11. NOTIFICATIONS
--     related_id stores an order_number (e.g. 'SF-007') or user id
--     as a string so both types fit without a union structure.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  type       ENUM('order','user','alert') NOT NULL DEFAULT 'order',
  message    TEXT         NOT NULL,
  related_id VARCHAR(30)           DEFAULT NULL,
  is_read    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notif_is_read (is_read),
  KEY idx_notif_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 12. WEEKLY BOXES
--     Curated boxes assembled by admins from surplus harvest products.
--     products column stores a JSON array of product name strings.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS weekly_boxes (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  title       VARCHAR(200)  NOT NULL,
  description TEXT                   DEFAULT NULL,
  image       TEXT                   DEFAULT NULL,
  price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity    INT UNSIGNED  NOT NULL DEFAULT 0,
  products    TEXT          NOT NULL DEFAULT '[]',  -- JSON array of product name strings
  box_type    VARCHAR(100)  NOT NULL DEFAULT 'mixed',
  is_active      TINYINT(1)    NOT NULL DEFAULT 1,
  free_delivery  TINYINT(1)    NOT NULL DEFAULT 0,
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_boxes_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
--  SCHEMA UPGRADES — Weekly + Season Boxes (run once)
-- ================================================================

-- ── Upgrade weekly_boxes: new columns for category / season / dates / badges / stats ──
ALTER TABLE weekly_boxes
  ADD COLUMN IF NOT EXISTS category        ENUM('weekly','season') NOT NULL DEFAULT 'weekly'              AFTER id,
  ADD COLUMN IF NOT EXISTS original_price  DECIMAL(10,2)           NULL                                   AFTER price,
  ADD COLUMN IF NOT EXISTS season          ENUM('spring','summer','autumn','winter') NULL                  AFTER box_type,
  ADD COLUMN IF NOT EXISTS available_from  DATE                    NULL                                   AFTER season,
  ADD COLUMN IF NOT EXISTS available_until DATE                    NULL                                   AFTER available_from,
  ADD COLUMN IF NOT EXISTS badge           VARCHAR(100)            NULL                                   AFTER free_delivery,
  ADD COLUMN IF NOT EXISTS orders_count    INT UNSIGNED            NOT NULL DEFAULT 0;

-- ── Upgrade cart_items: make product_id nullable, add box_id ──
ALTER TABLE cart_items
  MODIFY COLUMN product_id INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS box_id INT UNSIGNED NULL DEFAULT NULL AFTER product_id,
  ADD CONSTRAINT IF NOT EXISTS fk_cart_box FOREIGN KEY (box_id) REFERENCES weekly_boxes(id) ON DELETE CASCADE;

-- ── Upgrade order_items: add box_id column ──
ALTER TABLE order_items
  ADD COLUMN IF NOT EXISTS box_id INT UNSIGNED NULL DEFAULT NULL AFTER product_id,
  ADD CONSTRAINT IF NOT EXISTS fk_items_box FOREIGN KEY (box_id) REFERENCES weekly_boxes(id) ON DELETE SET NULL;

-- ── Box subscriptions (recurring weekly delivery) ──
CREATE TABLE IF NOT EXISTS box_subscriptions (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  box_id           INT UNSIGNED NOT NULL,
  user_id          INT UNSIGNED NOT NULL,
  delivery_wilaya  VARCHAR(100) NOT NULL DEFAULT '',
  delivery_commune VARCHAR(100) NOT NULL DEFAULT '',
  delivery_address TEXT,
  frequency        ENUM('weekly','biweekly') NOT NULL DEFAULT 'weekly',
  status           ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
  next_delivery    DATE NULL,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sub_user_box (user_id, box_id),
  KEY idx_sub_box  (box_id),
  KEY idx_sub_user (user_id),
  CONSTRAINT fk_sub_box  FOREIGN KEY (box_id)  REFERENCES weekly_boxes(id) ON DELETE CASCADE,
  CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Client of the week (admin assigns a free pack to a chosen client) ──
CREATE TABLE IF NOT EXISTS client_of_week (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  box_id     INT UNSIGNED NOT NULL,
  week_start DATE         NOT NULL,
  note       TEXT,
  status     ENUM('active','delivered','cancelled') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cow_user (user_id),
  KEY idx_cow_week (week_start),
  CONSTRAINT fk_cow_user FOREIGN KEY (user_id) REFERENCES users(id)         ON DELETE CASCADE,
  CONSTRAINT fk_cow_box  FOREIGN KEY (box_id)  REFERENCES weekly_boxes(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================================================
-- Swaply Database Schema
-- C2C E-Commerce Platform for Informal Township Traders
-- ============================================================

CREATE DATABASE IF NOT EXISTS swaply CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE swaply;

-- ============================================================
-- 1. USERS TABLE
-- Stores buyers, sellers, and admins
-- ============================================================
CREATE TABLE users (
    user_id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(120) NOT NULL UNIQUE,
    phone           VARCHAR(20)  NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('buyer','seller','admin') DEFAULT 'buyer',
    verified        TINYINT(1) DEFAULT 0,
    email_verified  TINYINT(1) DEFAULT 0,
    email_code      VARCHAR(10) DEFAULT NULL,
    id_verified     TINYINT(1) DEFAULT 0,
    first_order     TINYINT(1) DEFAULT 1,
    id_number       VARCHAR(20) DEFAULT NULL,
    id_document     VARCHAR(255) DEFAULT NULL,
    profile_image   VARCHAR(255) DEFAULT 'default_avatar.png',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. CATEGORIES TABLE
-- Product categories
-- ============================================================
CREATE TABLE categories (
    category_id     INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(50) NOT NULL,
    description     VARCHAR(255),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. PRODUCTS TABLE
-- Items listed by sellers
-- ============================================================
CREATE TABLE products (
    product_id      INT AUTO_INCREMENT PRIMARY KEY,
    seller_id       INT NOT NULL,
    category_id     INT,
    name            VARCHAR(150) NOT NULL,
    description     TEXT,
    price           DECIMAL(10,2) NOT NULL,
    stock           INT DEFAULT 0,
    image           VARCHAR(255) DEFAULT 'default_product.png',
    status          ENUM('active','inactive','sold_out') DEFAULT 'active',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 4. ORDERS TABLE
-- Customer orders
-- ============================================================
CREATE TABLE orders (
    order_id        INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id        INT NOT NULL,
    total           DECIMAL(10,2) NOT NULL,
    discount        DECIMAL(10,2) DEFAULT 0,
    final_total     DECIMAL(10,2) NOT NULL,
    status          ENUM('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method  VARCHAR(50) DEFAULT 'card',
    transaction_ref VARCHAR(50) NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. ORDER_ITEMS TABLE
-- Individual items within an order
-- ============================================================
CREATE TABLE order_items (
    item_id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    product_id      INT NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    price_at_time   DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. REVIEWS TABLE
-- Buyer reviews of sellers after orders
-- ============================================================
CREATE TABLE reviews (
    review_id       INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    seller_id       INT NOT NULL,
    buyer_id        INT NOT NULL,
    rating          INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment         TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. CART TABLE (Session-backed, but persisted for logged-in users)
-- ============================================================
CREATE TABLE cart (
    cart_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    product_id      INT NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    added_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Phones, accessories, gadgets'),
('Clothing', 'Men, women, and kids fashion'),
('Food & Groceries', 'Fresh produce, snacks, beverages'),
('Home & Kitchen', 'Appliances, cookware, decor'),
('Beauty & Health', 'Cosmetics, skincare, wellness'),
('Services', 'Repair, tutoring, transport');

-- Sample Users (password = 'Password123!' for all)
-- Admin
INSERT INTO users (full_name, email, phone, password_hash, role, verified, email_verified, id_verified) VALUES
('Admin User', 'admin@swaply.co.za', '0710000000', '$2b$12$S0ThPaDjQU.tkDztc5U51Osyw5H59dovP3aEJlz65mXy2RzeOPu42', 'admin', 1, 1, 1);

-- Sellers (email + ID verified)
INSERT INTO users (full_name, email, phone, password_hash, role, verified, email_verified, id_verified, id_number) VALUES
('Thabo Mokoena', 'thabo@email.com', '0721234567', '$2b$12$S0ThPaDjQU.tkDztc5U51Osyw5H59dovP3aEJlz65mXy2RzeOPu42', 'seller', 1, 1, 1, '8501015800085'),
('Lerato Dlamini', 'lerato@email.com', '0732345678', '$2b$12$S0ThPaDjQU.tkDztc5U51Osyw5H59dovP3aEJlz65mXy2RzeOPu42', 'seller', 1, 1, 1, '9005120094082'),
('Sipho Nkosi', 'sipho@email.com', '0743456789', '$2b$12$S0ThPaDjQU.tkDztc5U51Osyw5H59dovP3aEJlz65mXy2RzeOPu42', 'seller', 1, 1, 1, '8803245713081');

-- Buyers (Nomsa: email+ID verified, Bongani: email verified, ID pending)
INSERT INTO users (full_name, email, phone, password_hash, role, verified, email_verified, id_verified, id_number) VALUES
('Nomsa Tshabalala', 'nomsa@email.com', '0754567890', '$2b$12$S0ThPaDjQU.tkDztc5U51Osyw5H59dovP3aEJlz65mXy2RzeOPu42', 'buyer', 1, 1, 1, '9508150927083'),
('Bongani Mthembu', 'bongani@email.com', '0765678901', '$2b$12$S0ThPaDjQU.tkDztc5U51Osyw5H59dovP3aEJlz65mXy2RzeOPu42', 'buyer', 1, 1, 0, '0203045671081');

-- Sample Products
INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status) VALUES
(2, 1, 'Samsung Galaxy A14 128GB', 'Brand new sealed Samsung Galaxy A14. 6.6" display, 5000mAh battery. Comes with charger and 1 year warranty.', 3499.00, 5, 'phone1.jpg', 'active'),
(2, 1, 'iPhone 13 128GB', 'Pre-owned iPhone 13 in excellent condition. Battery health 92%. No scratches on screen.', 8999.00, 2, 'phone2.jpg', 'active'),
(3, 1, 'Bluetooth Speaker', 'Portable wireless speaker with 12-hour battery life. Water resistant. Great sound quality.', 450.00, 10, 'speaker.jpg', 'active'),
(2, 2, 'Denim Jacket - Large', 'Vintage style denim jacket, size Large. Brand new with tags. Great for winter.', 350.00, 3, 'jacket.jpg', 'active'),
(3, 2, 'Running Shoes Size 9', 'Nike running shoes, size 9. Worn twice, excellent condition. Original box included.', 800.00, 1, 'shoes.jpg', 'active'),
(2, 3, '10kg Maize Meal', 'Ace maize meal 10kg bag. Fresh stock, best before Dec 2026.', 89.99, 20, 'maize.jpg', 'active'),
(3, 3, 'Fresh Vegetable Box', 'Mixed seasonal vegetables: spinach, tomatoes, onions, peppers, cabbage. Delivered fresh.', 120.00, 8, 'vegbox.jpg', 'active'),
(2, 4, '2-Plate Gas Stove', 'Portable gas stove with 2 burners. Includes hose and regulator. Perfect for small spaces.', 450.00, 4, 'stove.jpg', 'active'),
(3, 5, 'Shea Butter 500ml', 'Pure organic shea butter. Great for skin and hair. 500ml tub.', 85.00, 15, 'sheabutter.jpg', 'active'),
(2, 6, 'Mathematics Tutoring', 'Grade 10-12 math tutoring. R150 per hour. Online or in-person in Soweto area.', 150.00, 10, 'tutor.jpg', 'active');

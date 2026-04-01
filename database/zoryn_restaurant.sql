CREATE DATABASE IF NOT EXISTS zoryn_restaurant CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE zoryn_restaurant;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','cashier','staff') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL,
    product_name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) NULL,
    unit VARCHAR(50) NOT NULL DEFAULT 'pcs',
    selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    current_stock DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS groceries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grocery_name VARCHAR(150) NOT NULL,
    unit VARCHAR(50) NOT NULL DEFAULT 'pcs',
    current_stock DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    latest_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_type ENUM('product','grocery') NOT NULL,
    item_id INT NOT NULL,
    movement_type ENUM('stock_in','stock_out') NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    remarks VARCHAR(255) NULL,
    movement_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    INDEX idx_item_type_item_id (item_type, item_id),
    CONSTRAINT fk_stock_movements_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS viands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    viand_name VARCHAR(150) NOT NULL,
    selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS viand_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    viand_id INT NOT NULL,
    grocery_id INT NOT NULL,
    quantity_needed DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_viand_ingredients_viand FOREIGN KEY (viand_id) REFERENCES viands(id) ON DELETE CASCADE,
    CONSTRAINT fk_viand_ingredients_grocery FOREIGN KEY (grocery_id) REFERENCES groceries(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(50) NOT NULL,
    sale_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_by INT NULL,
    CONSTRAINT fk_sales_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    viand_id INT NULL,
    product_id INT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    line_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    CONSTRAINT fk_sale_items_viand FOREIGN KEY (viand_id) REFERENCES viands(id) ON DELETE SET NULL,
    CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

INSERT INTO users (full_name, username, password, role)
VALUES ('System Administrator', 'admin', '$2y$12$OUA07b/jnoyUQUErNGYv0.eFTl5bwQB/WurJMGy6p6IemPUNc/Rji', 'admin')
ON DUPLICATE KEY UPDATE username = username;

INSERT INTO categories (category_name) VALUES ('Meals'), ('Beverages'), ('Add-ons')
ON DUPLICATE KEY UPDATE category_name = category_name;

INSERT INTO groceries (grocery_name, unit, current_stock, latest_cost)
VALUES
('Garlic', 'pcs', 3.00, 10.00),
('Chicken', 'kg', 5.00, 180.00),
('Soy Sauce', 'ml', 1000.00, 0.15)
ON DUPLICATE KEY UPDATE grocery_name = grocery_name;

INSERT INTO viands (viand_name, selling_price, description)
VALUES ('Garlic Chicken', 150.00, 'Sample viand for recipe costing demo')
ON DUPLICATE KEY UPDATE viand_name = viand_name;

INSERT INTO viand_ingredients (viand_id, grocery_id, quantity_needed)
SELECT 1, 1, 1.00
WHERE NOT EXISTS (SELECT 1 FROM viand_ingredients WHERE viand_id = 1 AND grocery_id = 1);


INSERT INTO users (full_name, username, password, role)
VALUES ('Cashier One', 'cashier1', '$2y$10$6YBdwApTjkP8ow6QvO0ZVevSx8y5m8A4R16vuMJnqd7qV3CyMfCVy', 'cashier')
ON DUPLICATE KEY UPDATE username = username;

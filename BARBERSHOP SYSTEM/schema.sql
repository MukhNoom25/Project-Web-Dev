-- MySQL schema for Barbershop POS + Booking (simple starter)
CREATE DATABASE IF NOT EXISTS barbershop_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barbershop_pos;

-- Users (admin/staff)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) UNIQUE,
  phone VARCHAR(30),
  role ENUM('admin', 'staff') DEFAULT 'staff',
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Staff (barbers)
CREATE TABLE IF NOT EXISTS staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(30),
  active TINYINT(1) DEFAULT 1
);

-- Services
CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  duration_min INT NOT NULL DEFAULT 30,
  price_cents INT NOT NULL DEFAULT 0,
  active TINYINT(1) DEFAULT 1
);

-- Products (optional retail items)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(60),
  name VARCHAR(120) NOT NULL,
  price_cents INT NOT NULL DEFAULT 0,
  stock_qty INT NOT NULL DEFAULT 0,
  active TINYINT(1) DEFAULT 1
);

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30),
  staff_id INT,
  service_id INT,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(id),
  FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Orders (POS)
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NULL,
  order_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_cents INT NOT NULL,
  payment_method ENUM('cash','card','ewallet','transfer') DEFAULT 'cash',
  status ENUM('paid','refunded') DEFAULT 'paid',
  created_by INT NULL,
  FOREIGN KEY (booking_id) REFERENCES bookings(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Order items
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_type ENUM('service','product') NOT NULL,
  item_id INT NULL,
  name VARCHAR(120) NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  unit_price_cents INT NOT NULL,
  line_total_cents INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Example seed data
INSERT INTO staff (name, phone) VALUES 
('Ali', '012-3456789'),
('Siti', '013-5551111');

INSERT INTO services (name, duration_min, price_cents) VALUES
('Basic Cut', 30, 1500),
('Premium Cut & Wash', 45, 2500),
('Beard Trim', 20, 1200);

INSERT INTO users (name, email, role, password_hash) VALUES
('Admin', 'admin@example.com', 'admin', SHA2('admin123', 256));

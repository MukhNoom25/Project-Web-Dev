CREATE DATABASE jppb_db;
USE jppb_db;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('user','hr') DEFAULT 'user'
);

-- Leave Requests
CREATE TABLE cuti_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    start_date DATE,
    end_date DATE,
    reason TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Attendance (Clock In/Out)
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    clock_in DATETIME,
    clock_out DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Barang Requests
CREATE TABLE barang_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    item_name VARCHAR(255),
    quantity INT,
    reason TEXT,
    status ENUM('pending','picked','not_picked') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- KPI Dataset
CREATE TABLE kpi_dataset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('speedtrap','jm_saman','rtx','contract'),
    value INT,
    date DATE
);

-- Create Default HR and User
INSERT INTO users (username, password, role) VALUES
('admin', PASSWORD('admin123'), 'hr'),
('user1', PASSWORD('user123'), 'user');

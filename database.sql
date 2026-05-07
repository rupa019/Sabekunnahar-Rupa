-- MeoWoof Database Setup
-- XAMPP এ phpMyAdmin এ গিয়ে এই SQL রান করুন

CREATE DATABASE IF NOT EXISTS meowoof_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE meowoof_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    role ENUM('Donator', 'Volunteer', 'Vet', 'Admin') NOT NULL DEFAULT 'Donator',
    address VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Test user (password: 123456)
INSERT INTO users (full_name, email, phone, role, address, password) VALUES
('Test User', 'test@example.com', '01711111111', 'Donator', 'Dhaka', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

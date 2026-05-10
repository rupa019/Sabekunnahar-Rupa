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
INSERT INTO users (full_name, email, phone, role, address, password) VALUES
('Sabekunnahar Rupa', 'sabekunnaharrupa99@gmail.com', '0180000000', 'Donator', 'Dhaka', 'Rupa/1234$#');

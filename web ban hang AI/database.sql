-- 1. Tạo cơ sở dữ liệu và sử dụng nó
CREATE DATABASE IF NOT EXISTS nova
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE nova;

-- ==========================================
-- 2. Tạo bảng Category (Danh mục sản phẩm)
-- ==========================================
CREATE TABLE Category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 3. Tạo bảng Product (Sản phẩm)
-- ==========================================
CREATE TABLE Product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL, -- Kiểu số thập phân cho giá tiền (VD: 99.99)
    stock_quantity INT DEFAULT 0,  -- Số lượng tồn kho
    image_url VARCHAR(255),        -- Đường dẫn ảnh sản phẩm
    category_id INT,               -- Khóa ngoại liên kết với bảng Category
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Ràng buộc khóa ngoại
    FOREIGN KEY (category_id) REFERENCES Category(category_id) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
);

-- ==========================================
-- 4. Tạo bảng Customer (Khách hàng)
-- ==========================================
-- Bảng này được thiết kế khớp với form register.html của bạn
CREATE TABLE Customer (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- Lưu mật khẩu đã được mã hóa (không lưu plain text)
    gender ENUM('male', 'female', 'unisex') DEFAULT 'unisex', -- Giới tính
    address TEXT,                        -- Địa chỉ giao hàng
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- 1. สร้างฐานข้อมูล
CREATE DATABASE BG1db;
USE BG1db;

-- 2. ตารางหมวดหมู่อาหาร (ตรงกับ: จัดการประเภทสินค้า)
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- 3. ตารางสมาชิก/ลูกค้า (ตรงกับ: ระบบสมาชิก, สมัคร, ล็อกอิน)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    role ENUM('admin', 'customer') DEFAULT 'customer', -- แยก Admin กับ ลูกค้า
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. ตารางเมนูอาหาร/สินค้า (ตรงกับ: จัดการสินค้า)
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_file VARCHAR(255), -- เก็บชื่อไฟล์รูปภาพ
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- 5. ตารางออเดอร์ (ตรงกับ: จัดการออเดอร์, ประวัติการสั่งซื้อ)
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'cooking', 'completed', 'cancelled') DEFAULT 'pending', -- สถานะออเดอร์
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 6. ตารางรายละเอียดออเดอร์ (เก็บว่าออเดอร์นี้สั่งอะไรบ้าง)
CREATE TABLE order_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- เพิ่มข้อมูลตัวอย่างหมวดหมู่ (เพื่อให้ Dropdown ในหน้าเพิ่มสินค้ามีข้อมูล)
INSERT INTO categories (category_name) VALUES ('อาหารจานหลัก'), ('ของทานเล่น'), ('เครื่องดื่ม'), ('ของหวาน');
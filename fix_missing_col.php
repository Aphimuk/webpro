<?php
require_once 'connect.php';

// เช็คก่อนว่ามีคอลัมน์นี้หรือยัง
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_visible'");

if ($check->num_rows == 0) {
    // ถ้ายังไม่มี -> สั่งสร้าง
    $sql = "ALTER TABLE users ADD COLUMN is_visible TINYINT(1) DEFAULT 1";
    if ($conn->query($sql) === TRUE) {
        echo "<h1 style='color:green'>✅ แก้ไขสำเร็จ! เพิ่มคอลัมน์ is_visible แล้ว</h1>";
    } else {
        echo "<h1 style='color:red'>❌ เกิดข้อผิดพลาด: " . $conn->error . "</h1>";
    }
} else {
    // ถ้ามีอยู่แล้ว
    echo "<h1 style='color:blue'>ℹ️ ฐานข้อมูลปกติ (มีคอลัมน์ is_visible อยู่แล้ว)</h1>";
}

echo "<br><a href='login.php' style='font-size:20px;'>กลับไปหน้าเข้าสู่ระบบ</a>";
?>
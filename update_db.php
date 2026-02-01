<?php
require_once 'connect.php';

// คำสั่งเพิ่มคอลัมน์ is_visible (ถ้ายังไม่มี)
// 1 = แสดงปกติ, 0 = ซ่อน
$sql = "ALTER TABLE users ADD COLUMN is_visible TINYINT(1) DEFAULT 1";

if ($conn->query($sql) === TRUE) {
    echo "<h1>✅ อัปเดตฐานข้อมูลสำเร็จ!</h1>";
    echo "<p>เพิ่มระบบ 'ซ่อนรายชื่อ' เรียบร้อยแล้ว</p>";
    echo "<a href='admin_panel.php'>กลับไปหน้า Admin</a>";
} else {
    echo "<h1>⚠️ แจ้งเตือน:</h1>";
    echo "<p>" . $conn->error . "</p>";
    echo "<p>(ถ้าขึ้นว่า Duplicate column แสดงว่าคุณเคยทำไปแล้ว ไม่ต้องตกใจครับ ใช้งานต่อได้เลย)</p>";
    echo "<a href='admin_panel.php'>กลับไปหน้า Admin</a>";
}
?>
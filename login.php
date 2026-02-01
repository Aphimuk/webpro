<?php
session_start();
require_once ('connect.php');

// 1. Logic การสมัครสมาชิก
if (isset($_POST['register'])) {
    // 1.1 ป้องกัน SQL Injection โดยการ Escape ตัวอักษรพิเศษ
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password']; 
    $name = $conn->real_escape_string($_POST['fullname']);
    $role = 'customer'; 

    // 1.2 ตรวจสอบว่า Username ซ้ำหรือไม่
    $check_sql = "SELECT username FROM users WHERE username = '$user'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Username นี้มีผู้ใช้งานแล้ว กรุณาใช้ชื่ออื่น');</script>";
    } else {
        // 1.3 เข้ารหัสรหัสผ่านก่อนบันทึก (Hash)
        $password_hashed = password_hash($pass, PASSWORD_DEFAULT);

        // บันทึกโดยใช้รหัสผ่านที่เข้ารหัสแล้ว ($password_hashed)
        $sql = "INSERT INTO users (username, password, fullname, role) VALUES ('$user', '$password_hashed', '$name', '$role')";
        
        if($conn->query($sql)){ 
            echo "<script>alert('สมัครสำเร็จ! กรุณาล็อกอิน');</script>"; 
        } else {
            echo "<script>alert('เกิดข้อผิดพลาด: " . $conn->error . "');</script>";
        }
    }
}

// 2. Logic การ Login
if (isset($_POST['login'])) {
    // 2.1 ป้องกัน SQL Injection
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    // 2.2 ดึงข้อมูล user มาก่อน (ยังไม่เช็ครหัสผ่านใน Query เพื่อความปลอดภัยสูงสุด)
    $sql = "SELECT * FROM users WHERE username = '$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 2.3 ตรวจสอบรหัสผ่านด้วย password_verify (เทียบรหัสที่กรอก กับ Hash ใน DB)
        if (password_verify($pass, $row['password'])) {
            
            // รหัสผ่านถูกต้อง -> สร้าง Session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];

            if($row['role'] == 'admin'){
                header("Location: admin_panel.php");
            } else {
                header("Location: index.php");
            }
            exit();

        } else {
            // รหัสผ่านไม่ถูกต้อง
            echo "<script>alert('ชื่อผู้ใช้หรือรหัสผ่านผิด');</script>";
        }
    } else {
        // ไม่พบชื่อผู้ใช้นี้
        echo "<script>alert('ชื่อผู้ใช้หรือรหัสผ่านผิด');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php // include 'navbar.php'; ?> 
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h4>เข้าสู่ระบบ (Login)</h4>
                    <form method="post">
                        <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                        <button type="submit" name="login" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h4>สมัครสมาชิก (Register)</h4>
                    <form method="post">
                        <input type="text" name="fullname" class="form-control mb-2" placeholder="ชื่อ-นามสกุล" required>
                        <input type="text" name="username" class="form-control mb-2" placeholder="Username ใหม่" required>
                        <input type="password" name="password" class="form-control mb-2" placeholder="Password ใหม่" required>
                        <button type="submit" name="register" class="btn btn-success w-100">สมัครสมาชิก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
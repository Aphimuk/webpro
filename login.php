<?php
session_start();
require_once ('connect.php');

// 1. Logic การสมัครสมาชิก
if (isset($_POST['register'])) {
    $user = $_POST['username'];
    $pass = $_POST['password']; // ควรเข้ารหัสด้วย password_hash() ในงานจริง
    $name = $_POST['fullname'];
    $role = 'customer'; // สมัครหน้าเว็บเป็นลูกค้าเสมอ

    $sql = "INSERT INTO users (username, password, fullname, role) VALUES ('$user', '$pass', '$name', '$role')";
    if($conn->query($sql)){ echo "<script>alert('สมัครสำเร็จ! กรุณาล็อกอิน');</script>"; }
}

// 2. Logic การ Login
if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
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
    <?php include 'navbar.php'; ?>
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
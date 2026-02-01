<?php
session_start();
require_once ('connect.php');

// กำหนดตัวแปรสำหรับเก็บค่าเดิม และ ข้อความแจ้งเตือน (เริ่มต้นให้เป็นค่าว่าง)
$old_fullname = "";
$old_username = "";
$register_error = ""; // เก็บข้อความ Error สมัครสมาชิก
$login_error = "";    // เก็บข้อความ Error ล็อกอิน

// 1. Logic การสมัครสมาชิก
if (isset($_POST['register'])) {
    // รับค่าและป้องกัน SQL Injection
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password']; 
    $name = $conn->real_escape_string($_POST['fullname']);
    $role = 'customer'; 

    // **เก็บค่าที่ user พิมพ์มาใส่ตัวแปรไว้ เพื่อส่งกลับไปแสดงที่หน้าฟอร์ม**
    $old_fullname = $name;
    $old_username = $user;

    // ตรวจสอบ Username ซ้ำ
    $check_sql = "SELECT username FROM users WHERE username = '$user'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // **ถ้าซ้ำ กำหนดข้อความ Error**
        $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว กรุณาเปลี่ยนชื่อใหม่";
    } else {
        // ถ้าไม่ซ้ำ -> เข้ารหัสและบันทึก
        $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, fullname, role) VALUES ('$user', '$password_hashed', '$name', '$role')";
        
        if($conn->query($sql)){ 
            echo "<script>alert('✅ สมัครสมาชิกสำเร็จ! กรุณาล็อกอิน');</script>";
            // สมัครผ่านแล้ว ล้างค่าเดิมทิ้ง
            $old_fullname = "";
            $old_username = "";
        } else {
            $register_error = "เกิดข้อผิดพลาดระบบ: " . $conn->error;
        }
    }
}

// 2. Logic การ Login
if (isset($_POST['login'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
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
            $login_error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $login_error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ / สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light"> 
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            
            <div class="col-md-5 mb-4">
                <div class="card p-4 shadow-sm h-100 border-0">
                    <h4 class="mb-3 text-primary"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</h4>
                    
                    <?php if($login_error != ""): ?>
                        <div class="alert alert-danger p-2 text-center" role="alert">
                            <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2">เข้าสู่ระบบ</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card p-4 shadow-sm h-100 border-0">
                    <h4 class="mb-3 text-success">สมัครสมาชิกใหม่</h4>
                    
                    <?php if($register_error != ""): ?>
                        <div class="alert alert-danger border-2 border-danger shadow-sm text-center fw-bold" role="alert">
                            <?php echo $register_error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" name="fullname" class="form-control" 
                                   value="<?php echo htmlspecialchars($old_fullname); ?>" 
                                   placeholder="เช่น สมชาย ใจดี" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username (สำหรับล็อกอิน)</label>
                            <input type="text" name="username" class="form-control <?php echo ($register_error != "") ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old_username); ?>" 
                                   placeholder="ภาษาอังกฤษเท่านั้น" required>
                            <?php if($register_error != ""): ?>
                                <div class="invalid-feedback">กรุณาเปลี่ยนชื่อผู้ใช้นี้</div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="ตั้งรหัสผ่าน" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-success w-100 py-2">สมัครสมาชิก</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
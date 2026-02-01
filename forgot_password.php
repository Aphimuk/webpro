<?php
session_start();
require_once ('connect.php');

$error_msg = "";
$success_msg = "";

if (isset($_POST['reset_password'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $new_pass = $_POST['new_password'];

    // 1. ตรวจสอบว่า Username และ เบอร์โทร ตรงกันไหม
    $sql_check = "SELECT * FROM users WHERE username = '$user' AND phone = '$phone'";
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        // 2. ถ้าข้อมูลถูกต้อง ให้เปลี่ยนรหัสผ่าน
        $new_pass_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        
        $sql_update = "UPDATE users SET password = '$new_pass_hashed' WHERE username = '$user'";
        
        if ($conn->query($sql_update)) {
            $success_msg = "✅ เปลี่ยนรหัสผ่านสำเร็จ! กรุณากลับไปเข้าสู่ระบบ";
        } else {
            $error_msg = "เกิดข้อผิดพลาด: " . $conn->error;
        }
    } else {
        $error_msg = "❌ ไม่พบข้อมูล! ชื่อผู้ใช้หรือเบอร์โทรศัพท์ไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน / เปลี่ยนรหัสผ่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Sarabun', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        .btn-purple {
            background-color: #512da8;
            color: white;
        }
        .btn-purple:hover {
            background-color: #311b92;
            color: white;
        }
    </style>
</head>
<body>

    <div class="card p-4">
        <h3 class="text-center mb-3 text-primary fw-bold">เปลี่ยนรหัสผ่านใหม่</h3>
        <p class="text-center text-muted small">กรอกชื่อผู้ใช้และเบอร์โทรศัพท์เพื่อยืนยันตัวตน</p>

        <?php if($error_msg != ""): ?>
            <div class="alert alert-danger text-center"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if($success_msg != ""): ?>
            <div class="alert alert-success text-center">
                <?php echo $success_msg; ?>
                <div class="mt-2">
                    <a href="index.php" class="btn btn-outline-success btn-sm">กลับหน้าเข้าสู่ระบบ</a>
                </div>
            </div>
        <?php else: ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                <input type="text" name="username" class="form-control" required placeholder="กรอก Username ของคุณ">
            </div>
            
            <div class="mb-3">
                <label class="form-label">เบอร์โทรศัพท์ที่ลงทะเบียน</label>
                <input type="text" name="phone" class="form-control" required placeholder="08xxxxxxxx">
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">ตั้งรหัสผ่านใหม่</label>
                <input type="password" name="new_password" class="form-control" required placeholder="รหัสผ่านใหม่ที่ต้องการ">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="reset_password" class="btn btn-purple">ยืนยันการเปลี่ยนรหัสผ่าน</button>
                <a href="index.php" class="btn btn-light text-muted">ยกเลิก / กลับไปหน้า Login</a>
            </div>
        </form>

        <?php endif; ?>
    </div>

</body>
</html>
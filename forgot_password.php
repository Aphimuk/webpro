<?php
session_start();
require_once ('connect.php');

$error_msg = "";
$success_msg = "";

if (isset($_POST['reset_password'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $new_pass = $_POST['new_password'];

    // เช็คข้อมูล
    $sql_check = "SELECT * FROM users WHERE username = '$user' AND phone = '$phone'";
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        $new_pass_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $sql_update = "UPDATE users SET password = '$new_pass_hashed' WHERE username = '$user'";
        
        if ($conn->query($sql_update)) {
            $success_msg = "✅ เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบใหม่";
        } else {
            $error_msg = "เกิดข้อผิดพลาด: " . $conn->error;
        }
    } else {
        $error_msg = "❌ ข้อมูลไม่ถูกต้อง! (ชื่อผู้ใช้หรือเบอร์โทรผิด)";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - บักปึก</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #FFFDE7; 
            font-family: 'Sarabun', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #3E2723;
        }
        .card {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
            border: 1px solid #FFE0B2;
        }
        h3 { color: #C62828; font-weight: 800; margin-bottom: 10px; }
        p { color: #5D4037; font-size: 14px; margin-bottom: 30px; }
        
        input {
            background-color: #FAFAFA;
            border: 1px solid #BDBDBD;
            padding: 12px 15px;
            margin: 10px 0;
            width: 100%;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            box-sizing: border-box;
        }
        input:focus { outline: 2px solid #EF6C00; }
        
        .btn-reset {
            background-color: #D84315; color: white; border: none;
            border-radius: 50px; padding: 12px; width: 100%;
            font-weight: bold; font-size: 16px; margin-top: 20px;
            cursor: pointer; transition: 0.2s;
        }
        .btn-reset:hover { background-color: #BF360C; }
        
        .alert-error { background: #FFEBEE; color: #D32F2F; padding: 10px; border-radius: 8px; font-weight: bold; margin-bottom: 20px; border: 1px solid #EF9A9A; }
        .alert-success { background: #E8F5E9; color: #1B5E20; padding: 10px; border-radius: 8px; font-weight: bold; margin-bottom: 20px; border: 1px solid #A5D6A7; }
        
        a { color: #555; text-decoration: none; font-size: 14px; display: block; margin-top: 15px; font-weight: bold;}
        a:hover { color: #D84315; }
    </style>
</head>
<body>

    <div class="card">
        <h3>🔐 เปลี่ยนรหัสผ่านใหม่</h3>
        <p>กรอกข้อมูลยืนยันตัวตนเพื่อตั้งรหัสใหม่</p>

        <?php if($error_msg != ""): ?>
            <div class="alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if($success_msg != ""): ?>
            <div class="alert-success"><?php echo $success_msg; ?></div>
            <a href="login.php" class="btn-reset" style="display:block; text-decoration:none; padding-top:12px;">กลับไปหน้าเข้าสู่ระบบ</a>
        <?php else: ?>

        <form method="post">
            <div style="text-align: left; font-weight: bold; font-size: 14px; margin-bottom: 5px;">ชื่อผู้ใช้ (Username)</div>
            <input type="text" name="username" required placeholder="กรอก Username ของคุณ">
            
            <div style="text-align: left; font-weight: bold; font-size: 14px; margin-bottom: 5px; margin-top:10px;">เบอร์โทรศัพท์</div>
            <input type="text" name="phone" required placeholder="เบอร์โทรที่ลงทะเบียนไว้">

            <div style="height: 1px; background: #EEE; margin: 20px 0;"></div>

            <div style="text-align: left; font-weight: bold; font-size: 14px; margin-bottom: 5px;">ตั้งรหัสผ่านใหม่</div>
            <input type="password" name="new_password" required placeholder="รหัสผ่านใหม่ที่ต้องการ">

            <button type="submit" name="reset_password" class="btn-reset">ยืนยันการเปลี่ยนรหัส</button>
            <a href="login.php">ยกเลิก / กลับไปหน้า Login</a>
        </form>

        <?php endif; ?>
    </div>

</body>
</html>
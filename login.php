<?php
session_start();
require_once ('connect.php');

// กำหนดตัวแปรสำหรับเก็บค่าเดิม (เพิ่มที่อยู่และเบอร์โทร)
$old_fullname = "";
$old_username = "";
$old_address = "";  // * ใหม่
$old_phone = "";    // * ใหม่

$register_error = ""; 
$register_success = ""; 
$login_error = "";    

// 1. Logic การสมัครสมาชิก
if (isset($_POST['register'])) {
    // รับค่าและป้องกัน SQL Injection
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password']; 
    $name = $conn->real_escape_string($_POST['fullname']);
    
    // * รับค่าใหม่ (ที่อยู่ & เบอร์โทร)
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    $role = 'customer'; 

    // **เก็บค่าเดิมไว้ส่งกลับหน้าฟอร์ม (รวมถึงฟิลด์ใหม่ด้วย)**
    $old_fullname = $name;
    $old_username = $user;
    $old_address = $address;
    $old_phone = $phone;

    // ตรวจสอบ Username ซ้ำ
    $check_sql = "SELECT username FROM users WHERE username = '$user'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว! กรุณาเปลี่ยนชื่อใหม่";
    } else {
        // เข้ารหัสรหัสผ่าน
        $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
        
        // * อัปเดตคำสั่ง INSERT ให้บันทึกที่อยู่และเบอร์โทร
        $sql = "INSERT INTO users (username, password, fullname, address, phone, role) 
                VALUES ('$user', '$password_hashed', '$name', '$address', '$phone', '$role')";
        
        if($conn->query($sql)){ 
            $register_success = "✅ สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
            
            // สมัครผ่านแล้ว ล้างค่าเดิมทิ้ง
            $old_fullname = "";
            $old_address = "";
            $old_phone = "";
            // old_username เก็บไว้เติมช่อง login เหมือนเดิม
        } else {
            $register_error = "เกิดข้อผิดพลาดระบบ: " . $conn->error;
        }
    }
}

// 2. Logic การ Login (เหมือนเดิม)
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
            $login_error = "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $login_error = "❌ ไม่พบชื่อผู้ใช้นี้ในระบบ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ / สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light"> 
    
    <div class="container mt-5">
        
        <?php if($register_success != ""): ?>
            <div class="alert alert-success text-center fs-5 fw-bold shadow-sm" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $register_success; ?>
            </div>
        <?php endif; ?>

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
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo ($register_success != "") ? htmlspecialchars($old_username) : ''; ?>" 
                                   required>
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
                    <h4 class="mb-3 text-success"><i class="fas fa-user-plus"></i> สมัครสมาชิกใหม่</h4>
                    
                    <?php if($register_error != ""): ?>
                        <div class="alert alert-danger border-2 border-danger shadow-sm text-center fw-bold" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $register_error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" 
                                   value="<?php echo htmlspecialchars($old_fullname); ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เบอร์โทรศัพท์ <span class="text-muted small">(ไม่บังคับ)</span></label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($old_phone); ?>"
                                   placeholder="08xxxxxxxx"> 
                            </div>

                        <div class="mb-3">
                            <label class="form-label">ที่อยู่จัดส่ง <span class="text-muted small">(ไม่บังคับ)</span></label>
                            <textarea name="address" class="form-control" rows="2" placeholder="บ้านเลขที่, ถนน, ตำบล..."><?php echo htmlspecialchars($old_address); ?></textarea>
                            </div>

                        <hr> <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control <?php echo ($register_error != "") ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo ($register_success == "") ? htmlspecialchars($old_username) : ''; ?>" 
                                   placeholder="ภาษาอังกฤษเท่านั้น" required>
                            
                            <?php if($register_error != ""): ?>
                                <div class="invalid-feedback fw-bold">กรุณาเปลี่ยนชื่อผู้ใช้นี้</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
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
<?php
session_start();
require_once ('connect.php');

// --- (ส่วน Logic PHP เหมือนเดิมทุกประการ ไม่ต้องแก้) ---
$old_fullname = "";
$old_username = "";
$old_address = "";  
$old_phone = "";    
$register_error = ""; 
$register_success = ""; 
$login_error = "";    

if (isset($_POST['register'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password']; 
    $name = $conn->real_escape_string($_POST['fullname']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = 'customer'; 

    $old_fullname = $name;
    $old_username = $user;
    $old_address = $address;
    $old_phone = $phone;

    $check_sql = "SELECT username FROM users WHERE username = '$user'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว! กรุณาเปลี่ยนชื่อใหม่";
    } else {
        $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, fullname, address, phone, role) 
                VALUES ('$user', '$password_hashed', '$name', '$address', '$phone', '$role')";
        
        if($conn->query($sql)){ 
            $register_success = "✅ สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
            $old_fullname = "";
            $old_address = "";
            $old_phone = "";
        } else {
            $register_error = "เกิดข้อผิดพลาดระบบ: " . $conn->error;
        }
    }
}

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
    
    <style>
        /* CSS เดิมของคุณ */
        .form-box {
            position: absolute;
            right: 0;
            top: 0; /* เพิ่ม top: 0 ให้ชิดขอบบน */
            width: 50%;     
            height: 100%;
            background: #fff;
            display: flex;
            align-items: center; /* จัดกึ่งกลางแนวตั้ง */
            justify-content: center; /* เพิ่มจัดกึ่งกลางแนวนอน */
            color: #333;        
            text-align: left; /* เปลี่ยนเป็น left ให้อ่านง่าย */
            padding: 40px;
            overflow-y: auto; /* สำคัญ! ถ้าฟอร์มยาวเกินจอ ให้เลื่อนได้ */
        }

        /* เพิ่ม CSS ตกแต่งพื้นหลังด้านซ้าย */
        body {
            background-color: #2c3e50; /* สีพื้นหลังฝั่งซ้าย (เปลี่ยนเป็นรูปได้) */
            background-image: url('https://source.unsplash.com/random/1920x1080/?food'); /* ตัวอย่างรูปพื้นหลัง */
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
            overflow: hidden; /* ซ่อน Scrollbar ของ Body หลัก */
        }

        /* ปรับแต่งบนมือถือ (จอเล็กกว่า 768px) */
        @media (max-width: 768px) {
            .form-box {
                width: 100%; /* เต็มจอ */
                position: relative; /* ไม่ต้องลอย */
                height: auto;
            }
            body {
                overflow: auto; /* ให้เลื่อนได้ปกติ */
            }
        }
    </style>
</head>
<body> 
    
    <div class="form-box shadow-lg">
        <div class="container-fluid"> <?php if($register_success != ""): ?>
                <div class="alert alert-success text-center fs-5 fw-bold shadow-sm mb-4" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $register_success; ?>
                </div>
            <?php endif; ?>

            <div class="row g-4"> <div class="col-12">
                    <div class="card p-4 shadow-sm border-0 bg-light">
                        <h4 class="mb-3 text-primary"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</h4>
                        
                        <?php if($login_error != ""): ?>
                            <div class="alert alert-danger p-2 text-center" role="alert">
                                <?php echo $login_error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="row">
                                <div class="col-md-5 mb-2">
                                    <input type="text" name="username" class="form-control" placeholder="Username"
                                           value="<?php echo ($register_success != "") ? htmlspecialchars($old_username) : ''; ?>" required>
                                </div>
                                <div class="col-md-5 mb-2">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-12 text-center text-muted">
                    <hr>
                    <span>หรือ สมัครสมาชิกใหม่ด้านล่าง</span>
                </div>

                <div class="col-12">
                    <div class="card p-4 shadow-sm border-0">
                        <h4 class="mb-3 text-success"><i class="fas fa-user-plus"></i> สมัครสมาชิก</h4>
                        
                        <?php if($register_error != ""): ?>
                            <div class="alert alert-danger border-2 border-danger shadow-sm text-center fw-bold" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $register_error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" name="fullname" class="form-control" 
                                           value="<?php echo htmlspecialchars($old_fullname); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">เบอร์โทรศัพท์</label>
                                    <input type="text" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($old_phone); ?>" placeholder="08xxxxxxxx">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">ที่อยู่จัดส่ง</label>
                                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($old_address); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control <?php echo ($register_error != "") ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo ($register_success == "") ? htmlspecialchars($old_username) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>

                            <button type="submit" name="register" class="btn btn-success w-100 py-2">สมัครสมาชิก</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
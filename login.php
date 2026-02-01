<?php
session_start();
require_once ('connect.php');

// --- PHP Logic (ส่วนจัดการข้อมูล) ---

// ตัวแปรเก็บค่าเดิม
$old_fullname = "";
$old_username = "";
$old_address = "";  
$old_phone = "";    

// ตัวแปรเก็บสถานะแจ้งเตือน
$register_error = ""; 
$register_success = ""; 
$login_error = "";    

// ตัวแปรเช็คว่าควรเปิดหน้าไหน (false = หน้า Login, true = หน้า Register)
$show_register_page = false;

// 1. Logic การสมัครสมาชิก
if (isset($_POST['register'])) {
    $show_register_page = true; // ถ้ากดสมัคร ให้หน้าเว็บเปิดค้างที่หน้าสมัคร (แม้จะ error)

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
            // สมัครผ่าน ให้สลับกลับไปหน้า Login เพื่อให้ User กรอกล็อกอิน
            $show_register_page = false; 
            
            // ล้างค่าเดิม
            $old_fullname = "";
            $old_address = "";
            $old_phone = "";
        } else {
            $register_error = "เกิดข้อผิดพลาดระบบ: " . $conn->error;
        }
    }
}

// 2. Logic การ Login
if (isset($_POST['login'])) {
    $show_register_page = false; // กดล็อกอิน ให้หน้าเว็บอยู่ที่หน้าล็อกอิน

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ / สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
            font-family: 'Sarabun', sans-serif; /* ถ้ามีฟอนต์ไทย */
        }

        /* ส่วนซ้าย: รูปภาพพื้นหลัง */
        .left-side {
            position: absolute;
            left: 0;
            top: 0;
            width: 50%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }

        /* ส่วนขวา: พื้นที่ฟอร์ม */
        .right-side {
            position: absolute;
            right: 0;
            top: 0;
            width: 50%;
            height: 100%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto; /* เลื่อนลงได้ถ้าฟอร์มยาว */
        }

        .form-container {
            width: 80%; /* ความกว้างของฟอร์มภายในกล่องขวา */
            max-width: 500px;
            padding: 20px;
        }

        /* Animation การสลับหน้า */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive: มือถือให้เรียงบนล่าง */
        @media (max-width: 768px) {
            .left-side { display: none; } /* ซ่อนรูปภาพ */
            .right-side { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="left-side"></div>

    <div class="right-side">
        <div class="form-container">

            <?php if($register_success != ""): ?>
                <div class="alert alert-success text-center fs-5 fw-bold shadow-sm mb-4">
                    <i class="fas fa-check-circle"></i> <?php echo $register_success; ?>
                </div>
            <?php endif; ?>

            <div id="loginSection" class="fade-in" style="display: <?php echo $show_register_page ? 'none' : 'block'; ?>;">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">ยินดีต้อนรับกลับ!</h2>
                    <p class="text-muted">กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ</p>
                </div>

                <?php if($login_error != ""): ?>
                    <div class="alert alert-danger text-center"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control form-control-lg" 
                               value="<?php echo ($register_success != "") ? htmlspecialchars($old_username) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary w-100 btn-lg mb-3">เข้าสู่ระบบ</button>
                    
                    <div class="text-center">
                        <span class="text-muted">ยังไม่มีบัญชีใช่ไหม?</span>
                        <a href="javascript:void(0)" onclick="toggleForms()" class="fw-bold text-decoration-none text-success">สมัครสมาชิกที่นี่</a>
                    </div>
                </form>
            </div>

            <div id="registerSection" class="fade-in" style="display: <?php echo $show_register_page ? 'block' : 'none'; ?>;">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-success">สมัครสมาชิกใหม่</h2>
                    <p class="text-muted">กรอกข้อมูลเพื่อเริ่มต้นใช้งาน</p>
                </div>

                <?php if($register_error != ""): ?>
                    <div class="alert alert-danger text-center fw-bold"><?php echo $register_error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($old_fullname); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control <?php echo ($register_error != "") ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old_username); ?>" required>
                            <?php if($register_error != ""): ?><div class="invalid-feedback">เปลี่ยนชื่อนี้</div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เบอร์โทรศัพท์ <small class="text-muted">(ไม่บังคับ)</small></label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($old_phone); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ที่อยู่ <small class="text-muted">(ไม่บังคับ)</small></label>
                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($old_address); ?></textarea>
                    </div>

                    <button type="submit" name="register" class="btn btn-success w-100 btn-lg mb-3">ยืนยันการสมัคร</button>
                    
                    <div class="text-center">
                        <span class="text-muted">มีบัญชีอยู่แล้ว?</span>
                        <a href="javascript:void(0)" onclick="toggleForms()" class="fw-bold text-decoration-none text-primary">เข้าสู่ระบบที่นี่</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function toggleForms() {
            var loginSec = document.getElementById("loginSection");
            var regSec = document.getElementById("registerSection");

            if (loginSec.style.display === "none") {
                // ถ้า Login ปิดอยู่ -> ให้เปิด Login, ปิด Register
                loginSec.style.display = "block";
                regSec.style.display = "none";
            } else {
                // ถ้า Login เปิดอยู่ -> ให้ปิด Login, เปิด Register
                loginSec.style.display = "none";
                regSec.style.display = "block";
            }
        }
    </script>
</body>
</html>
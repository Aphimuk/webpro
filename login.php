<?php
session_start();
require_once ('connect.php');

// --- 1. PHP Logic ---
$old_fullname = "";
$old_username = "";
$old_address = "";  
$old_phone = "";    

$register_error = ""; 
$register_success = ""; 
$login_error = "";    

$is_register_active = false;

// 1.1 Logic สมัครสมาชิก
if (isset($_POST['register'])) {
    $is_register_active = true;

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

    // ตรวจสอบข้อมูลฝั่ง Server (เผื่อ JS ไม่ทำงาน)
    if(empty($user) || empty($pass) || empty($name) || empty($phone)){ // * เพิ่ม check phone
        $register_error = "⚠️ กรุณากรอกข้อมูลให้ครบทุกช่อง (รวมถึงเบอร์โทรศัพท์)";
    } else {
        // ตรวจสอบ Username ซ้ำ
        $check_sql = "SELECT username FROM users WHERE username = '$user'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว!";
        } else {
            $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, fullname, address, phone, role) 
                    VALUES ('$user', '$password_hashed', '$name', '$address', '$phone', '$role')";
            
            if($conn->query($sql)){ 
                $register_success = "✅ สมัครสำเร็จ! กรุณาเข้าสู่ระบบ";
                $is_register_active = false; 
                
                $old_fullname = "";
                $old_address = "";
                $old_phone = "";
            } else {
                $register_error = "Error: " . $conn->error;
            }
        }
    }
}

// 1.2 Logic การ Login
if (isset($_POST['login'])) {
    $is_register_active = false;

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
        $login_error = "❌ ไม่พบชื่อผู้ใช้นี้";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ / สมัครสมาชิก</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; }
        body {
            background: #c9d6ff;
            background: linear-gradient(to right, #e2e2e2, #c9d6ff);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Sarabun', sans-serif;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            min-height: 550px;
        }
        .container p { font-size: 16px; line-height: 24px; margin: 20px 0; }
        .container span { font-size: 14px; margin-bottom: 10px; display: block;}
        .container a { color: #333; font-size: 14px; text-decoration: none; margin: 15px 0 10px; font-weight: bold; }
        .container a:hover { text-decoration: underline; color: #512da8; }
        .container button {
            background-color: #512da8; color: #fff; font-size: 14px; padding: 10px 45px;
            border: 1px solid transparent; border-radius: 8px; font-weight: 600; text-transform: uppercase;
            margin-top: 10px; cursor: pointer; transition: 0.3s; font-family: 'Sarabun', sans-serif;
        }
        .container button:hover { background-color: #311b92; }
        .container button.ghost { background-color: transparent; border-color: #fff; }
        .container form {
            background-color: #fff; display: flex; align-items: center; justify-content: center;
            flex-direction: column; padding: 0 40px; height: 100%; text-align: center;
        }
        .container input {
            background-color: #eee; border: none; margin: 8px 0; padding: 12px 15px;
            font-size: 14px; border-radius: 8px; width: 100%; outline: none; font-family: 'Sarabun', sans-serif;
        }
        
        /* Highlight ช่องที่ยังไม่ได้กรอก */
        .input-error { border: 1px solid #e74c3c !important; background-color: #fadbd8 !important; }

        .form-container { position: absolute; top: 0; height: 100%; transition: all 0.6s ease-in-out; }
        .sign-in-container { left: 0; width: 50%; z-index: 2; }
        .container.right-panel-active .sign-in-container { transform: translateX(100%); }
        .sign-up-container { left: 0; width: 50%; opacity: 0; z-index: 1; }
        .container.right-panel-active .sign-up-container { transform: translateX(100%); opacity: 1; z-index: 5; animation: show 0.6s; }
        @keyframes show { 0%, 49.99% { opacity: 0; z-index: 1; } 50%, 100% { opacity: 1; z-index: 5; } }
        .overlay-container { position: absolute; top: 0; left: 50%; width: 50%; height: 100%; overflow: hidden; transition: transform 0.6s ease-in-out; z-index: 100; }
        .container.right-panel-active .overlay-container { transform: translateX(-100%); }
        .overlay {
            background: #512da8; background: linear-gradient(to right, #5c6bc0, #512da8);
            background-repeat: no-repeat; background-size: cover; background-position: 0 0;
            color: #ffffff; position: relative; left: -100%; height: 100%; width: 200%;
            transform: translateX(0); transition: transform 0.6s ease-in-out;
        }
        .container.right-panel-active .overlay { transform: translateX(50%); }
        .overlay-panel { position: absolute; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0 40px; text-align: center; top: 0; height: 100%; width: 50%; transform: translateX(0); transition: transform 0.6s ease-in-out; }
        .overlay-left { transform: translateX(-20%); }
        .container.right-panel-active .overlay-left { transform: translateX(0); }
        .overlay-right { right: 0; transform: translateX(0); }
        .container.right-panel-active .overlay-right { transform: translateX(20%); }
        
        .alert-text { color: #e74c3c; font-weight: bold; font-size: 14px; margin-bottom: 10px; display: block; }
        .success-text { color: #2ecc71; font-weight: bold; font-size: 14px; margin-bottom: 10px; }
        
        @media (max-width: 768px) {
            .container { width: 100%; min-height: 800px; border-radius: 0; }
            .form-container { width: 100%; }
            .sign-in-container { top: 0; height: 50%; }
            .sign-up-container { bottom: 0; top: auto; height: 50%; opacity: 1; z-index: 1; transform: none !important;}
            .overlay-container { display: none; }
            .sign-in-container, .sign-up-container { position: relative; width: 100%; height: auto; padding: 20px 0; }
        }
    </style>
</head>
<body>

    <div class="container <?php echo $is_register_active ? 'right-panel-active' : ''; ?>" id="container">
        
        <div class="form-container sign-up-container">
            <form method="post" id="registerForm" novalidate>
                <h1>สมัครสมาชิก</h1>
                <span class="mb-2">กรอกข้อมูลของคุณเพื่อใช้งานระบบ</span>
                
                <?php if($register_error != ""): ?>
                    <div class="alert-text"><?php echo $register_error; ?></div>
                <?php endif; ?>
                
                <div id="js-error" class="alert-text" style="display:none;"></div>

                <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" value="<?php echo htmlspecialchars($old_fullname); ?>" data-label="ชื่อ-นามสกุล" required />
                
                <input type="text" name="username" placeholder="ชื่อผู้ใช้ (Username)" value="<?php echo htmlspecialchars($old_username); ?>" data-label="ชื่อผู้ใช้" required />
                
                <input type="password" name="password" placeholder="รหัสผ่าน" data-label="รหัสผ่าน" required />
                
                <input type="text" name="phone" placeholder="เบอร์โทรศัพท์" value="<?php echo htmlspecialchars($old_phone); ?>" data-label="เบอร์โทรศัพท์" required />
                
                <input type="text" name="address" placeholder="ที่อยู่ (ถ้ามี)" value="<?php echo htmlspecialchars($old_address); ?>" />

                <button type="submit" name="register">ยืนยันการสมัคร</button>
            </form>
        </div>

        <div class="form-container sign-in-container">
            <form method="post" novalidate>
                <h1>เข้าสู่ระบบ</h1>
                <span class="mb-2">ยินดีต้อนรับกลับมาอีกครั้ง</span>
                
                <?php if($register_success != ""): ?>
                    <div class="success-text"><?php echo $register_success; ?></div>
                <?php endif; ?>
                <?php if($login_error != ""): ?>
                    <div class="alert-text"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <input type="text" name="username" placeholder="ชื่อผู้ใช้ (Username)" value="<?php echo ($register_success != "") ? htmlspecialchars($old_username) : ''; ?>" required />
                <input type="password" name="password" placeholder="รหัสผ่าน" required />
                
                <a href="forgot_password.php">ลืมรหัสผ่านใช่ไหม?</a>
                
                <button type="submit" name="login">เข้าสู่ระบบ</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                
                <div class="overlay-panel overlay-left">
                    <h1>สวัดดีสมาชิกใหม่!</h1>
                    <p>สมัครสมาชิก<br>แล้วมากินของอร่อยกันเถอะ</p>
                    <button class="ghost" id="signIn">ไปที่หน้าเข้าสู่ระบบ</button>
                </div>
                
                <div class="overlay-panel overlay-right">
                    <h1>ยินดีต้อนรับสู่ร้านบักปึก!</h1>
                    <p>เข้าสู่ระบบ<br>แล้วมาอร่อยไปด้วยกัน</p>
                    <button class="ghost" id="signUp">ไปที่หน้าสมัครสมาชิก</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');
        const registerForm = document.getElementById('registerForm');
        const jsErrorDiv = document.getElementById('js-error');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        // --- เพิ่ม Script ตรวจสอบช่องว่างและแจ้งเตือนด้านบน ---
        registerForm.addEventListener('submit', function(e) {
            let errors = [];
            let inputs = registerForm.querySelectorAll('input[required]');
            
            // ล้างสีแดงเก่าออกก่อน
            inputs.forEach(input => input.classList.remove('input-error'));
            jsErrorDiv.style.display = 'none';
            jsErrorDiv.innerHTML = '';

            inputs.forEach(function(input) {
                if (!input.value.trim()) {
                    // ถ้าช่องไหนว่าง ให้เก็บชื่อช่องไว้
                    errors.push(input.getAttribute('data-label'));
                    // ไฮไลท์ช่องนั้นเป็นสีแดง
                    input.classList.add('input-error');
                }
            });

            if (errors.length > 0) {
                // ถ้ามี error ห้ามส่งฟอร์ม (ป้องกันรีเฟรช)
                e.preventDefault();
                // แสดงข้อความด้านบน
                jsErrorDiv.style.display = 'block';
                jsErrorDiv.innerHTML = '⚠️ กรุณากรอกข้อมูล: ' + errors.join(', ');
            }
        });
    </script>

</body>
</html>
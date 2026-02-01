<?php
session_start();
require_once ('connect.php');

$old_fullname = "";
$old_username = "";
$old_address = "";  
$old_phone = "";    

$register_error = ""; 
$register_success = ""; 
$login_error = "";    

$is_register_active = false;

// Logic สมัครสมาชิก
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

    if(empty($user) || empty($pass) || empty($name) || empty($phone)){
        $register_error = "⚠️ กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        $check_sql = "SELECT username FROM users WHERE username = '$user'";
        if ($conn->query($check_sql)->num_rows > 0) {
            $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว!";
        } else {
            $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, fullname, address, phone, role) 
                    VALUES ('$user', '$password_hashed', '$name', '$address', '$phone', '$role')";
            
            if($conn->query($sql)){ 
                $register_success = "✅ สมัครสมาชิกเรียบร้อย! ยินดีต้อนรับสู่ครอบครัวไก่ทอด";
                $is_register_active = false; 
                $old_fullname = ""; $old_address = ""; $old_phone = "";
            } else {
                $register_error = "Error: " . $conn->error;
            }
        }
    }
}

// Logic Login
if (isset($_POST['login'])) {
    $is_register_active = false;
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$user'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];

            // ตั้งค่าแจ้งเตือนต้อนรับ
            $_SESSION['alert_msg'] = "ยินดีต้อนรับคุณ {$row['fullname']} สู่ร้านไก่ทอดบักปึก!";
            $_SESSION['alert_type'] = "success";

            header("Location: " . ($row['role'] == 'admin' ? "admin_panel.php" : "index.php"));
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
    <title>เข้าสู่ระบบ - ร้านไก่ทอดบักปึก</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            /* ธีมไก่ทอด: พื้นหลังสีครีมอุ่นๆ */
            background: #FFF3E0;
            font-family: 'Sarabun', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
        }
        h1 { font-weight: bold; margin: 0; color: #E65100; }
        p { font-size: 14px; font-weight: 100; line-height: 20px; letter-spacing: 0.5px; margin: 20px 0 30px; }
        span { font-size: 12px; }
        a { color: #333; font-size: 14px; text-decoration: none; margin: 15px 0; }
        button {
            border-radius: 20px;
            border: 1px solid #FF6D00;
            background-color: #FF6D00; /* สีส้มไก่ทอด */
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
        }
        button:active { transform: scale(0.95); }
        button:focus { outline: none; }
        button.ghost { background-color: transparent; border-color: #FFFFFF; }
        
        form {
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }
        input {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 5px;
            font-family: 'Sarabun', sans-serif;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 550px;
        }
        .form-container { position: absolute; top: 0; height: 100%; transition: all 0.6s ease-in-out; }
        .sign-in-container { left: 0; width: 50%; z-index: 2; }
        .container.right-panel-active .sign-in-container { transform: translateX(100%); }
        .sign-up-container { left: 0; width: 50%; opacity: 0; z-index: 1; }
        .container.right-panel-active .sign-up-container { transform: translateX(100%); opacity: 1; z-index: 5; animation: show 0.6s; }
        
        @keyframes show { 0%, 49.99% { opacity: 0; z-index: 1; } 50%, 100% { opacity: 1; z-index: 5; } }
        
        .overlay-container {
            position: absolute; top: 0; left: 50%; width: 50%; height: 100%; overflow: hidden;
            transition: transform 0.6s ease-in-out; z-index: 100;
        }
        .container.right-panel-active .overlay-container { transform: translateX(-100%); }
        
        .overlay {
            /* Gradient สีส้มแดง */
            background: #FF416C;
            background: -webkit-linear-gradient(to right, #FF4B2B, #FF416C);
            background: linear-gradient(to right, #FF4B2B, #FF416C);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #FFFFFF;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }
        .container.right-panel-active .overlay { transform: translateX(50%); }
        .overlay-panel {
            position: absolute; display: flex; align-items: center; justify-content: center;
            flex-direction: column; padding: 0 40px; text-align: center; top: 0; height: 100%; width: 50%;
            transform: translateX(0); transition: transform 0.6s ease-in-out;
        }
        .overlay-left { transform: translateX(-20%); }
        .container.right-panel-active .overlay-left { transform: translateX(0); }
        .overlay-right { right: 0; transform: translateX(0); }
        .container.right-panel-active .overlay-right { transform: translateX(20%); }

        .alert-text { color: #D32F2F; font-weight: bold; font-size: 14px; margin-bottom: 10px; }
        .success-text { color: #388E3C; font-weight: bold; font-size: 14px; margin-bottom: 10px; }
        .input-error { border: 1px solid #D32F2F !important; background-color: #FFEBEE !important; }
    </style>
</head>
<body>

<div class="container <?php echo $is_register_active ? 'right-panel-active' : ''; ?>" id="container">
    <div class="form-container sign-up-container">
        <form method="post" id="registerForm">
            <h1>สมัครสมาชิกใหม่</h1>
            <span>กรอกข้อมูลเพื่อสั่งไก่ทอดแสนอร่อย</span>
            
            <?php if($register_error != ""): ?>
                <div class="alert-text"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <div id="js-error" class="alert-text" style="display:none;"></div>

            <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" value="<?php echo htmlspecialchars($old_fullname); ?>" required />
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($old_username); ?>" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="text" name="phone" placeholder="เบอร์โทรศัพท์" value="<?php echo htmlspecialchars($old_phone); ?>" required />
            <input type="text" name="address" placeholder="ที่อยู่จัดส่ง" value="<?php echo htmlspecialchars($old_address); ?>" />
            
            <button type="submit" name="register">สมัครสมาชิก</button>
        </form>
    </div>

    <div class="form-container sign-in-container">
        <form method="post">
            <h1>เข้าสู่ระบบ</h1>
            <span>ยินดีต้อนรับสู่ร้านบักปึก</span>
            
            <?php if($register_success != ""): ?>
                <div class="success-text"><?php echo $register_success; ?></div>
            <?php endif; ?>
            <?php if($login_error != ""): ?>
                <div class="alert-text"><?php echo $login_error; ?></div>
            <?php endif; ?>

            <input type="text" name="username" placeholder="Username" value="<?php echo ($register_success != "") ? htmlspecialchars($old_username) : ''; ?>" required />
            <input type="password" name="password" placeholder="Password" required />
            <a href="forgot_password.php">ลืมรหัสผ่าน?</a>
            <button type="submit" name="login">เข้าสู่ระบบ</button>
        </form>
    </div>

    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>มีบัญชีอยู่แล้ว?</h1>
                <p>เข้าสู่ระบบเพื่อสั่งไก่ทอดร้อนๆ ได้เลย</p>
                <button class="ghost" id="signIn">ไปหน้าเข้าสู่ระบบ</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>ยังไม่เป็นสมาชิก?</h1>
                <p>สมัครสมาชิกวันนี้ สั่งง่าย ส่งไว อร่อยชัวร์!</p>
                <button class="ghost" id="signUp">สมัครสมาชิก</button>
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

    signUpButton.addEventListener('click', () => container.classList.add("right-panel-active"));
    signInButton.addEventListener('click', () => container.classList.remove("right-panel-active"));

    registerForm.addEventListener('submit', function(e) {
        let errors = [];
        let inputs = registerForm.querySelectorAll('input[required]');
        inputs.forEach(input => input.classList.remove('input-error'));
        jsErrorDiv.style.display = 'none';

        inputs.forEach(function(input) {
            if (!input.value.trim()) {
                input.classList.add('input-error');
                errors.push('กรุณากรอกข้อมูลให้ครบ');
            }
        });

        if (errors.length > 0) {
            e.preventDefault();
            jsErrorDiv.style.display = 'block';
            jsErrorDiv.innerHTML = errors[0];
        }
    });
</script>
</body>
</html>
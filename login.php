<?php
session_start();
require_once ('connect.php');

// --- 1. PHP Logic (ส่วนจัดการข้อมูลเดิม) ---
$old_fullname = "";
$old_username = "";
$old_address = "";  
$old_phone = "";    

$register_error = ""; 
$register_success = ""; 
$login_error = "";    

// ตัวแปรเช็คว่าควรเปิดหน้า Register ค้างไว้ไหม (ถ้า error หรือเพิ่งกดสมัคร)
$is_register_active = false;

// 1.1 Logic สมัครสมาชิก
if (isset($_POST['register'])) {
    $is_register_active = true; // สั่งให้ CSS เปิดหน้า Register ค้างไว้

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
        $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว!";
    } else {
        $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, fullname, address, phone, role) 
                VALUES ('$user', '$password_hashed', '$name', '$address', '$phone', '$role')";
        
        if($conn->query($sql)){ 
            $register_success = "✅ สมัครสำเร็จ! กรุณาล็อกอิน";
            $is_register_active = false; // สมัครผ่าน สลับกลับไปหน้า Login
            
            // ล้างค่าเดิม
            $old_fullname = "";
            $old_address = "";
            $old_phone = "";
            // old_username เก็บไว้เติมช่อง login
        } else {
            $register_error = "Error: " . $conn->error;
        }
    }
}

// 1.2 Logic การ Login
if (isset($_POST['login'])) {
    $is_register_active = false; // สั่งให้ CSS อยู่หน้า Login

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
    <title>Welcome to Our Site</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- CSS Design (Sliding Effect) --- */
        * {
            box-sizing: border-box;
        }

        body {
            background: #c9d6ff;
            background: linear-gradient(to right, #e2e2e2, #c9d6ff);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 900px; /* ความกว้างของกล่องหลัก */
            max-width: 100%;
            min-height: 550px; /* ความสูง */
        }

        .container p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 20px 0;
        }

        .container span {
            font-size: 12px;
        }

        .container a {
            color: #333;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
        }

        .container button {
            background-color: #512da8; /* สีปุ่มหลัก */
            color: #fff;
            font-size: 12px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .container button:hover {
            background-color: #311b92;
        }

        .container button.hidden {
            background-color: transparent;
            border-color: #fff;
        }

        .container form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        .container input {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        /* --- LOGIC การสลับหน้า --- */
        
        /* หน้า Login (Sign In) */
        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        /* เมื่อกดสลับ หน้า Login จะเลื่อนออก */
        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        /* หน้า Register (Sign Up) */
        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        /* เมื่อกดสลับ หน้า Register จะเลื่อนเข้ามาและแสดงผล */
        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {
            0%, 49.99% { opacity: 0; z-index: 1; }
            50%, 100% { opacity: 1; z-index: 5; }
        }

        /* --- OVERLAY (แผ่นสีฟ้าที่เลื่อนไปมา) --- */
        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: #512da8; /* สีพื้นหลังแผ่นสไลด์ */
            background: -webkit-linear-gradient(to right, #5c6bc0, #512da8);
            background: linear-gradient(to right, #5c6bc0, #512da8);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #ffffff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        /* Social Icons */
        .social-container {
            margin: 20px 0;
        }
        .social-container a {
            border: 1px solid #ddd;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 5px;
            height: 40px;
            width: 40px;
            color: #333;
        }

        /* Error Messages */
        .alert-text {
            color: #e74c3c;
            font-size: 12px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .success-text {
            color: #2ecc71;
            font-size: 12px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container { width: 100%; min-height: 800px; border-radius: 0; }
            .form-container { width: 100%; }
            .sign-in-container { top: 0; height: 50%; }
            .sign-up-container { bottom: 0; top: auto; height: 50%; opacity: 1; z-index: 1; transform: none !important;}
            .overlay-container { display: none; } /* ซ่อน Animation บนมือถือเพราะพื้นที่มีน้อย */
            
            /* ปรับ CSS แบบง่ายสำหรับมือถือคือโชว์ทั้งคู่ */
            .sign-in-container, .sign-up-container {
                position: relative; width: 100%; height: auto; padding: 20px 0;
            }
        }
    </style>
</head>
<body>

    <div class="container <?php echo $is_register_active ? 'right-panel-active' : ''; ?>" id="container">
        
        <div class="form-container sign-up-container">
            <form method="post">
                <h1>Create Account</h1>
                
                <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>or use your email for registration</span>
                
                <?php if($register_error != ""): ?>
                    <div class="alert-text"><?php echo $register_error; ?></div>
                <?php endif; ?>

                <input type="text" name="fullname" placeholder="Name" value="<?php echo htmlspecialchars($old_fullname); ?>" required />
                <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($old_username); ?>" required />
                <input type="password" name="password" placeholder="Password" required />
                
                <input type="text" name="phone" placeholder="Phone (Optional)" value="<?php echo htmlspecialchars($old_phone); ?>" />
                <input type="text" name="address" placeholder="Address (Optional)" value="<?php echo htmlspecialchars($old_address); ?>" />

                <button type="submit" name="register">Sign Up</button>
            </form>
        </div>

        <div class="form-container sign-in-container">
            <form method="post">
                <h1>Sign in</h1>
                
                <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>or use your account</span>
                
                <?php if($register_success != ""): ?>
                    <div class="success-text"><?php echo $register_success; ?></div>
                <?php endif; ?>
                <?php if($login_error != ""): ?>
                    <div class="alert-text"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <input type="text" name="username" placeholder="Username" value="<?php echo ($register_success != "") ? htmlspecialchars($old_username) : ''; ?>" required />
                <input type="password" name="password" placeholder="Password" required />
                
                <a href="#">Forgot your password?</a>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login with your personal info</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                
                <div class="overlay-panel overlay-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start journey with us</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        // เมือกดปุ่ม Sign Up (ในแผ่น overlay) ให้เลื่อนแผ่นไปซ้าย
        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        // เมือกดปุ่ม Sign In (ในแผ่น overlay) ให้เลื่อนแผ่นกลับมาขวา
        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>

</body>
</html>
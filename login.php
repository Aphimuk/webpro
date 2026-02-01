<?php
session_start();
require_once ('connect.php');

// --- PHP Logic ส่วนเดิม ---
$old_fullname = "";
$old_username = "";
$old_address = "";  
$old_phone = "";    

$register_error = ""; 
$register_success = ""; 
$login_error = "";    

$is_register_active = false;

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
        $register_error = "⚠️ กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        $check_sql = "SELECT username FROM users WHERE username = '$user'";
        if ($conn->query($check_sql)->num_rows > 0) {
            $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว!";
        } else {
            $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, fullname, address, phone, role) 
                    VALUES ('$user', '$password_hashed', '$name', '$address', '$phone', '$role')";
            
            if($conn->query($sql)){ 
                $register_success = "✅ สมัครสมาชิกสำเร็จ!";
                $is_register_active = false; 
                $old_fullname = ""; $old_address = ""; $old_phone = "";
            } else {
                $register_error = "Error: " . $conn->error;
            }
        }
    }
}

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
            $_SESSION['alert_msg'] = "ยินดีต้อนรับคุณ {$row['fullname']} !";
            $_SESSION['alert_type'] = "success";

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
    <title>เข้าสู่ระบบ - ร้านไก่ทอดบักปึก</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background: #FFFDE7; /* พื้นหลังครีม */
            font-family: 'Sarabun', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
        }
        
        /* --- แก้ไข: ตั้งค่าสีข้อความทั่วไป (สำหรับฝั่งฟอร์มสีขาว) --- */
        h1 { 
            font-weight: 800; margin: 0; 
            color: #C62828; /* แดงเข้ม (อยู่บนพื้นขาว อ่านง่าย) */
        }
        p { font-size: 14px; font-weight: 500; line-height: 20px; letter-spacing: 0.5px; margin: 20px 0 30px; color: #3E2723; }
        span { font-size: 12px; color: #5D4037; font-weight: 500; margin-bottom: 10px; display: block;}
        a { color: #333; font-size: 14px; text-decoration: none; margin: 15px 0 10px; font-weight: bold; }
        a:hover { text-decoration: underline; color: #D84315; }

        /* --- ปุ่มหลัก (Sign In / Sign Up) --- */
        button {
            border-radius: 50px;
            border: 1px solid #BF360C;
            background-color: #D84315; /* ส้มอิฐ */
            color: #FFFFFF;
            font-size: 14px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in, background-color 0.2s;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            margin-top: 10px;
        }
        button:hover { background-color: #BF360C; }
        button:active { transform: scale(0.95); }
        
        /* --- ปุ่ม Ghost (ปุ่มบนแถบสีแดง) --- */
        button.ghost { 
            background-color: transparent; 
            border-color: #FFFFFF; 
            color: #FFFFFF; /* บังคับตัวหนังสือสีขาว */
            box-shadow: none;
        }
        button.ghost:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
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
            background-color: #FFFFFF; /* เปลี่ยนเป็นขาวล้วน */
            border: 2px solid #EEEEEE; /* ขอบสีเทาจางๆ */
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            color: #333;
            font-weight: 500;
        }
        input:focus { outline: none; border-color: #FF6D00; background-color: #FFF8E1; }

        .container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            min-height: 600px;
        }
        
        /* Animation */
        .form-container { position: absolute; top: 0; height: 100%; transition: all 0.6s ease-in-out; }
        .sign-in-container { left: 0; width: 50%; z-index: 2; }
        .container.right-panel-active .sign-in-container { transform: translateX(100%); }
        .sign-up-container { left: 0; width: 50%; opacity: 0; z-index: 1; }
        .container.right-panel-active .sign-up-container { transform: translateX(100%); opacity: 1; z-index: 5; animation: show 0.6s; }
        @keyframes show { 0%, 49.99% { opacity: 0; z-index: 1; } 50%, 100% { opacity: 1; z-index: 5; } }
        
        .overlay-container { position: absolute; top: 0; left: 50%; width: 50%; height: 100%; overflow: hidden; transition: transform 0.6s ease-in-out; z-index: 100; }
        .container.right-panel-active .overlay-container { transform: translateX(-100%); }
        
        /* --- แถบสีด้านข้าง (Gradient ส้มแดงเข้ม) --- */
        .overlay {
            background: #B71C1C;
            background: -webkit-linear-gradient(to right, #D84315, #B71C1C);
            background: linear-gradient(to right, #D84315, #B71C1C);
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
        
        /* --- สำคัญ: แก้ไขสีตัวหนังสือที่อยู่บนแถบสีแดง ให้เป็นสีขาว --- */
        .overlay-panel h1 {
            color: #FFFFFF !important; /* บังคับสีขาว */
            text-shadow: 0 2px 4px rgba(0,0,0,0.3); /* ใส่เงาให้อ่านง่ายขึ้น */
        }
        .overlay-panel p {
            color: #FFFFFF !important; /* บังคับสีขาว */
            font-weight: 500;
        }
        
        .container.right-panel-active .overlay { transform: translateX(50%); }
        .overlay-panel { position: absolute; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0 40px; text-align: center; top: 0; height: 100%; width: 50%; transform: translateX(0); transition: transform 0.6s ease-in-out; }
        .overlay-left { transform: translateX(-20%); }
        .container.right-panel-active .overlay-left { transform: translateX(0); }
        .overlay-right { right: 0; transform: translateX(0); }
        .container.right-panel-active .overlay-right { transform: translateX(20%); }

        .alert-text { color: #D32F2F; font-weight: bold; margin-bottom: 10px; background: #FFEBEE; padding: 10px; border-radius: 8px; width: 100%; border: 1px solid #EF9A9A; }
        .success-text { color: #1B5E20; font-weight: bold; margin-bottom: 10px; background: #E8F5E9; padding: 10px; border-radius: 8px; width: 100%; border: 1px solid #A5D6A7; }
        .input-error { border: 2px solid #D32F2F !important; background-color: #FFEBEE !important; }
    </style>
</head>
<body>

    <div class="container <?php echo $is_register_active ? 'right-panel-active' : ''; ?>" id="container">
        
        <div class="form-container sign-up-container">
            <form method="post" id="registerForm" novalidate>
                <h1>สมัครสมาชิก</h1>
                <span>กรอกข้อมูลเพื่อเริ่มสั่งความอร่อย</span>
                
                <?php if($register_error != ""): ?>
                    <div class="alert-text"><?php echo $register_error; ?></div>
                <?php endif; ?>
                
                <div id="js-error" class="alert-text" style="display:none;"></div>

                <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" value="<?php echo htmlspecialchars($old_fullname); ?>" data-label="ชื่อ-นามสกุล" required />
                <input type="text" name="username" placeholder="ชื่อผู้ใช้ (Username)" value="<?php echo htmlspecialchars($old_username); ?>" data-label="ชื่อผู้ใช้" required />
                <input type="password" name="password" placeholder="รหัสผ่าน" data-label="รหัสผ่าน" required />
                <input type="text" name="phone" placeholder="เบอร์โทรศัพท์" value="<?php echo htmlspecialchars($old_phone); ?>" data-label="เบอร์โทรศัพท์" required />
                <input type="text" name="address" placeholder="ที่อยู่ (ถ้ามี)" value="<?php echo htmlspecialchars($old_address); ?>" />

                <button type="submit" name="register">สมัครสมาชิก</button>
            </form>
        </div>

        <div class="form-container sign-in-container">
            <form method="post" novalidate>
                <h1>เข้าสู่ระบบ</h1>
                <span>ยินดีต้อนรับสู่ร้านบักปึก</span>
                
                <?php if($register_success != ""): ?>
                    <div class="success-text"><?php echo $register_success; ?></div>
                <?php endif; ?>
                <?php if($login_error != ""): ?>
                    <div class="alert-text"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <input type="text" name="username" placeholder="ชื่อผู้ใช้ (Username)" value="<?php echo ($register_success != "") ? htmlspecialchars($old_username) : ''; ?>" required />
                <input type="password" name="password" placeholder="รหัสผ่าน" required />
                
                <a href="forgot_password.php">ลืมรหัสผ่าน?</a>
                
                <button type="submit" name="login">เข้าสู่ระบบ</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                
                <div class="overlay-panel overlay-left">
                    <h1>สวัสดีเพื่อนใหม่!</h1>
                    <p>สมัครสมาชิกวันนี้<br>เพื่อรับประสบการณ์ความอร่อยที่เหนือกว่า</p>
                    
                    <button class="ghost" id="signIn">ไปหน้าเข้าสู่ระบบ</button>
                </div>
                
                <div class="overlay-panel overlay-right">
                    <h1>หิวแล้วใช่ไหม?</h1>
                    <p>กลับเข้าสู่ระบบเพื่อสั่งไก่ทอดร้อนๆ<br>ส่งตรงถึงบ้านคุณ</p>
                    <button class="ghost" id="signUp">สมัครสมาชิกใหม่</button>
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

        signUpButton.addEventListener('click', () => { container.classList.add("right-panel-active"); });
        signInButton.addEventListener('click', () => { container.classList.remove("right-panel-active"); });

        registerForm.addEventListener('submit', function(e) {
            let errors = [];
            let inputs = registerForm.querySelectorAll('input[required]');
            inputs.forEach(input => input.classList.remove('input-error'));
            jsErrorDiv.style.display = 'none';
            jsErrorDiv.innerHTML = '';

            inputs.forEach(function(input) {
                if (!input.value.trim()) {
                    errors.push(input.getAttribute('data-label'));
                    input.classList.add('input-error');
                }
            });

            if (errors.length > 0) {
                e.preventDefault();
                jsErrorDiv.style.display = 'block';
                jsErrorDiv.innerHTML = '⚠️ กรุณากรอก: ' + errors.join(', ');
            }
        });
    </script>

</body>
</html>
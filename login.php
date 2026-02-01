<?php
session_start();
require_once ('connect.php');
// ... (Logic PHP ส่วนบนคงเดิมได้เลย หรือก๊อปจากอันก่อนหน้านี้มา) ...
// แต่ให้เปลี่ยนส่วน <style> ใน <head> ดังนี้ครับ:
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
            /* พื้นหลังครีมเหลืองอ่อนๆ เพื่อให้อ่านง่าย */
            background: #FFFDE7; 
            font-family: 'Sarabun', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
        }
        
        h1 { 
            font-weight: 800; margin: 0; 
            color: #C62828; /* แดงเข้ม ชัดเจน */
        }
        
        p { font-size: 14px; font-weight: 400; line-height: 20px; letter-spacing: 0.5px; margin: 20px 0 30px; color: #3E2723; }
        span { font-size: 12px; color: #5D4037; }
        
        button {
            border-radius: 50px;
            border: 1px solid #BF360C;
            background-color: #D84315; /* ส้มอิฐเข้ม (High Contrast) */
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
        }
        button:hover { background-color: #BF360C; }
        button:active { transform: scale(0.95); }
        button.ghost { 
            background-color: transparent; 
            border-color: #FFFFFF; 
            box-shadow: none;
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
            background-color: #F5F5F5; /* เทาอ่อน ตัดกับพื้นขาว */
            border: 1px solid #E0E0E0;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            color: #333;
            font-weight: 500;
        }
        input:focus { outline: 2px solid #FF6D00; }

        .container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 850px; /* ขยายให้กว้างขึ้น */
            max-width: 100%;
            min-height: 550px;
        }
        
        /* ... (CSS Animation ส่วนเดิมใช้ได้เลย) ... */
        .form-container { position: absolute; top: 0; height: 100%; transition: all 0.6s ease-in-out; }
        .sign-in-container { left: 0; width: 50%; z-index: 2; }
        .container.right-panel-active .sign-in-container { transform: translateX(100%); }
        .sign-up-container { left: 0; width: 50%; opacity: 0; z-index: 1; }
        .container.right-panel-active .sign-up-container { transform: translateX(100%); opacity: 1; z-index: 5; animation: show 0.6s; }
        @keyframes show { 0%, 49.99% { opacity: 0; z-index: 1; } 50%, 100% { opacity: 1; z-index: 5; } }
        .overlay-container { position: absolute; top: 0; left: 50%; width: 50%; height: 100%; overflow: hidden; transition: transform 0.6s ease-in-out; z-index: 100; }
        .container.right-panel-active .overlay-container { transform: translateX(-100%); }
        
        .overlay {
            /* สี Gradient พื้นหลังด้านข้าง: แดงเข้มไล่ไปส้ม */
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
        
        /* ... (CSS Overlay Panel ส่วนเดิม) ... */
        .container.right-panel-active .overlay { transform: translateX(50%); }
        .overlay-panel { position: absolute; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0 40px; text-align: center; top: 0; height: 100%; width: 50%; transform: translateX(0); transition: transform 0.6s ease-in-out; }
        .overlay-left { transform: translateX(-20%); }
        .container.right-panel-active .overlay-left { transform: translateX(0); }
        .overlay-right { right: 0; transform: translateX(0); }
        .container.right-panel-active .overlay-right { transform: translateX(20%); }

        .alert-text { color: #D32F2F; font-weight: bold; margin-bottom: 10px; background: #FFEBEE; padding: 5px 10px; border-radius: 5px; width: 100%; }
        .success-text { color: #1B5E20; font-weight: bold; margin-bottom: 10px; background: #E8F5E9; padding: 5px 10px; border-radius: 5px; width: 100%; }
        .input-error { border: 2px solid #D32F2F !important; background-color: #FFEBEE !important; }
    </style>
</head>
<body>
    ```
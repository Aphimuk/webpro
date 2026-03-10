<?php
session_start();

$action = isset($_GET['action']) ? $_GET['action'] : '';
// เช็คว่ามาจากหน้าไหน เพื่อให้ Redirect กลับไปหน้าเดิมได้ถูกต้อง
$return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

if ($action == 'add') {
    $id = $_GET['id'];
    $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = array(); }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    
    $_SESSION['alert_msg'] = "✅ เพิ่มเมนูลงตะกร้าเรียบร้อยแล้ว!";
    $_SESSION['alert_type'] = "success";
    header("Location: " . $return_url); 
    exit();
} 
elseif ($action == 'decrease') {
    $id = $_GET['id'];
    if (isset($_SESSION['cart'][$id])) {
        if ($_SESSION['cart'][$id] > 1) {
            $_SESSION['cart'][$id]--;
            $_SESSION['alert_msg'] = "➖ ลดจำนวนสินค้าแล้ว";
            $_SESSION['alert_type'] = "warning";
        } else {
            // ถ้าเหลือ 1 แล้วกดลดอีก ให้ลบออกจากตะกร้าเลย
            unset($_SESSION['cart'][$id]);
            $_SESSION['alert_msg'] = "🗑️ ลบรายการออกจากตะกร้าแล้ว";
            $_SESSION['alert_type'] = "warning";
        }
    }
    header("Location: cart.php");
    exit();
}
elseif ($action == 'delete') {
    $id = $_GET['id'];
    unset($_SESSION['cart'][$id]);
    
    $_SESSION['alert_msg'] = "🗑️ ลบรายการออกจากตะกร้าแล้ว";
    $_SESSION['alert_type'] = "warning";
    header("Location: cart.php");
    exit();
}
elseif ($action == 'clear') {
    unset($_SESSION['cart']);
    header("Location: index.php");
    exit();
}
?>
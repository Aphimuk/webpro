<?php
session_start();

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add') {
    $id = $_GET['id'];
    $qty = isset($_GET['qty']) ? $_GET['qty'] : 1;
    
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = array(); }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    
    
    $_SESSION['alert_msg'] = "✅ เพิ่มเมนูลงตะกร้าเรียบร้อยแล้ว!";
    $_SESSION['alert_type'] = "success";
    
    
    header("Location: index.php"); 
} 
elseif ($action == 'delete') {
    $id = $_GET['id'];
    unset($_SESSION['cart'][$id]);
    
    $_SESSION['alert_msg'] = "🗑️ ลบรายการออกจากตะกร้าแล้ว";
    $_SESSION['alert_type'] = "warning";
    header("Location: cart.php");
}
elseif ($action == 'clear') {
    unset($_SESSION['cart']);
    header("Location: index.php");
}
?>
<?php
session_start();
// action: add, update, delete, clear
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add') {
    $id = $_GET['id'];
    $qty = isset($_GET['qty']) ? $_GET['qty'] : 1;
    
    // ตรวจสอบว่ามีตะกร้าหรือยัง
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // ถ้ามีสินค้านี้อยู่แล้ว ให้เพิ่มจำนวน
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    header("Location: cart.php");
} 
elseif ($action == 'delete') {
    $id = $_GET['id'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
}
elseif ($action == 'clear') {
    unset($_SESSION['cart']);
    header("Location: index.php");
}
?>
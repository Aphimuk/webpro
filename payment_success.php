<?php
session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $method = $_POST['method'];

    // Update เป็น "จ่ายแล้ว" และสถานะเป็น "กำลังปรุง" (Cooking) ทันที (Auto)
    $sql = "UPDATE orders SET 
            payment_status = 'paid', 
            payment_method = '$method', 
            status = 'cooking' 
            WHERE order_id = $order_id";

    if ($conn->query($sql)) {
        $_SESSION['alert_msg'] = "🎉 ชำระเงินสำเร็จ! ระบบได้รับยอดเงินแล้ว (Auto)";
        $_SESSION['alert_type'] = "success";
        header("Location: my_orders.php");
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: index.php");
}
?>
<?php
session_start();
require_once ('connect.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$total_amount = 0;

// คำนวณยอดรวม
foreach($_SESSION['cart'] as $p_id => $qty){
    $sql_p = "SELECT price FROM products WHERE product_id = $p_id";
    $res_p = $conn->query($sql_p);
    $row_p = $res_p->fetch_assoc();
    $total_amount += ($row_p['price'] * $qty);
}

// 1. Insert Orders
$sql_order = "INSERT INTO orders (user_id, total_amount, status) VALUES ('$user_id', '$total_amount', 'pending')";
if ($conn->query($sql_order) === TRUE) {
    $order_id = $conn->insert_id;

    // 2. Insert Order Details
    foreach($_SESSION['cart'] as $p_id => $qty){
        $sql_p = "SELECT price FROM products WHERE product_id = $p_id";
        $res_p = $conn->query($sql_p);
        $row_p = $res_p->fetch_assoc();
        $price = $row_p['price'];

        $sql_detail = "INSERT INTO order_details (order_id, product_id, quantity, price_per_unit) 
                       VALUES ('$order_id', '$p_id', '$qty', '$price')";
        $conn->query($sql_detail);
    }

    // 3. ล้างตะกร้า และส่งแจ้งเตือน
    unset($_SESSION['cart']);
    
    // --- เปลี่ยนตรงนี้: ส่งค่าไปแจ้งเตือนหน้า my_orders แทน Popup ---
    $_SESSION['alert_msg'] = "🎉 สั่งซื้อสำเร็จ! รหัสคำสั่งซื้อของคุณคือ #$order_id ทางร้านได้รับออเดอร์แล้วครับ";
    $_SESSION['alert_type'] = "success";
    header("Location: my_orders.php");
    exit();

} else {
    $_SESSION['alert_msg'] = "❌ เกิดข้อผิดพลาด: " . $conn->error;
    $_SESSION['alert_type'] = "danger";
    header("Location: cart.php");
}
?>
<?php
session_start();
require_once ('connect.php');

// เช็คสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { die("Access Denied"); }

$oid = $_GET['order_id'];

// ดึงข้อมูลออเดอร์ + ข้อมูลลูกค้า (ที่อยู่)
$sql_order = "SELECT o.*, u.fullname, u.address, u.phone 
              FROM orders o JOIN users u ON o.user_id = u.user_id 
              WHERE o.order_id = $oid";
$order_info = $conn->query($sql_order)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำสั่งซื้อ #<?php echo $oid; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h3>รายละเอียดคำสั่งซื้อ #<?php echo $oid; ?></h3>
        <div class="card mb-3">
            <div class="card-body">
                <h5>ข้อมูลลูกค้า / จัดส่ง</h5>
                <p>
                    <strong>ชื่อ:</strong> <?php echo $order_info['fullname']; ?><br>
                    <strong>เบอร์โทร:</strong> <?php echo $order_info['phone']; ?><br>
                    <strong>ที่อยู่จัดส่ง:</strong> <?php echo $order_info['address']; ?>
                </p>
                <p><strong>สถานะ:</strong> <span class="badge bg-info"><?php echo $order_info['status']; ?></span></p>
            </div>
        </div>

        <h5>รายการอาหารที่สั่ง</h5>
        <table class="table table-bordered">
            <thead><tr><th>เมนู</th><th>ราคาต่อหน่วย</th><th>จำนวน</th><th>รวม</th></tr></thead>
            <tbody>
                <?php
                $sql_items = "SELECT d.*, p.product_name 
                              FROM order_details d JOIN products p ON d.product_id = p.product_id 
                              WHERE d.order_id = $oid";
                $items = $conn->query($sql_items);
                while($item = $items->fetch_assoc()){
                    $subtotal = $item['price_per_unit'] * $item['quantity'];
                    echo "<tr>
                        <td>{$item['product_name']}</td>
                        <td>{$item['price_per_unit']}</td>
                        <td>{$item['quantity']}</td>
                        <td>".number_format($subtotal, 2)."</td>
                    </tr>";
                }
                ?>
                <tr class="table-dark">
                    <td colspan="3" class="text-end">ยอดรวมทั้งสิ้น</td>
                    <td><?php echo number_format($order_info['total_amount'], 2); ?></td>
                </tr>
            </tbody>
        </table>
        <a href="admin_panel.php" class="btn btn-secondary">กลับหน้า Admin</a>
    </div>
</body>
</html>
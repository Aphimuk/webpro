<?php
session_start();
require_once ('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { die("Access Denied"); }

$oid = $_GET['order_id'];

// ดึงข้อมูล
$sql_order = "SELECT o.*, u.fullname, u.address, u.phone 
              FROM orders o JOIN users u ON o.user_id = u.user_id 
              WHERE o.order_id = $oid";
$order_info = $conn->query($sql_order)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo $oid; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-4">
        <div class="card shadow border-0">
            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #37474F;">
                <h4 class="m-0">🧾 รายละเอียดคำสั่งซื้อ #<?php echo $oid; ?></h4>
                <a href="admin_panel.php?page=orders" class="btn btn-sm btn-light fw-bold text-dark">ย้อนกลับ</a>
            </div>
            <div class="card-body p-4">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white h-100">
                            <h5 class="text-danger fw-bold border-bottom pb-2"><i class="fas fa-user"></i> ข้อมูลลูกค้า</h5>
                            <p class="mb-1"><strong>ชื่อ:</strong> <?php echo $order_info['fullname']; ?></p>
                            <p class="mb-1"><strong>เบอร์โทร:</strong> <?php echo $order_info['phone']; ?></p>
                            <p class="mb-0"><strong>ที่อยู่:</strong> <?php echo $order_info['address']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white h-100">
                            <h5 class="text-danger fw-bold border-bottom pb-2"><i class="fas fa-info-circle"></i> สถานะออเดอร์</h5>
                            <p class="mb-2"><strong>วันที่สั่ง:</strong> <?php echo $order_info['order_date']; ?></p>
                            <p class="mb-0"><strong>สถานะปัจจุบัน:</strong> 
                                <?php 
                                    $st = $order_info['status'];
                                    $badge = 'secondary';
                                    if($st=='pending') $badge='warning text-dark';
                                    if($st=='cooking') $badge='info text-dark';
                                    if($st=='completed') $badge='success';
                                    if($st=='cancelled') $badge='danger';
                                    echo "<span class='badge bg-$badge fs-6'>".strtoupper($st)."</span>";
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold text-secondary">รายการอาหารที่สั่ง</h5>
                <table class="table table-bordered align-middle">
                    <thead class="table-light"><tr><th>เมนู</th><th>ราคาต่อหน่วย</th><th>จำนวน</th><th>รวม</th></tr></thead>
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
                                <td>".number_format($item['price_per_unit'], 2)."</td>
                                <td>{$item['quantity']}</td>
                                <td class='fw-bold'>".number_format($subtotal, 2)."</td>
                            </tr>";
                        }
                        ?>
                        <tr style="background-color: #FFF3E0;">
                            <td colspan="3" class="text-end fw-bold fs-5">ยอดรวมทั้งสิ้น</td>
                            <td class="fw-bold text-danger fs-4"><?php echo number_format($order_info['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="text-end mt-4">
                    <a href="admin_panel.php?page=orders" class="btn btn-secondary px-4">กลับหน้ารวมออเดอร์</a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
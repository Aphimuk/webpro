<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// รับค่า Order ID
if (!isset($_GET['order_id'])) { header("Location: my_orders.php"); exit(); }
$order_id = $_GET['order_id'];

// ตรวจสอบว่าเป็นออเดอร์ของคนนี้จริงไหม
$uid = $_SESSION['user_id'];
$sql = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = $uid";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "ไม่พบออเดอร์"; exit();
}

$order = $result->fetch_assoc();

// ถ้ามีการส่งฟอร์มแจ้งโอน
if (isset($_POST['submit_payment'])) {
    $target_dir = __DIR__ . "/img/slips/"; // สร้างโฟลเดอร์ img/slips ด้วยนะครับ
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $filename = basename($_FILES['slip_image']['name']);
    $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // ตั้งชื่อไฟล์ใหม่ป้องกันชื่อซ้ำ: slip_orderID_timestamp.jpg
    $new_filename = "slip_" . $order_id . "_" . time() . "." . $fileType;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES['slip_image']['tmp_name'], $target_file)) {
        // อัปเดตฐานข้อมูล
        $sql_update = "UPDATE orders SET slip_file = '$new_filename', payment_date = NOW() WHERE order_id = $order_id";
        $conn->query($sql_update);

        $_SESSION['alert_msg'] = "✅ แจ้งโอนเงินเรียบร้อยแล้ว รอผู้ดูแลตรวจสอบครับ";
        $_SESSION['alert_type'] = "success";
        header("Location: my_orders.php");
        exit();
    } else {
        $error = "❌ อัปโหลดไฟล์ไม่สำเร็จ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แจ้งชำระเงิน - ออเดอร์ #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header text-white bg-primary text-center py-3">
                        <h4 class="mb-0">💸 แจ้งชำระเงิน (Order #<?php echo $order_id; ?>)</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="alert alert-info text-center">
                            <h5 class="fw-bold"><i class="fas fa-university"></i> ธนาคารกสิกรไทย</h5>
                            <p class="mb-1">ชื่อบัญชี: <strong>ร้านไก่ทอดบักปึก จำกัด</strong></p>
                            <h3 class="text-primary fw-bold my-2">099-1-23456-7</h3>
                            <p class="mb-0 small text-muted">ยอดที่ต้องชำระ: <strong class="text-danger fs-5"><?php echo number_format($order['total_amount'], 2); ?></strong> บาท</p>
                        </div>

                        <hr>

                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">แนบหลักฐานการโอนเงิน (สลิป)</label>
                                <input type="file" name="slip_image" class="form-control" accept="image/*" required>
                                <div class="form-text">รองรับไฟล์ .jpg, .png, .jpeg</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="submit_payment" class="btn btn-success btn-lg">
                                    ยืนยันการแจ้งโอน <i class="fas fa-paper-plane"></i>
                                </button>
                                <a href="my_orders.php" class="btn btn-secondary">ยกเลิก</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
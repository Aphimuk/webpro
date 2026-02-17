<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) { header("Location: index.php"); exit(); }

$order_id = $_GET['order_id'];
$uid = $_SESSION['user_id'];
$order = $conn->query("SELECT * FROM orders WHERE order_id = $order_id AND user_id = $uid")->fetch_assoc();

// ป้องกันการจ่ายซ้ำ
if($order['payment_status'] == 'paid'){
    header("Location: my_orders.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f2f5; }
        .payment-card { max-width: 500px; margin: 50px auto; border-radius: 15px; border: none; }
        .nav-pills .nav-link.active { background-color: #1A237E; }
        /* หน้าจอโหลด */
        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; text-align: center; padding-top: 20%; }
    </style>
</head>
<body>

<div id="loader" class="overlay">
    <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"></div>
    <h3 class="mt-3 text-dark fw-bold">กำลังดำเนินการชำระเงิน...</h3>
    <p class="text-muted">กรุณาอย่าปิดหน้าต่างนี้</p>
</div>

<div class="container">
    <div class="card shadow-lg payment-card">
        <div class="card-header text-white text-center py-4" style="background: linear-gradient(45deg, #1A237E, #283593);">
            <h4 class="mb-0"><i class="fas fa-lock"></i> Secure Payment</h4>
            <small>ระบบชำระเงินปลอดภัย 100%</small>
        </div>
        <div class="card-body p-4">
            <h5 class="text-center text-muted mb-1">ยอดชำระ Order #<?php echo $order_id; ?></h5>
            <h1 class="text-center text-primary fw-bold mb-4">฿<?php echo number_format($order['total_amount'], 2); ?></h1>

            <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4 me-2" data-bs-toggle="pill" data-bs-target="#credit-card">
                        <i class="far fa-credit-card"></i> บัตรเครดิต
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#qrcode">
                        <i class="fas fa-qrcode"></i> QR Code
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <div class="tab-pane fade show active" id="credit-card">
                    <form id="cardForm" action="payment_success.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="method" value="credit_card">
                        <div class="mb-3">
                            <label>หมายเลขบัตร</label>
                            <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>วันหมดอายุ</label>
                                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label>CVV</label>
                                <input type="password" class="form-control" placeholder="123" maxlength="3" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg rounded-pill mt-2">ชำระเงินทันที</button>
                    </form>
                </div>

                <div class="tab-pane fade text-center" id="qrcode">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=FakePayment_Order_<?php echo $order_id; ?>" class="img-thumbnail mb-3">
                    <p class="text-muted small">สแกนเพื่อชำระเงินผ่านแอปธนาคาร</p>
                    <form action="payment_success.php" method="POST" id="qrForm">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="method" value="qr_code">
                        <button type="button" onclick="fakeScan()" class="btn btn-success w-100 btn-lg rounded-pill">จำลองการสแกนสำเร็จ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Script จำลองการโหลด
    document.getElementById('cardForm').addEventListener('submit', function(e) {
        e.preventDefault(); // หยุดการส่งฟอร์มปกติ
        document.getElementById('loader').style.display = 'block'; // โชว์ Loading
        setTimeout(() => { this.submit(); }, 2000); // รอ 2 วิ แล้วส่งจริง
    });

    function fakeScan() {
        document.getElementById('loader').style.display = 'block';
        setTimeout(() => { document.getElementById('qrForm').submit(); }, 2000);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
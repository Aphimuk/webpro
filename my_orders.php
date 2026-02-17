<?php
session_start();
require_once ('connect.php');
if (!isset($_SESSION['user_id'])) header("Location: login.php");

// Logic Update Profile
if(isset($_POST['update_profile'])){
    $uid = $_SESSION['user_id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $conn->query("UPDATE users SET fullname='$fullname', phone='$phone', address='$address' WHERE user_id=$uid");
    
    $_SESSION['fullname'] = $fullname;
    $_SESSION['alert_msg'] = "✅ บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว";
    $_SESSION['alert_type'] = "success";
    header("Location: my_orders.php"); 
    exit();
}

$uid = $_SESSION['user_id'];
$user_info = $conn->query("SELECT * FROM users WHERE user_id=$uid")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลของฉัน - บักปึก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header text-white fw-bold text-center py-3" style="background-color: #D84315;">
                        <i class="fas fa-user-edit"></i> แก้ไขข้อมูลส่วนตัว
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="fw-bold text-secondary">ชื่อ-นามสกุล</label>
                                <input type="text" name="fullname" class="form-control" value="<?php echo $user_info['fullname']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold text-secondary">เบอร์โทรศัพท์</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $user_info['phone']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold text-secondary">ที่อยู่จัดส่ง</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo $user_info['address']; ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-success w-100 rounded-pill">บันทึกข้อมูล</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="fas fa-history"></i> ประวัติการสั่งซื้อ
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#รหัส</th>
                                        <th>วันที่สั่ง</th>
                                        <th>ยอดรวม</th>
                                        <th>การชำระเงิน</th>
                                        <th>สถานะร้าน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $orders = $conn->query("SELECT * FROM orders WHERE user_id=$uid ORDER BY order_id DESC");
                                    if($orders->num_rows > 0):
                                        while($o = $orders->fetch_assoc()){
                                            // Check Slip
                                            $has_slip = !empty($o['slip_file']);
                                            
                                            // Status Badge
                                            $badge_color = 'bg-secondary';
                                            $status_text = $o['status'];
                                            if($o['status']=='pending') { $badge_color = 'bg-warning text-dark'; $status_text = 'รอรับออเดอร์'; }
                                            if($o['status']=='cooking') { $badge_color = 'bg-info text-dark'; $status_text = 'กำลังปรุง'; }
                                            if($o['status']=='completed') { $badge_color = 'bg-success'; $status_text = 'เสร็จสิ้น'; }
                                            if($o['status']=='cancelled') { $badge_color = 'bg-danger'; $status_text = 'ยกเลิก'; }

                                            echo "<tr>
                                                <td class='fw-bold text-muted'>#{$o['order_id']}</td>
                                                <td>".date('d/m/Y', strtotime($o['order_date']))."<br><small class='text-muted'>".date('H:i', strtotime($o['order_date']))."</small></td>
                                                <td class='fw-bold text-danger'>฿".number_format($o['total_amount'], 2)."</td>
                                                <td>";
                                            
                                            // Logic ปุ่มจ่ายเงิน
                                            if($o['status'] == 'cancelled'){
                                                echo "<span class='text-muted small'>- ยกเลิก -</span>";
                                            } else {
                                                if ($has_slip) {
                                                    echo "<a href='img/slips/{$o['slip_file']}' target='_blank' class='btn btn-sm btn-outline-info rounded-pill'><i class='fas fa-receipt'></i> ดูสลิป</a>
                                                          <div class='text-success small mt-1'><i class='fas fa-check-circle'></i> แจ้งโอนแล้ว</div>";
                                                } else {
                                                    echo "<a href='payment.php?order_id={$o['order_id']}' class='btn btn-sm btn-primary rounded-pill px-3'>
                                                            <i class='fas fa-money-bill-wave'></i> แจ้งโอน
                                                          </a>";
                                                }
                                            }

                                            echo "</td>
                                                <td><span class='badge rounded-pill $badge_color'>$status_text</span></td>
                                            </tr>";
                                        }
                                    else:
                                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>ยังไม่มีประวัติการสั่งซื้อ</td></tr>";
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
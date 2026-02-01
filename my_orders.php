<?php
session_start();
require_once ('connect.php');
if (!isset($_SESSION['user_id'])) header("Location: login.php");

// Logic แก้ไขข้อมูลส่วนตัว
if(isset($_POST['update_profile'])){
    $uid = $_SESSION['user_id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $conn->query("UPDATE users SET fullname='$fullname', phone='$phone', address='$address' WHERE user_id=$uid");
    $_SESSION['fullname'] = $fullname; // อัปเดต session
    echo "<script>alert('อัปเดตข้อมูลแล้ว');</script>";
}

// ดึงข้อมูล User
$uid = $_SESSION['user_id'];
$user_info = $conn->query("SELECT * FROM users WHERE user_id=$uid")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลส่วนตัว</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <div class="card p-3 mb-4">
                    <h4>แก้ไขข้อมูลส่วนตัว</h4>
                    <form method="post">
                        <label>ชื่อ-นามสกุล</label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo $user_info['fullname']; ?>">
                        <label>เบอร์โทร</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $user_info['phone']; ?>">
                        <label>ที่อยู่จัดส่ง</label>
                        <textarea name="address" class="form-control"><?php echo $user_info['address']; ?></textarea>
                        <button type="submit" name="update_profile" class="btn btn-primary mt-2">บันทึก</button>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <h4>ประวัติการสั่งซื้อ</h4>
                <table class="table table-striped">
                    <thead>
                        <tr><th>#Order</th><th>วันที่</th><th>ยอดรวม</th><th>สถานะ</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders = $conn->query("SELECT * FROM orders WHERE user_id=$uid ORDER BY order_id DESC");
                        while($o = $orders->fetch_assoc()){
                            $status_color = ($o['status']=='pending') ? 'text-warning' : 'text-success';
                            echo "<tr>
                                <td>{$o['order_id']}</td>
                                <td>{$o['order_date']}</td>
                                <td>{$o['total_amount']}</td>
                                <td class='$status_color'>".strtoupper($o['status'])."</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
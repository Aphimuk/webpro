<?php
session_start();
require_once ('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { die("Access Denied"); }

// รับค่า ID
if (isset($_GET['id'])) {
    $uid = $_GET['id'];
    $sql = "SELECT * FROM users WHERE user_id = $uid";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
} else {
    header("Location: admin_panel.php?page=customers"); exit();
}

// Update logic
if (isset($_POST['update_customer'])) {
    $uid = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    
    $sql = "UPDATE users SET fullname='$fullname', phone='$phone', address='$address' WHERE user_id=$uid";
    
    if($conn->query($sql)){
        $_SESSION['alert_msg'] = "✅ แก้ไขข้อมูลลูกค้าเรียบร้อยแล้ว";
        $_SESSION['alert_type'] = "success";
        header("Location: admin_panel.php?page=customers"); exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลลูกค้า - Admin</title>
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
                    <div class="card-header text-white" style="background-color: #37474F;">
                        <h4 class="mb-0">✏️ แก้ไขข้อมูลลูกค้า</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            
                            <div class="mb-3">
                                <label class="fw-bold">Username (แก้ไขไม่ได้)</label>
                                <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled readonly>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">ชื่อ-นามสกุล</label>
                                <input type="text" name="fullname" class="form-control" value="<?php echo $user['fullname']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">เบอร์โทรศัพท์</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">ที่อยู่</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo $user['address']; ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="admin_panel.php?page=customers" class="btn btn-secondary px-4">ยกเลิก</a>
                                <button type="submit" name="update_customer" class="btn btn-success px-4">บันทึกข้อมูล</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
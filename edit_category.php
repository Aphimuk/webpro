<?php
session_start();
require_once ('connect.php');

// ตรวจสอบว่าเป็น Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    die("Access Denied"); 
}

// 1. รับค่า ID ที่จะแก้ไข
if (isset($_GET['id'])) {
    $cid = $_GET['id'];
    $sql = "SELECT * FROM categories WHERE category_id = $cid";
    $result = $conn->query($sql);
    
    if($result->num_rows == 0){
        header("Location: admin_panel.php?page=categories"); exit();
    }
    
    $cat = $result->fetch_assoc();
} else {
    header("Location: admin_panel.php?page=categories"); exit();
}

// 2. Logic บันทึกข้อมูลเมื่อกดปุ่ม Update
if (isset($_POST['update_cat'])) {
    $cid = $_POST['category_id'];
    $cname = $_POST['category_name'];

    $sql = "UPDATE categories SET category_name='$cname' WHERE category_id=$cid";
    
    if($conn->query($sql)){
        $_SESSION['alert_msg'] = "✅ แก้ไขชื่อหมวดหมู่เรียบร้อยแล้ว";
        $_SESSION['alert_type'] = "success";
        header("Location: admin_panel.php?page=categories"); exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขหมวดหมู่ - Admin</title>
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
                    <div class="card-header text-white" style="background-color: #FF6D00;">
                        <h4 class="mb-0">✏️ แก้ไขชื่อหมวดหมู่</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="post">
                            <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">ชื่อหมวดหมู่</label>
                                <input type="text" name="category_name" class="form-control form-control-lg" value="<?php echo $cat['category_name']; ?>" required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="admin_panel.php?page=categories" class="btn btn-secondary px-4">ยกเลิก</a>
                                <button type="submit" name="update_cat" class="btn btn-success px-4 fw-bold">บันทึกการแก้ไข</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
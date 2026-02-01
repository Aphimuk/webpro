<?php
session_start();
require_once ('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied"); 
}

// Logic ต่างๆ (Delete/Add)
if (isset($_GET['delete_product'])) {
    $pid = $_GET['delete_product'];
    $res_imgs = $conn->query("SELECT image_file FROM product_images WHERE product_id=$pid");
    while($r = $res_imgs->fetch_assoc()){ @unlink("img/" . $r['image_file']); }
    $conn->query("DELETE FROM product_images WHERE product_id=$pid");
    $conn->query("DELETE FROM products WHERE product_id=$pid");
    
    $_SESSION['alert_msg'] = "🗑️ ลบสินค้าเรียบร้อยแล้ว";
    $_SESSION['alert_type'] = "warning";
    header("Location: admin_panel.php?page=products");
    exit();
}

if (isset($_POST['add_category'])) {
    $c_name = $_POST['cat_name'];
    $conn->query("INSERT INTO categories (category_name) VALUES ('$c_name')");
    $_SESSION['alert_msg'] = "✅ เพิ่มหมวดหมู่ '$c_name' สำเร็จ";
    $_SESSION['alert_type'] = "success";
    header("Location: admin_panel.php?page=categories");
    exit();
}

if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $st = $_POST['status'];
    $conn->query("UPDATE orders SET status='$st' WHERE order_id=$oid");
    $_SESSION['alert_msg'] = "✅ อัปเดตสถานะออเดอร์ #$oid เป็น $st เรียบร้อย";
    $_SESSION['alert_type'] = "info";
    header("Location: admin_panel.php?page=orders");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'orders';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - บักปึก ไก่ทอด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f6f9; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .nav-link { color: #b0bec5; margin-bottom: 5px; border-radius: 5px; }
        .nav-link:hover, .nav-link.active { background-color: #FF6D00; color: white; }
        .btn-add { background-color: #27ae60; color: white; border: none; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-3">
                <h5 class="text-white text-center py-3 border-bottom">🍗 Admin Menu</h5>
                <nav class="nav flex-column mt-3">
                    <a href="admin_panel.php?page=orders" class="nav-link <?php echo $page=='orders'?'active':''; ?>"><i class="fas fa-box"></i> ออเดอร์</a>
                    <a href="admin_panel.php?page=products" class="nav-link <?php echo $page=='products'?'active':''; ?>"><i class="fas fa-drumstick-bite"></i> สินค้า</a>
                    <a href="admin_panel.php?page=categories" class="nav-link <?php echo $page=='categories'?'active':''; ?>"><i class="fas fa-list"></i> หมวดหมู่</a>
                    <a href="admin_panel.php?page=customers" class="nav-link <?php echo $page=='customers'?'active':''; ?>"><i class="fas fa-users"></i> ลูกค้า</a>
                </nav>
                <div class="mt-4">
                    <a href="add_product.php" class="btn btn-add w-100 py-2 shadow-sm">+ เพิ่มสินค้าใหม่</a>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php if($page == 'orders'): ?>
                            <h3 class="text-secondary">📦 รายการสั่งซื้อล่าสุด</h3>
                            <table class="table table-hover mt-3 align-middle">
                                <thead class="table-light"><tr><th>ID</th><th>ลูกค้า</th><th>ยอดรวม</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
                                <?php
                                $res = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_id DESC");
                                while($row = $res->fetch_assoc()){
                                    echo "<tr>
                                        <td>#{$row['order_id']}</td>
                                        <td>{$row['username']}</td>
                                        <td class='fw-bold text-success'>".number_format($row['total_amount'])."</td>
                                        <td><span class='badge bg-secondary'>{$row['status']}</span></td>
                                        <td>
                                            <form method='post' class='d-flex gap-2'>
                                                <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                                <select name='status' class='form-select form-select-sm' style='width:120px;'>
                                                    <option value='pending'>รับออเดอร์</option>
                                                    <option value='cooking'>กำลังทอด</option>
                                                    <option value='completed'>เสร็จสิ้น</option>
                                                    <option value='cancelled'>ยกเลิก</option>
                                                </select>
                                                <button type='submit' name='update_status' class='btn btn-sm btn-primary'>บันทึก</button>
                                                <a href='admin_order_detail.php?order_id={$row['order_id']}' class='btn btn-sm btn-outline-dark'>ดู</a>
                                            </form>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </table>

                        <?php elseif($page == 'products'): ?>
                            <h3 class="text-secondary">🍗 จัดการสินค้า</h3>
                            <form class="d-flex mb-3 mt-3 w-50" method="GET">
                                <input type="hidden" name="page" value="products">
                                <input class="form-control me-2" type="search" name="search" placeholder="ค้นหาสินค้า...">
                                <button class="btn btn-outline-primary" type="submit">ค้นหา</button>
                            </form>
                            <table class="table table-bordered align-middle">
                                <thead><tr><th>รูป</th><th>ชื่อสินค้า</th><th>ราคา</th><th>จัดการ</th></tr></thead>
                                <?php
                                $search = isset($_GET['search']) ? $_GET['search'] : '';
                                $sql = "SELECT * FROM products WHERE product_name LIKE '%$search%' ORDER BY product_id DESC";
                                $res = $conn->query($sql);
                                while($row = $res->fetch_assoc()){
                                    $img_src = !empty($row['image_file']) ? "img/".$row['image_file'] : "https://via.placeholder.com/50";
                                    echo "<tr>
                                        <td><img src='$img_src' width='60' height='60' class='rounded' style='object-fit:cover;'></td>
                                        <td>{$row['product_name']}</td>
                                        <td>{$row['price']}</td>
                                        <td>
                                            <a href='edit_product.php?id={$row['product_id']}' class='btn btn-warning btn-sm'>แก้ไข</a>
                                            <a href='admin_panel.php?delete_product={$row['product_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"ลบจริงนะ?\")'>ลบ</a>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </table>

                        <?php elseif($page == 'categories'): ?>
                            <h3 class="text-secondary">📂 หมวดหมู่สินค้า</h3>
                            <form method="post" class="row g-3 mb-4 mt-2">
                                <div class="col-auto"><input type="text" name="cat_name" class="form-control" placeholder="ชื่อประเภทใหม่" required></div>
                                <div class="col-auto"><button type="submit" name="add_category" class="btn btn-success">เพิ่ม</button></div>
                            </form>
                            <ul class="list-group w-50">
                                <?php
                                $res = $conn->query("SELECT * FROM categories");
                                while($row = $res->fetch_assoc()){
                                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                        {$row['category_name']}
                                        <a href='admin_panel.php?delete_cat={$row['category_id']}' class='btn btn-sm btn-outline-danger'>ลบ</a>
                                    </li>";
                                }
                                ?>
                            </ul>

                        <?php elseif($page == 'customers'): ?>
                            <h3 class="text-secondary">👥 รายชื่อลูกค้า</h3>
                            <table class="table mt-3">
                                <thead><tr><th>User</th><th>ชื่อ-สกุล</th><th>เบอร์โทร</th><th>จัดการ</th></tr></thead>
                                <?php
                                $res = $conn->query("SELECT * FROM users WHERE role='customer'");
                                while($row = $res->fetch_assoc()){
                                    echo "<tr>
                                        <td>{$row['username']}</td>
                                        <td>{$row['fullname']}</td>
                                        <td>{$row['phone']}</td>
                                        <td><a href='admin_panel.php?delete_user={$row['user_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"ลบลูกค้า?\")'>ลบ</a></td>
                                    </tr>";
                                }
                                ?>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
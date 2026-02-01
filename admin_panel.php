<?php
session_start();
require_once ('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied"; exit();
}

// --------------------------------------------------------
// 1. Logic ลบสินค้า (Hard Delete + ลบรูป)
// --------------------------------------------------------
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

// --------------------------------------------------------
// 2. Logic หมวดหมู่
// --------------------------------------------------------
if (isset($_POST['add_category'])) {
    $c_name = $_POST['cat_name'];
    $conn->query("INSERT INTO categories (category_name) VALUES ('$c_name')");
    $_SESSION['alert_msg'] = "✅ เพิ่มหมวดหมู่สำเร็จ";
    $_SESSION['alert_type'] = "success";
    header("Location: admin_panel.php?page=categories");
    exit();
}
if (isset($_GET['delete_cat'])) {
    $cid = $_GET['delete_cat'];
    $conn->query("DELETE FROM categories WHERE category_id=$cid"); 
    $_SESSION['alert_msg'] = "🗑️ ลบหมวดหมู่เรียบร้อย";
    $_SESSION['alert_type'] = "warning";
    header("Location: admin_panel.php?page=categories");
    exit();
}

// --------------------------------------------------------
// 3. Logic อัปเดตสถานะออเดอร์
// --------------------------------------------------------
if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $st = $_POST['status'];
    $conn->query("UPDATE orders SET status='$st' WHERE order_id=$oid");
    $_SESSION['alert_msg'] = "✅ อัปเดตสถานะเรียบร้อย";
    $_SESSION['alert_type'] = "info";
    header("Location: admin_panel.php?page=orders");
    exit();
}

// --------------------------------------------------------
// 4. Logic ปุ่มที่ 1: ซ่อนลูกค้า (Soft Delete)
// --------------------------------------------------------
if (isset($_GET['hide_user'])) {
    $uid = $_GET['hide_user'];
    // เปลี่ยนสถานะเป็น 0 (ซ่อน) แต่ข้อมูลยังอยู่
    $conn->query("UPDATE users SET is_visible = 0 WHERE user_id = $uid");
    
    $_SESSION['alert_msg'] = "👻 ซ่อนรายชื่อลูกค้าแล้ว (ลูกค้ายัง Login ได้ตามปกติ)";
    $_SESSION['alert_type'] = "secondary"; 
    header("Location: admin_panel.php?page=customers");
    exit();
}

// --------------------------------------------------------
// 5. Logic ปุ่มที่ 2: ลบถาวร (Hard Delete)
// --------------------------------------------------------
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    
    // 5.1 ลบประวัติออเดอร์ที่ 'ยกเลิก' ทิ้งไปก่อน
    $get_cancelled = $conn->query("SELECT order_id FROM orders WHERE user_id=$uid AND status='cancelled'");
    while($row = $get_cancelled->fetch_assoc()){
        $oid = $row['order_id'];
        $conn->query("DELETE FROM order_details WHERE order_id=$oid");
        $conn->query("DELETE FROM orders WHERE order_id=$oid");
    }

    // 5.2 เช็คว่ามีออเดอร์ค้างอยู่ไหม (Pending/Cooking/Completed)
    $check_remaining = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id=$uid");
    $remaining = $check_remaining->fetch_assoc()['count'];

    if ($remaining > 0) {
        $_SESSION['alert_msg'] = "⚠️ ลบลูกค้าถาวรไม่ได้! เพราะยังมีออเดอร์ค้างอยู่ (ระบบลบให้เฉพาะประวัติที่ยกเลิกเท่านั้น)";
        $_SESSION['alert_type'] = "warning";
    } else {
        // ถ้าไม่มีออเดอร์ค้าง -> ลบUserทิ้งถาวร
        if($conn->query("DELETE FROM users WHERE user_id=$uid")){
            $_SESSION['alert_msg'] = "⛔ ลบลูกค้าถาวรเรียบร้อยแล้ว (ต้องสมัครใหม่)";
            $_SESSION['alert_type'] = "danger";
        } else {
            $_SESSION['alert_msg'] = "❌ Error: " . $conn->error;
            $_SESSION['alert_type'] = "danger";
        }
    }
    
    header("Location: admin_panel.php?page=customers");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'orders';
$search = isset($_GET['search']) ? $_GET['search'] : '';
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
        body { font-family: 'Sarabun', sans-serif; background-color: #FFF8E7; }
        .sidebar { background-color: #263238; min-height: 100vh; color: white; }
        .nav-link { color: #cfd8dc; margin-bottom: 5px; border-radius: 5px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: #FF6D00; color: white; padding-left: 20px; }
        .btn-add { background-color: #2E7D32; color: white; border: none; font-weight: bold; }
        .btn-add:hover { background-color: #1B5E20; color: white; }
        .card { border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-3">
                <h5 class="text-warning text-center py-3 border-bottom border-secondary">
                    <i class="fas fa-drumstick-bite"></i> Admin Menu
                </h5>
                <nav class="nav flex-column mt-3">
                    <a href="admin_panel.php?page=orders" class="nav-link <?php echo $page=='orders'?'active':''; ?>"><i class="fas fa-box me-2"></i> ออเดอร์</a>
                    <a href="admin_panel.php?page=products" class="nav-link <?php echo $page=='products'?'active':''; ?>"><i class="fas fa-utensils me-2"></i> สินค้า</a>
                    <a href="admin_panel.php?page=categories" class="nav-link <?php echo $page=='categories'?'active':''; ?>"><i class="fas fa-list me-2"></i> หมวดหมู่</a>
                    <a href="admin_panel.php?page=customers" class="nav-link <?php echo $page=='customers'?'active':''; ?>"><i class="fas fa-users me-2"></i> ลูกค้า</a>
                </nav>
                <div class="mt-4">
                    <a href="add_product.php" class="btn btn-add w-100 py-2 shadow-sm">+ เพิ่มสินค้าใหม่</a>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                
                <?php if($page == 'orders'): ?>
                    <h3 class="text-dark fw-bold mb-3">📦 รายการสั่งซื้อล่าสุด</h3>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr><th>#ID</th><th>ลูกค้า</th><th>ยอดรวม</th><th>สถานะ</th><th>เปลี่ยนสถานะ</th><th>รายละเอียด</th></tr>
                                </thead>
                                <tbody>
                                <?php
                                $res = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_id DESC");
                                while($row = $res->fetch_assoc()){
                                    $st_color = 'secondary';
                                    if($row['status']=='pending') $st_color='warning text-dark';
                                    if($row['status']=='cooking') $st_color='info text-dark';
                                    if($row['status']=='completed') $st_color='success';
                                    if($row['status']=='cancelled') $st_color='danger';

                                    echo "<tr>
                                        <td class='fw-bold'>#{$row['order_id']}</td>
                                        <td>{$row['username']}</td>
                                        <td class='fw-bold text-danger'>฿".number_format($row['total_amount'])."</td>
                                        <td><span class='badge bg-$st_color'>".strtoupper($row['status'])."</span></td>
                                        <td>
                                            <form method='post' class='d-flex align-items-center gap-2'>
                                                <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                                <select name='status' class='form-select form-select-sm' style='width:130px;'>
                                                    <option value='pending' ".($row['status']=='pending'?'selected':'').">Pending</option>
                                                    <option value='cooking' ".($row['status']=='cooking'?'selected':'').">Cooking</option>
                                                    <option value='completed' ".($row['status']=='completed'?'selected':'').">Completed</option>
                                                    <option value='cancelled' ".($row['status']=='cancelled'?'selected':'').">Cancelled</option>
                                                </select>
                                                <button type='submit' name='update_status' class='btn btn-sm btn-primary'><i class='fas fa-save'></i></button>
                                            </form>
                                        </td>
                                        <td><a href='admin_order_detail.php?order_id={$row['order_id']}' class='btn btn-sm btn-outline-secondary'>ดูบิล</a></td>
                                    </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif($page == 'products'): ?>
                   <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="text-dark fw-bold">🍗 จัดการสินค้า</h3>
                        <form class="d-flex" method="GET">
                            <input type="hidden" name="page" value="products">
                            <input class="form-control me-2" type="search" name="search" placeholder="ค้นหาสินค้า..." value="<?php echo $search; ?>">
                            <button class="btn btn-primary" type="submit">ค้นหา</button>
                        </form>
                    </div>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0 align-middle">
                                <thead class="table-light"><tr><th>รูป</th><th>ชื่อสินค้า</th><th>ราคา</th><th>จัดการ</th></tr></thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM products WHERE product_name LIKE '%$search%' ORDER BY product_id DESC";
                                $res = $conn->query($sql);
                                while($row = $res->fetch_assoc()){
                                    $img_src = !empty($row['image_file']) ? "img/".$row['image_file'] : "https://via.placeholder.com/50";
                                    echo "<tr>
                                        <td class='text-center'><img src='$img_src' width='60' height='60' class='rounded border' style='object-fit:cover;'></td>
                                        <td>{$row['product_name']}</td>
                                        <td class='fw-bold text-success'>{$row['price']}</td>
                                        <td>
                                            <a href='edit_product.php?id={$row['product_id']}' class='btn btn-warning btn-sm'>แก้ไข</a>
                                            <a href='admin_panel.php?delete_product={$row['product_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"ยืนยันลบสินค้านี้?\")'>ลบ</a>
                                        </td>
                                    </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif($page == 'categories'): ?>
                    <h3 class="text-dark fw-bold mb-3">📂 จัดการหมวดหมู่</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" class="d-flex gap-2 mb-4">
                                <input type="text" name="cat_name" class="form-control" placeholder="ชื่อประเภทใหม่..." required>
                                <button type="submit" name="add_category" class="btn btn-success px-4">เพิ่ม</button>
                            </form>
                            <ul class="list-group shadow-sm">
                                <?php
                                $res = $conn->query("SELECT * FROM categories");
                                while($row = $res->fetch_assoc()){
                                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                        {$row['category_name']}
                                        <a href='admin_panel.php?delete_cat={$row['category_id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"ยืนยันลบ?\")'>ลบ</a>
                                    </li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>

                <?php elseif($page == 'customers'): ?>
                    <h3 class="text-dark fw-bold mb-3">👥 รายชื่อลูกค้า</h3>
                    <div class="alert alert-info border-0 shadow-sm" style="background-color: #E3F2FD; color: #0D47A1;">
                        <small>
                            <i class="fas fa-eye-slash"></i> <strong>ปุ่มซ่อน:</strong> ลูกค้าหายจากหน้านี้ แต่ยัง Login ได้ (จะกลับมาแสดงเมื่อเขา Login ใหม่)<br>
                            <i class="fas fa-trash-alt"></i> <strong>ปุ่มลบถาวร:</strong> ลบข้อมูลทิ้งทั้งหมด ลูกค้าต้องสมัครใหม่ (ลบไม่ได้ถ้ามีออเดอร์ค้าง)
                        </small>
                    </div>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-dark"><tr><th>User</th><th>ชื่อ-สกุล</th><th>เบอร์โทร</th><th>จัดการ</th></tr></thead>
                                <tbody>
                                <?php
                                // แสดงเฉพาะคนที่ is_visible = 1 (หรือยังไม่มีค่านี้)
                                $res = $conn->query("SELECT * FROM users WHERE role='customer' AND (is_visible IS NULL OR is_visible = 1)");
                                while($row = $res->fetch_assoc()){
                                    echo "<tr>
                                        <td>{$row['username']}</td>
                                        <td>{$row['fullname']}</td>
                                        <td>{$row['phone']}</td>
                                        <td>
                                            <a href='admin_panel.php?hide_user={$row['user_id']}' class='btn btn-secondary btn-sm px-3' onclick='return confirm(\"ซ่อนลูกค้ารายนี้จากหน้า Admin ชั่วคราว?\")'>
                                                <i class='fas fa-eye-slash'></i> ซ่อน
                                            </a>
                                            
                                            <a href='admin_panel.php?delete_user={$row['user_id']}' class='btn btn-danger btn-sm px-3' onclick='return confirm(\"⚠️ ยืนยันลบถาวร? ลูกค้าต้องสมัครใหม่นะ\")'>
                                                <i class='fas fa-trash-alt'></i> ลบถาวร
                                            </a>
                                        </td>
                                    </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
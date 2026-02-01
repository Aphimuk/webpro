<?php
session_start();
require_once ('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied"; exit();
}

// --- Logic จัดการสินค้า (Delete) ---
if (isset($_GET['delete_product'])) {
    $pid = $_GET['delete_product'];
    
    // ลบไฟล์รูปภาพจากโฟลเดอร์ img/
    $res_imgs = $conn->query("SELECT image_file FROM product_images WHERE product_id=$pid");
    while($r = $res_imgs->fetch_assoc()){
        @unlink("img/" . $r['image_file']);
    }
    
    $conn->query("DELETE FROM product_images WHERE product_id=$pid");
    $conn->query("DELETE FROM products WHERE product_id=$pid");
    
    header("Location: admin_panel.php?page=products");
}

// --- Logic จัดการประเภทสินค้า ---
if (isset($_POST['add_category'])) {
    $c_name = $_POST['cat_name'];
    $conn->query("INSERT INTO categories (category_name) VALUES ('$c_name')");
    header("Location: admin_panel.php?page=categories");
}
if (isset($_GET['delete_cat'])) {
    $cid = $_GET['delete_cat'];
    $conn->query("DELETE FROM categories WHERE category_id=$cid"); 
    header("Location: admin_panel.php?page=categories");
}

// --- Logic อัปเดตสถานะออเดอร์ ---
if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $st = $_POST['status'];
    $conn->query("UPDATE orders SET status='$st' WHERE order_id=$oid");
    header("Location: admin_panel.php?page=orders");
}

// --- Logic ลบลูกค้า ---
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    $conn->query("DELETE FROM users WHERE user_id=$uid");
    header("Location: admin_panel.php?page=customers");
}

$page = isset($_GET['page']) ? $_GET['page'] : 'orders';
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - บักปึก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 bg-dark text-white p-3" style="min-height: 100vh;">
                <h5 class="text-warning">Admin Menu</h5>
                <hr>
                <a href="admin_panel.php?page=orders" class="nav-link text-white mb-2">📦 จัดการออเดอร์</a>
                <a href="admin_panel.php?page=products" class="nav-link text-white mb-2">🍔 จัดการสินค้า</a>
                <a href="admin_panel.php?page=categories" class="nav-link text-white mb-2">📂 จัดการประเภทสินค้า</a>
                <a href="admin_panel.php?page=customers" class="nav-link text-white mb-2">👥 จัดการลูกค้า</a>
                <hr>
                <a href="add_product.php" class="btn btn-success w-100">+ เพิ่มสินค้าใหม่</a>
            </div>
            
            <div class="col-md-10 p-4">
                
                <?php if($page == 'orders'): ?>
                    <h3>รายการสั่งซื้อ (Orders)</h3>
                    <table class="table table-hover shadow-sm">
                        <thead class="table-light"><tr><th>#ID</th><th>ลูกค้า</th><th>ยอดรวม</th><th>สถานะ</th><th>เปลี่ยนสถานะ</th><th>รายละเอียด</th></tr></thead>
                        <?php
                        $res = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_id DESC");
                        while($row = $res->fetch_assoc()){
                            echo "<tr>
                                <td>{$row['order_id']}</td>
                                <td>{$row['username']}</td>
                                <td>".number_format($row['total_amount'])."</td>
                                <td><span class='badge bg-info'>{$row['status']}</span></td>
                                <td>
                                    <form method='post' class='d-flex'>
                                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                        <select name='status' class='form-select form-select-sm me-1'>
                                            <option value='pending' ".($row['status']=='pending'?'selected':'').">Pending</option>
                                            <option value='cooking' ".($row['status']=='cooking'?'selected':'').">Cooking</option>
                                            <option value='completed' ".($row['status']=='completed'?'selected':'').">Completed</option>
                                            <option value='cancelled' ".($row['status']=='cancelled'?'selected':'').">Cancelled</option>
                                        </select>
                                        <button type='submit' name='update_status' class='btn btn-sm btn-primary'>✓</button>
                                    </form>
                                </td>
                                <td><a href='admin_order_detail.php?order_id={$row['order_id']}' class='btn btn-sm btn-outline-dark'>ดูรายละเอียด</a></td>
                            </tr>";
                        }
                        ?>
                    </table>

                <?php elseif($page == 'products'): ?>
                    <h3>จัดการสินค้า</h3>
                    <form class="d-flex mb-3" method="GET">
                        <input type="hidden" name="page" value="products">
                        <input class="form-control me-2" type="search" name="search" placeholder="ค้นหาสินค้า..." value="<?php echo $search; ?>">
                        <button class="btn btn-outline-success" type="submit">ค้นหา</button>
                    </form>

                    <table class="table table-bordered">
                        <thead><tr><th>ID</th><th>รูป (ปก)</th><th>ชื่อสินค้า</th><th>ราคา</th><th>จัดการ</th></tr></thead>
                        <?php
                        $sql = "SELECT * FROM products WHERE product_name LIKE '%$search%' ORDER BY product_id DESC";
                        $res = $conn->query($sql);
                        while($row = $res->fetch_assoc()){
                            // --- แก้ไขตรงนี้: ชี้ไปที่ img/ ---
                            $img_src = !empty($row['image_file']) ? "img/".$row['image_file'] : "https://via.placeholder.com/50";
                            
                            echo "<tr>
                                <td>{$row['product_id']}</td>
                                <td><img src='$img_src' width='50' height='50' style='object-fit:cover;'></td>
                                <td>{$row['product_name']}</td>
                                <td>{$row['price']}</td>
                                <td>
                                    <a href='edit_product.php?id={$row['product_id']}' class='btn btn-warning btn-sm'>แก้ไข</a>
                                    <a href='admin_panel.php?delete_product={$row['product_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"ยืนยันลบสินค้านี้?\")'>ลบ</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </table>
                
                <?php elseif($page == 'categories'): ?>
                    <h3>จัดการประเภทสินค้า</h3>
                    <form method="post" class="row g-3 mb-4">
                        <div class="col-auto">
                            <input type="text" name="cat_name" class="form-control" placeholder="ชื่อประเภทใหม่" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_category" class="btn btn-primary">เพิ่มประเภท</button>
                        </div>
                    </form>
                    <table class="table w-50">
                        <thead><tr><th>ID</th><th>ชื่อประเภท</th><th>จัดการ</th></tr></thead>
                        <?php
                        $res = $conn->query("SELECT * FROM categories");
                        while($row = $res->fetch_assoc()){
                            echo "<tr>
                                <td>{$row['category_id']}</td>
                                <td>{$row['category_name']}</td>
                                <td>
                                    <a href='admin_panel.php?delete_cat={$row['category_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"ยืนยันลบ?\")'>ลบ</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </table>

                <?php elseif($page == 'customers'): ?>
                   <h3>จัดการลูกค้า</h3>
                   <table class="table">
                        <thead><tr><th>ID</th><th>Username</th><th>ชื่อจริง</th><th>เบอร์โทร</th><th>จัดการ</th></tr></thead>
                        <?php
                        $res = $conn->query("SELECT * FROM users WHERE role='customer'");
                        while($row = $res->fetch_assoc()){
                            echo "<tr>
                                <td>{$row['user_id']}</td>
                                <td>{$row['username']}</td>
                                <td>{$row['fullname']}</td>
                                <td>{$row['phone']}</td>
                                <td>
                                    <a href='admin_panel.php?delete_user={$row['user_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"ยืนยันลบลูกค้านี้?\")'>ลบ</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
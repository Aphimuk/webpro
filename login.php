<?php
session_start();
require_once ('connect.php');

// --- PHP Logic ---
$old_fullname = "";
$old_username = "";
$old_address = "";  
$old_phone = "";    

$register_error = ""; 
$register_success = ""; 
$login_error = "";    

$is_register_active = false;

if (isset($_POST['register'])) {
    $is_register_active = true;
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password']; 
    $name = $conn->real_escape_string($_POST['fullname']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = 'customer'; 

    $old_fullname = $name;
    $old_username = $user;
    $old_address = $address;
    $old_phone = $phone;

    if(empty($user) || empty($pass) || empty($name) || empty($phone)){
        $register_error = "⚠️ กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        $check_sql = "SELECT username FROM users WHERE username = '$user'";
        if ($conn->query($check_sql)->num_rows > 0) {
            $register_error = "⚠️ Username '$user' มีผู้ใช้งานแล้ว!";
        } else {
            // เพิ่ม is_visible = 1 (แสดงผล) โดย Default
            $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, fullname, address, phone, role, is_visible) 
                    VALUES ('$user', '$password_hashed', '$name', '$address', '$phone', '$role', 1)";
            
            if($conn->query($sql)){ 
                $register_success = "✅ สมัครสมาชิกสำเร็จ!";
                $is_register_active = false; 
                $old_fullname = ""; $old_address = ""; $old_phone = "";
            } else {
                $register_error = "Error: " . $conn->error;
            }
        }
    }
}

if (isset($_POST['login'])) {
    $is_register_active = false;
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$user'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['alert_msg'] = "ยินดีต้อนรับคุณ {$row['fullname']} !";
            $_SESSION['alert_type'] = "success";

            // --- [จุดสำคัญ] ถ้าล็อกอินสำเร็จ ให้ตั้งค่าเป็น "แสดงตัว" (is_visible = 1) ---
            $uid = $row['user_id'];
            $conn->query("UPDATE users SET is_visible = 1 WHERE user_id = $uid");

            if($row['role'] == 'admin'){
                header("Location: admin_panel.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $login_error = "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $login_error = "❌ ไม่พบชื่อผู้ใช้นี้";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ร้านไก่ทอดบักปึก</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    ```
*(หมายเหตุ: เพื่อไม่ให้โค้ดยาวเกินไป ให้คุณเอา PHP ข้างบนนี้ไปแปะทับส่วนบนของ `login.php` ไฟล์ล่าสุดของคุณครับ)*

---

### ขั้นตอนที่ 3: แก้ไฟล์ `admin_panel.php` (เพิ่มปุ่มซ่อน และปุ่มลบถาวร)

ไฟล์นี้จะมีการเปลี่ยนแปลง 3 จุด:
1.  เพิ่ม Logic **"ซ่อนลูกค้า"** (`hide_user`)
2.  ปรับ Query การแสดงผลให้โชว์เฉพาะคนที่ไม่ถูกซ่อน (`WHERE is_visible = 1`)
3.  เพิ่มปุ่มกด 2 ปุ่มในตาราง

**ไฟล์: `admin_panel.php` (ฉบับสมบูรณ์ พร้อม 2 ปุ่ม)**

```php
<?php
session_start();
require_once ('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied"; exit();
}

// --------------------------------------------------------
// 1. Logic ลบสินค้า
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
    // แค่อัปเดตสถานะเป็น 0 (ซ่อน) แต่ไม่ลบข้อมูล
    $conn->query("UPDATE users SET is_visible = 0 WHERE user_id = $uid");
    
    $_SESSION['alert_msg'] = "👻 ซ่อนรายชื่อลูกค้าออกจากหน้า Admin แล้ว (ลูกค้ายัง Login ได้)";
    $_SESSION['alert_type'] = "secondary"; // สีเทา
    header("Location: admin_panel.php?page=customers");
    exit();
}

// --------------------------------------------------------
// 5. Logic ปุ่มที่ 2: ลบถาวร (Hard Delete)
// --------------------------------------------------------
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    
    // ลบประวัติที่ยกเลิกก่อน
    $get_cancelled = $conn->query("SELECT order_id FROM orders WHERE user_id=$uid AND status='cancelled'");
    $deleted_count = 0;
    while($row = $get_cancelled->fetch_assoc()){
        $oid = $row['order_id'];
        $conn->query("DELETE FROM order_details WHERE order_id=$oid");
        $conn->query("DELETE FROM orders WHERE order_id=$oid");
        $deleted_count++;
    }

    // เช็คออเดอร์ค้าง
    $check_remaining = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id=$uid");
    $remaining = $check_remaining->fetch_assoc()['count'];

    if ($remaining > 0) {
        $_SESSION['alert_msg'] = "⚠️ ลบเฉพาะประวัติที่ 'ยกเลิก' ให้แล้ว แต่ยังลบลูกค้าถาวรไม่ได้ เพราะมีออเดอร์ค้างอยู่";
        $_SESSION['alert_type'] = "warning";
    } else {
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
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i> <strong>ปุ่มซ่อน:</strong> ลูกค้าหายจากหน้านี้ แต่ยัง Login ได้ (รายชื่อจะกลับมาเมื่อเขา Login)<br>
                            <i class="fas fa-exclamation-circle"></i> <strong>ปุ่มลบถาวร:</strong> ลบข้อมูลทิ้งทั้งหมด ลูกค้าต้องสมัครใหม่
                        </small>
                    </div>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-dark"><tr><th>User</th><th>ชื่อ-สกุล</th><th>เบอร์โทร</th><th>จัดการ</th></tr></thead>
                                <tbody>
                                <?php
                                // [สำคัญ] แสดงเฉพาะคนที่ is_visible = 1 (หรือ NULL เผื่อข้อมูลเก่า)
                                $res = $conn->query("SELECT * FROM users WHERE role='customer' AND (is_visible IS NULL OR is_visible = 1)");
                                while($row = $res->fetch_assoc()){
                                    echo "<tr>
                                        <td>{$row['username']}</td>
                                        <td>{$row['fullname']}</td>
                                        <td>{$row['phone']}</td>
                                        <td>
                                            <a href='admin_panel.php?hide_user={$row['user_id']}' class='btn btn-secondary btn-sm px-3' onclick='return confirm(\"ซ่อนลูกค้ารายนี้จากหน้า Admin?\")'>
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
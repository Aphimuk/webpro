<?php
session_start();
require_once ('connect.php');

$search_value = ""; 
$where_sql = "";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $conn->real_escape_string($_GET['search']);
    $where_sql = "WHERE product_name LIKE '%$search%'";
    $search_value = htmlspecialchars($_GET['search']); 
} elseif (isset($_GET['category_id']) && $_GET['category_id'] != "") {
    $cat_id = $conn->real_escape_string($_GET['category_id']);
    $where_sql = "WHERE category_id = '$cat_id'";
}

$sql_products = "SELECT * FROM products $where_sql ORDER BY product_id DESC";
$result_products = $conn->query($sql_products);

$sql_cats = "SELECT * FROM categories";
$result_cats = $conn->query($sql_cats);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บักปึก - หน้าร้าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-img-top { height: 200px; object-fit: cover; }
        .sidebar { background-color: #f8f9fa; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">บักปึก</a>
            
            <form class="d-flex mx-auto" action="index.php" method="GET">
                <input class="form-control me-2" type="search" name="search" 
                       value="<?php echo $search_value; ?>" 
                       placeholder="ค้นหาเมนูอาหาร..." aria-label="Search">
                <button class="btn btn-outline-warning" type="submit">ค้นหา</button>
            </form>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="my_orders.php">บัญชีของฉัน</a>
                    <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">เข้าสู่ระบบ</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <?php if(isset($_SESSION['alert_msg'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert_type']; ?> alert-dismissible fade show text-center fs-5 fw-bold" role="alert">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    echo $_SESSION['alert_msg']; 
                    unset($_SESSION['alert_msg']);
                    unset($_SESSION['alert_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <div class="row">
            <div class="col-md-3">
                <div class="sidebar shadow-sm">
                    <h4>ประเภทอาหาร</h4>
                    <div class="list-group">
                        <a href="index.php" class="list-group-item list-group-item-action">ทั้งหมด</a>
                        
                        <?php while($cat = $result_cats->fetch_assoc()): ?>
                            <?php 
                                $active_class = "";
                                if(isset($_GET['category_id']) && $_GET['category_id'] == $cat['category_id']){
                                    $active_class = "active";
                                }
                            ?>
                            <a href="index.php?category_id=<?php echo $cat['category_id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo $active_class; ?>">
                                <?php echo $cat['category_name']; ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <h3>รายการอาหารแนะนำ</h3>
                <hr>
                <div class="row">
                    <?php if ($result_products->num_rows > 0): ?>
                        <?php while($row = $result_products->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <?php 
                                        // ตรวจสอบรูปภาพ
                                        $img_show = !empty($row['image_file']) ? "uploads/".$row['image_file'] : "https://via.placeholder.com/300x200?text=No+Image";
                                    ?>
                                    <img src="<?php echo $img_show; ?>" class="card-img-top" alt="รูปอาหาร">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $row['product_name']; ?></h5>
                                        <p class="card-text text-danger fw-bold">฿<?php echo number_format($row['price'], 2); ?></p>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-info btn-sm text-white">ดูรายละเอียด</a>
                                            
                                            <a href="cart_action.php?action=add&id=<?php echo $row['product_id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-cart-plus"></i> สั่งซื้อ
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <h3><i class="fas fa-search"></i> ไม่พบรายการอาหารที่ค้นหา</h3>
                            <p>ลองค้นหาด้วยคำอื่น หรือเลือกดูเมนูทั้งหมด</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
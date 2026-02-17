<?php
session_start();
require_once ('connect.php');

$search_value = ""; 
$where_sql = "";
$page_title = "🍗 รายการอาหารแนะนำ"; 

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $conn->real_escape_string($_GET['search']);
    $where_sql = "WHERE product_name LIKE '%$search%'";
    $search_value = htmlspecialchars($_GET['search']); 
    
    
    $page_title = "🔍 ผลการค้นหา: " . $search_value;

} elseif (isset($_GET['category_id']) && $_GET['category_id'] != "") {
    $cat_id = $conn->real_escape_string($_GET['category_id']);
    $where_sql = "WHERE category_id = '$cat_id'";

    
    $sql_cat_name = "SELECT category_name FROM categories WHERE category_id = '$cat_id'";
    $res_cat_name = $conn->query($sql_cat_name);
    if ($res_cat_name->num_rows > 0) {
        $row_cat = $res_cat_name->fetch_assoc();
        $page_title = "🍽️ หมวดหมู่: " . $row_cat['category_name']; 
    }
}

$sql_products = "SELECT * FROM products $where_sql ORDER BY product_id DESC";
$result_products = $conn->query($sql_products);
$result_cats = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บักปึก ไก่ทอด - เมนูความอร่อย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #FFF8E1; } 
        .card-img-top { height: 220px; object-fit: cover; border-bottom: 3px solid #FF6D00; }
        .sidebar { background-color: #FFFFFF; padding: 20px; border-radius: 15px; border: 1px solid #FFE0B2; }
        .card { border: none; border-radius: 15px; transition: transform 0.2s; background: #fff; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .btn-orange { background-color: #FF6D00; color: white; border-radius: 20px; }
        .btn-orange:hover { background-color: #E65100; color: white; }
        .list-group-item.active { background-color: #FF6D00; border-color: #FF6D00; }
        .price-tag { font-size: 1.2rem; color: #D84315; font-weight: bold; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <form class="d-flex shadow-sm rounded-pill overflow-hidden bg-white" action="index.php" method="GET">
                    <input class="form-control border-0 px-4 py-3" type="search" name="search" 
                           value="<?php echo $search_value; ?>" 
                           placeholder="วันนี้กินไก่ทอดรสอะไรดี?..." aria-label="Search">
                    <button class="btn btn-warning px-4 fw-bold" type="submit">ค้นหา</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="sidebar shadow-sm">
                    <h5 class="text-secondary fw-bold"><i class="fas fa-utensils"></i> ประเภทอาหาร</h5>
                    <hr class="text-warning">
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action rounded mb-1 <?php echo (!isset($_GET['category_id']) && !isset($_GET['search'])) ? 'active' : ''; ?>">ทั้งหมด</a>
                        <?php while($cat = $result_cats->fetch_assoc()): ?>
                            <a href="index.php?category_id=<?php echo $cat['category_id']; ?>" 
                               class="list-group-item list-group-item-action rounded mb-1 <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['category_id']) ? 'active' : ''; ?>">
                                <?php echo $cat['category_name']; ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <h3 class="fw-bold text-dark mb-3"><?php echo $page_title; ?></h3>
                
                <div class="row">
                    <?php if ($result_products->num_rows > 0): ?>
                        <?php while($row = $result_products->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <?php 
                                        
                                        $img_show = !empty($row['image_file']) ? "img/".$row['image_file'] : "https://via.placeholder.com/300x200?text=No+Image";
                                    ?>
                                    <img src="<?php echo $img_show; ?>" class="card-img-top" alt="รูปอาหาร">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold text-dark"><?php echo $row['product_name']; ?></h5>
                                        <div class="mt-auto">
                                            <p class="price-tag mb-2">฿<?php echo number_format($row['price'], 0); ?></p>
                                            <div class="d-grid gap-2">
                                                <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill">รายละเอียด</a>
                                                <a href="cart_action.php?action=add&id=<?php echo $row['product_id']; ?>" class="btn btn-orange btn-sm">
                                                    <i class="fas fa-cart-plus"></i> สั่งเลย
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning text-center rounded-4 p-5">
                                <h3><i class="fas fa-search"></i> ไม่พบเมนูที่ค้นหา</h3>
                                <p>ลองค้นหาคำอื่น หรือดูเมนูทั้งหมดของเรา</p>
                                <a href="index.php" class="btn btn-outline-dark mt-3">ดูเมนูทั้งหมด</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
require_once ('connect.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // ดึงข้อมูลสินค้า
    $sql = "SELECT p.*, c.category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.product_id = $id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

    // ดึงรูปภาพทั้งหมดของสินค้านี้
    $sql_img = "SELECT * FROM product_images WHERE product_id = $id";
    $result_img = $conn->query($sql_img);
    
    // เก็บรูปใส่ Array
    $images = [];
    while($row_img = $result_img->fetch_assoc()){
        $images[] = $row_img['image_file'];
    }
    
    // ถ้าไม่มีรูปในตารางใหม่ ให้ใช้รูปจากตาราง products เดิม
    if(count($images) == 0 && !empty($product['image_file'])){
        $images[] = $product['image_file'];
    }
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product['product_name']; ?> - รายละเอียด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .carousel-item img {
            height: 400px;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container mt-5 mb-5">
        <a href="index.php" class="btn btn-secondary mb-3">&larr; กลับหน้าหลัก</a>
        
        <div class="card shadow">
            <div class="row g-0">
                <div class="col-md-6">
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="hover">
                        
                        <div class="carousel-indicators">
                            <?php foreach($images as $index => $img): ?>
                                <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                        class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="true"></button>
                            <?php endforeach; ?>
                        </div>

                        <div class="carousel-inner">
                            <?php if(count($images) > 0): ?>
                                <?php foreach($images as $index => $img): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="img/<?php echo $img; ?>" class="d-block w-100" alt="Product Image">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active">
                                    <img src="https://via.placeholder.com/600x400?text=No+Image" class="d-block w-100" alt="No Image">
                                </div>
                            <?php endif; ?>
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    </div>

                <div class="col-md-6">
                    <div class="card-body">
                        <h5 class="text-muted"><?php echo $product['category_name']; ?></h5>
                        <h2 class="card-title"><?php echo $product['product_name']; ?></h2>
                        <h3 class="text-danger my-3">ราคา: <?php echo number_format($product['price'], 2); ?> บาท</h3>
                        
                        <p class="card-text">
                            <strong>รายละเอียด:</strong><br>
                            <?php echo nl2br($product['description']); ?>
                        </p>

                        <hr>
                        
                        <form action="cart_action.php" method="GET">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?php echo $product['product_id']; ?>">
                            
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <label>จำนวน:</label>
                                </div>
                                <div class="col-auto">
                                    <input type="number" name="qty" value="1" min="1" class="form-control" style="width: 80px;">
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary w-100">ใส่ตะกร้าสินค้า</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
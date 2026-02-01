<?php
require_once ('connect.php');

// ตรวจสอบว่ามี ID ส่งมาหรือไม่
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT p.*, c.category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.product_id = $id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
} else {
    // ถ้าไม่มี ID ให้เด้งกลับหน้าแรก
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
</head>
<body class="bg-light">

    <div class="container mt-5">
        <a href="index.php" class="btn btn-secondary mb-3">&larr; กลับหน้าหลัก</a>
        
        <div class="card shadow">
            <div class="row g-0">
                <div class="col-md-6">
                    <img src="uploads/<?php echo $product['image_file']; ?>" class="img-fluid rounded-start w-100" style="height: 400px; object-fit: cover;" alt="Product Image">
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

</body>
</html>
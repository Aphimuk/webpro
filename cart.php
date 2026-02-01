<?php
session_start();
require_once ('connect.php');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h3>ตะกร้าสินค้า</h3>
        <table class="table table-bordered">
            <tr>
                <th>สินค้า</th>
                <th>ราคา</th>
                <th>จำนวน</th>
                <th>รวม</th>
                <th>ลบ</th>
            </tr>
            <?php
            $total = 0;
            if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0){
                foreach($_SESSION['cart'] as $p_id => $qty){
                    $sql = "SELECT * FROM products WHERE product_id = $p_id";
                    $query = $conn->query($sql);
                    $row = $query->fetch_assoc();
                    $sum = $row['price'] * $qty;
                    $total += $sum;
            ?>
            <tr>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo $qty; ?></td>
                <td><?php echo number_format($sum, 2); ?></td>
                <td><a href="cart_action.php?action=delete&id=<?php echo $p_id; ?>" class="btn btn-danger btn-sm">ลบ</a></td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='5' class='text-center'>ตะกร้าว่างเปล่า</td></tr>";
            }
            ?>
            <tr>
                <td colspan="3" class="text-end"><strong>ราคารวมสุทธิ</strong></td>
                <td><strong><?php echo number_format($total, 2); ?></strong></td>
                <td>บาท</td>
            </tr>
        </table>
        
        <?php if($total > 0): ?>
            <div class="text-end">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="checkout_save.php" class="btn btn-success btn-lg">ยืนยันการสั่งซื้อ</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-warning">กรุณาเข้าสู่ระบบเพื่อสั่งซื้อ</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
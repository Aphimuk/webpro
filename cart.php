<?php
session_start();
require_once ('connect.php');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าความอร่อย - บักปึก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-header text-white text-center py-3" style="background: linear-gradient(45deg, #D84315, #FF6D00);">
                <h3 class="m-0 fw-bold"><i class="fas fa-shopping-basket"></i> ตะกร้าสินค้าของคุณ</h3>
            </div>
            <div class="card-body p-4">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-center text-uppercase text-secondary">
                                <th>สินค้า</th>
                                <th>ราคา</th>
                                <th>จำนวน</th>
                                <th>รวม</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
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
                            <tr class="text-center fw-bold text-dark">
                                <td class="text-start ps-4">
                                    <div class="d-flex align-items-center">
                                        <?php $img_show = !empty($row['image_file']) ? "img/".$row['image_file'] : "https://via.placeholder.com/50"; ?>
                                        <img src="<?php echo $img_show; ?>" class="rounded-circle me-3 border border-2 border-warning" width="50" height="50" style="object-fit:cover;">
                                        <span><?php echo $row['product_name']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-6 shadow-sm">
                                        <?php echo $qty; ?>
                                    </span>
                                </td>
                                <td class="text-danger fs-5">฿<?php echo number_format($sum, 2); ?></td>
                                <td>
                                    <a href="cart_action.php?action=delete&id=<?php echo $p_id; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                } 
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted fs-5'>ยังไม่มีความอร่อยในตะกร้า <br><a href='index.php' class='btn btn-link'>ไปเลือกซื้อเลย</a></td></tr>";
                            }
                            ?>
                        </tbody>
                        <?php if($total > 0): ?>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold text-secondary fs-5 pe-4">ยอดรวมสุทธิ</td>
                                <td class="text-center fw-bold text-danger fs-3">฿<?php echo number_format($total, 2); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index.php" class="btn btn-secondary rounded-pill px-4 fw-bold">
                        <i class="fas fa-arrow-left"></i> เลือกซื้อเพิ่ม
                    </a>
                    
                    <?php if($total > 0): ?>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="checkout_save.php" class="btn btn-success btn-lg rounded-pill px-5 shadow fw-bold" style="background-color: #2E7D32; border:none;">
                                ยืนยันการสั่งซื้อ <i class="fas fa-check-circle"></i>
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-warning btn-lg rounded-pill px-5 shadow fw-bold text-dark">
                                เข้าสู่ระบบเพื่อสั่งซื้อ <i class="fas fa-sign-in-alt"></i>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"> บักปึก </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item"><a class="nav-link text-warning" href="admin_panel.php">จัดการหลังบ้าน (Admin)</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> ตะกร้า 
                        <span class="badge bg-danger"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="my_orders.php">ข้อมูล & ประวัติสั่งซื้อ</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-light" href="logout.php">ออกจากระบบ</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">เข้าสู่ระบบ / สมัครสมาชิก</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
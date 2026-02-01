<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: linear-gradient(to right, #FF416C, #FF4B2B);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-drumstick-bite"></i> บักปึก ไก่ทอด
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link text-white" href="index.php">หน้าแรก</a></li>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item"><a class="nav-link text-warning fw-bold" href="admin_panel.php">★ จัดการร้าน (Admin)</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-white position-relative" href="cart.php">
                        <i class="fas fa-shopping-basket"></i> ตะกร้า 
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link text-white" href="my_orders.php">สวัสดี, <?php echo $_SESSION['username']; ?></a></li>
                    <li class="nav-item"><a class="nav-link btn btn-sm btn-light text-danger ms-2 fw-bold" href="logout.php" style="border-radius: 20px; padding-left:15px; padding-right:15px;">ออกจากระบบ</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link text-white" href="login.php">เข้าสู่ระบบ</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if(isset($_SESSION['alert_msg'])): ?>
    <div class="container mt-3">
        <div class="alert alert-<?php echo $_SESSION['alert_type']; ?> alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
            <i class="fas fa-bell"></i> 
            <strong>แจ้งเตือน:</strong> <?php echo $_SESSION['alert_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php 
        unset($_SESSION['alert_msg']);
        unset($_SESSION['alert_type']);
    ?>
<?php endif; ?>
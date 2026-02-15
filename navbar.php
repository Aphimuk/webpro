<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap');

    body {
        font-family: 'Sarabun', sans-serif;
        background-color: #FFF8E7; 
        color: #3E2723; 
    }

    
    .navbar-custom {
        background: linear-gradient(135deg, #C62828 0%, #EF6C00 100%);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .navbar-brand { font-weight: 800; letter-spacing: 1px; font-size: 1.5rem; }
    .nav-link { font-weight: 500; }
    
    
    .card {
        border: none;
        border-radius: 15px;
        background-color: #FFFFFF;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .card:hover { transform: translateY(-3px); }

    
    .btn-primary, .btn-success {
        background-color: #EF6C00; 
        border: none;
        font-weight: bold;
        border-radius: 50px;
        padding: 8px 20px;
    }
    .btn-primary:hover, .btn-success:hover {
        background-color: #D84315; 
    }

    
    h1, h2, h3, h4, h5, h6 {
        color: #B71C1C;
        font-weight: 700;
    }

    
    .custom-alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        font-weight: 500;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
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
                            <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-warning text-dark shadow-sm">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link text-white" href="my_orders.php">คุณ <?php echo $_SESSION['username']; ?></a></li>
                    <li class="nav-item ms-2">
                        <a class="nav-link btn btn-sm btn-light text-danger fw-bold px-3 rounded-pill" href="logout.php" style="background: white;">ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link text-white" href="login.php">เข้าสู่ระบบ</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if(isset($_SESSION['alert_msg'])): ?>
    <div class="container">
        <div class="alert alert-<?php echo $_SESSION['alert_type']; ?> custom-alert alert-dismissible fade show d-flex align-items-center" role="alert">
            <div class="fs-4 me-3">
                <?php if($_SESSION['alert_type'] == 'success') echo '<i class="fas fa-check-circle"></i>'; ?>
                <?php if($_SESSION['alert_type'] == 'danger') echo '<i class="fas fa-times-circle"></i>'; ?>
                <?php if($_SESSION['alert_type'] == 'warning') echo '<i class="fas fa-exclamation-triangle"></i>'; ?>
            </div>
            <div>
                <strong>แจ้งเตือน:</strong> <?php echo $_SESSION['alert_msg']; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php 
        unset($_SESSION['alert_msg']);
        unset($_SESSION['alert_type']);
    ?>
<?php endif; ?>
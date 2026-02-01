<?php
require_once ('connect.php');


// 1. รับค่า ID ที่จะแก้ไข
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE product_id = $id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
}

// 2. บันทึกข้อมูลเมื่อกดปุ่ม Update
if (isset($_POST['update'])) {
    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    $desc = $_POST['description'];

    // SQL Update พื้นฐาน
    $sql = "UPDATE products SET product_name='$name', price='$price', category_id='$cat_id', description='$desc' WHERE product_id=$id";

    // ถ้ามีการอัปโหลดรูปใหม่
    if (!empty($_FILES['product_image']['name'])) {
        $image = basename($_FILES['product_image']['name']);
        move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/$image");
        $sql = "UPDATE products SET product_name='$name', price='$price', category_id='$cat_id', description='$desc', image_file='$image' WHERE product_id=$id";
    }

    if ($conn->query($sql)) {
        echo "<script>alert('แก้ไขข้อมูลสำเร็จ'); window.location='admin_panel.php?page=products';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-warning">แก้ไขเมนูอาหาร</div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    
                    <label>ชื่อเมนู</label>
                    <input type="text" name="product_name" class="form-control mb-2" value="<?php echo $product['product_name']; ?>" required>
                    
                    <label>หมวดหมู่</label>
                    <select name="category_id" class="form-control mb-2">
                        <?php
                        $cats = $conn->query("SELECT * FROM categories");
                        while($c = $cats->fetch_assoc()){
                            $sel = ($c['category_id'] == $product['category_id']) ? 'selected' : '';
                            echo "<option value='{$c['category_id']}' $sel>{$c['category_name']}</option>";
                        }
                        ?>
                    </select>

                    <label>ราคา</label>
                    <input type="number" name="price" class="form-control mb-2" value="<?php echo $product['price']; ?>" required>

                    <label>รายละเอียด</label>
                    <textarea name="description" class="form-control mb-2"><?php echo $product['description']; ?></textarea>

                    <label>รูปภาพ (ปล่อยว่างถ้าไม่เปลี่ยน)</label>
                    <input type="file" name="product_image" class="form-control mb-3">

                    <button type="submit" name="update" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    <a href="admin_panel.php" class="btn btn-secondary">ยกเลิก</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
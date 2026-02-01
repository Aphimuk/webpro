<?php
session_start();
require_once ('connect.php');

// 1. รับค่า ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE product_id = $id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
}

// 2. Logic ลบรูปภาพทั้งหมด (Optional)
if(isset($_POST['delete_images'])){
    $id = $_POST['product_id'];
    $res = $conn->query("SELECT image_file FROM product_images WHERE product_id=$id");
    while($r = $res->fetch_assoc()){ @unlink(__DIR__ . "/img/" . $r['image_file']); }
    
    $conn->query("DELETE FROM product_images WHERE product_id=$id");
    $conn->query("UPDATE products SET image_file='' WHERE product_id=$id");
    
    $_SESSION['alert_msg'] = "🗑️ ลบรูปภาพทั้งหมดแล้ว";
    $_SESSION['alert_type'] = "warning";
    header("Location: edit_product.php?id=$id");
    exit();
}

// 3. บันทึกข้อมูลเมื่อกด Update
if (isset($_POST['update'])) {
    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    $desc = $_POST['description'];

    $sql = "UPDATE products SET product_name='$name', price='$price', category_id='$cat_id', description='$desc' WHERE product_id=$id";
    $conn->query($sql);

    // อัปโหลดรูปเพิ่ม
    if (!empty(array_filter($_FILES['product_images']['name']))) {
        $target_dir = __DIR__ . "/img/";
        $countfiles = count($_FILES['product_images']['name']);

        for($i = 0; $i < $countfiles; $i++){
            $filename = basename($_FILES['product_images']['name'][$i]);
            if($filename != ""){
                $target_file = $target_dir . $filename;
                if(move_uploaded_file($_FILES['product_images']['tmp_name'][$i], $target_file)){
                    $conn->query("INSERT INTO product_images (product_id, image_file) VALUES ('$id', '$filename')");
                    // อัปเดตปกถ้ายังไม่มี
                    $conn->query("UPDATE products SET image_file='$filename' WHERE product_id='$id' AND (image_file IS NULL OR image_file='')");
                }
            }
        }
    }

    $_SESSION['alert_msg'] = "✅ แก้ไขสินค้า '$name' เรียบร้อยแล้ว";
    $_SESSION['alert_type'] = "success";
    header("Location: admin_panel.php?page=products");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="card shadow border-0">
            <div class="card-header text-white" style="background-color: #FF6D00;">
                <h4 class="mb-0">✏️ แก้ไขเมนูอาหาร</h4>
            </div>
            <div class="card-body p-4">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="fw-bold">ชื่อเมนู</label>
                        <input type="text" name="product_name" class="form-control" value="<?php echo $product['product_name']; ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">หมวดหมู่</label>
                            <select name="category_id" class="form-select">
                                <?php
                                $cats = $conn->query("SELECT * FROM categories");
                                while($c = $cats->fetch_assoc()){
                                    $sel = ($c['category_id'] == $product['category_id']) ? 'selected' : '';
                                    echo "<option value='{$c['category_id']}' $sel>{$c['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">ราคา (บาท)</label>
                            <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">รายละเอียด</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $product['description']; ?></textarea>
                    </div>

                    <div class="mb-4 bg-white p-3 border rounded">
                        <label class="fw-bold mb-2">จัดการรูปภาพ</label>
                        
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php
                                $imgs = $conn->query("SELECT * FROM product_images WHERE product_id=".$product['product_id']);
                                if($imgs->num_rows > 0){
                                    while($img = $imgs->fetch_assoc()){
                                        echo "<img src='img/{$img['image_file']}' class='rounded border border-secondary' style='height: 80px; width: 80px; object-fit: cover;'>";
                                    }
                                } else {
                                    echo "<span class='text-muted'>- ไม่มีรูปภาพ -</span>";
                                }
                            ?>
                        </div>
                        <button type="submit" name="delete_images" class="btn btn-outline-danger btn-sm mb-3" onclick="return confirm('ต้องการลบรูปทั้งหมดจริงหรือไม่?')">
                            <i class="fas fa-trash"></i> ลบรูปภาพทั้งหมด
                        </button>

                        <label class="d-block small text-muted">เพิ่มรูปภาพใหม่ (เลือกได้หลายรูป)</label>
                        <input type="file" name="product_images[]" class="form-control" multiple accept="image/*">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="admin_panel.php?page=products" class="btn btn-secondary px-4">ย้อนกลับ</a>
                        <button type="submit" name="update" class="btn btn-success px-5 fw-bold">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
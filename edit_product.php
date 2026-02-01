<?php
require_once ('connect.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE product_id = $id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
}

// Logic จัดการรูปภาพ (ลบรูปทั้งหมด)
if(isset($_POST['delete_images'])){
    $id = $_POST['product_id'];
    $res = $conn->query("SELECT image_file FROM product_images WHERE product_id=$id");
    while($r = $res->fetch_assoc()){
        @unlink("img/" . $r['image_file']); // ลบจากโฟลเดอร์ img
    }
    
    $conn->query("DELETE FROM product_images WHERE product_id=$id");
    $conn->query("UPDATE products SET image_file='' WHERE product_id=$id");
    
    echo "<script>alert('ลบรูปภาพทั้งหมดแล้ว'); window.location='edit_product.php?id=$id';</script>";
}

// บันทึกข้อมูลเมื่อกดปุ่ม Update
if (isset($_POST['update'])) {
    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    $desc = $_POST['description'];

    $sql = "UPDATE products SET product_name='$name', price='$price', category_id='$cat_id', description='$desc' WHERE product_id=$id";
    $conn->query($sql);

    // ถ้ามีการอัปโหลดรูปภาพเพิ่ม
    if (!empty(array_filter($_FILES['product_images']['name']))) {
        $target_dir = "img/"; // เปลี่ยนเป็น img
        $countfiles = count($_FILES['product_images']['name']);

        for($i = 0; $i < $countfiles; $i++){
            $filename = basename($_FILES['product_images']['name'][$i]);
            if($filename != ""){
                $target_file = $target_dir . $filename;
                if(move_uploaded_file($_FILES['product_images']['tmp_name'][$i], $target_file)){
                    $conn->query("INSERT INTO product_images (product_id, image_file) VALUES ('$id', '$filename')");
                    
                    $conn->query("UPDATE products SET image_file='$filename' WHERE product_id='$id' AND (image_file IS NULL OR image_file='')");
                }
            }
        }
    }

    echo "<script>alert('แก้ไขข้อมูลสำเร็จ'); window.location='admin_panel.php?page=products';</script>";
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

                    <label>เพิ่มรูปภาพใหม่ (เลือกเพิ่มได้หลายรูป):</label>
                    <input type="file" name="product_images[]" class="form-control mb-3" multiple>
                    
                    <div class="alert alert-info">
                        <strong>รูปภาพปัจจุบัน:</strong><br>
                        <?php
                            $imgs = $conn->query("SELECT * FROM product_images WHERE product_id=".$product['product_id']);
                            if($imgs->num_rows > 0){
                                while($img = $imgs->fetch_assoc()){
                                    // เปลี่ยนเป็น img/
                                    echo "<img src='img/{$img['image_file']}' class='m-1 border' style='height: 80px;'>";
                                }
                            } else {
                                echo "ไม่มีรูปภาพ";
                            }
                        ?>
                        <br>
                        <button type="submit" name="delete_images" class="btn btn-danger btn-sm mt-2" onclick="return confirm('ต้องการลบรูปภาพทั้งหมดเพื่อลงใหม่ใช่หรือไม่?')">ลบรูปภาพทั้งหมด</button>
                    </div>

                    <button type="submit" name="update" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    <a href="admin_panel.php" class="btn btn-secondary">ยกเลิก</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
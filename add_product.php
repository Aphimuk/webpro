<?php
require_once ('connect.php');

// ตรวจสอบการกดปุ่ม Submit
if (isset($_POST['submit'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    
    // 1. Insert ข้อมูลสินค้าลงไปก่อน
    $sql = "INSERT INTO products (product_name, description, price, category_id) 
            VALUES ('$name', '$desc', '$price', '$cat_id')";

    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id; // ได้ ID สินค้าล่าสุดมา
        
        // --- แก้ไขตรงนี้: เปลี่ยนโฟลเดอร์เป็น img/ ---
        $target_dir = "img/";
        // ถ้ายังไม่มีโฟลเดอร์ img ให้สร้างให้อัตโนมัติ
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $countfiles = count($_FILES['product_images']['name']);
        
        for($i = 0; $i < $countfiles; $i++){
            $filename = basename($_FILES['product_images']['name'][$i]);
            
            if($filename != ""){
                $target_file = $target_dir . $filename;
                
                if(move_uploaded_file($_FILES['product_images']['tmp_name'][$i], $target_file)){
                    $sql_img = "INSERT INTO product_images (product_id, image_file) VALUES ('$last_id', '$filename')";
                    $conn->query($sql_img);

                    if($i == 0){
                        $conn->query("UPDATE products SET image_file='$filename' WHERE product_id='$last_id'");
                    }
                }
            }
        }

        echo "<script>alert('เพิ่มเมนูอาหารและรูปภาพเรียบร้อยแล้ว!'); window.location='admin_panel.php?page=products';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มเมนูอาหาร - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>เพิ่มเมนูอาหารใหม่ (โฟลเดอร์ img)</h4>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label">ชื่อเมนูอาหาร:</label>
                    <input type="text" name="product_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียด:</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ราคา (บาท):</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">หมวดหมู่:</label>
                        <select name="category_id" class="form-control">
                            <?php
                            $sql_cat = "SELECT * FROM categories";
                            $result_cat = $conn->query($sql_cat);
                            while($row = $result_cat->fetch_assoc()) {
                                echo "<option value='".$row['category_id']."'>".$row['category_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">รูปภาพอาหาร (เลือกได้หลายรูป):</label>
                    <input type="file" name="product_images[]" class="form-control" multiple="multiple" accept="image/*" required>
                    <small class="text-muted">* กด Ctrl ค้างไว้เพื่อเลือกหลายรูป</small>
                </div>

                <button type="submit" name="submit" class="btn btn-success">บันทึกข้อมูล</button>
                <a href="admin_panel.php?page=products" class="btn btn-secondary">ย้อนกลับ</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
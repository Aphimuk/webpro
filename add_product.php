<?php
require_once ('connect.php');

if (isset($_POST['submit'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    
    // 1. Insert ข้อมูลสินค้า
    $sql = "INSERT INTO products (product_name, description, price, category_id) 
            VALUES ('$name', '$desc', '$price', '$cat_id')";

    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
        
        // --- แก้ไขจุดที่ 1: ใช้ Absolute Path ---
        $target_dir = __DIR__ . "/img/";
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $countfiles = count($_FILES['product_images']['name']);
        $success_count = 0;
        
        for($i = 0; $i < $countfiles; $i++){
            $filename = basename($_FILES['product_images']['name'][$i]);
            
            if($filename != ""){
                $target_file = $target_dir . $filename;
                
                // เช็ค Error อัปโหลด
                if($_FILES['product_images']['error'][$i] != 0){
                    echo "<script>alert('❌ ไฟล์ $filename มีปัญหา (Code: ".$_FILES['product_images']['error'][$i].")');</script>";
                    continue; 
                }
                
                // ย้ายไฟล์
                if(move_uploaded_file($_FILES['product_images']['tmp_name'][$i], $target_file)){
                    $sql_img = "INSERT INTO product_images (product_id, image_file) VALUES ('$last_id', '$filename')";
                    $conn->query($sql_img);
                    $success_count++;

                    if($success_count == 1){
                        $conn->query("UPDATE products SET image_file='$filename' WHERE product_id='$last_id'");
                    }
                } else {
                    // --- เพิ่ม Debug Path ให้เห็นชัดๆ ---
                    echo "<script>alert('❌ ย้ายไฟล์ไม่สำเร็จ! \\nPath เป้าหมายคือ: " . addslashes($target_file) . "');</script>";
                }
            }
        }

        if($success_count > 0){
            echo "<script>alert('✅ บันทึกสำเร็จ!'); window.location='admin_panel.php?page=products';</script>";
        } else {
            echo "<script>alert('⚠️ บันทึกข้อมูลแล้ว แต่รูปภาพไม่เข้าโฟลเดอร์'); window.location='admin_panel.php?page=products';</script>";
        }

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มเมนูอาหาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>เพิ่มเมนูอาหารใหม่ (Absolute Path)</h4>
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
                    <label class="form-label">รูปภาพอาหาร:</label>
                    <input type="file" name="product_images[]" class="form-control" multiple="multiple" accept="image/*" required>
                </div>

                <button type="submit" name="submit" class="btn btn-success">บันทึกข้อมูล</button>
                <a href="admin_panel.php?page=products" class="btn btn-secondary">ย้อนกลับ</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
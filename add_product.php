<?php
require_once ('connect.php');


// ตรวจสอบว่ามีการกดปุ่ม Submit หรือไม่
if (isset($_POST['submit'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    
    // การจัดการอัปโหลดรูปภาพ
    $target_dir = "uploads/"; // ต้องสร้างโฟลเดอร์ uploads ไว้ด้วย
    $file_name = basename($_FILES["product_image"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    
    // (ส่วนนี้ควรเพิ่มโค้ดตรวจสอบไฟล์ซ้ำและประเภทไฟล์เพื่อความปลอดภัย)
    move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file);

    // SQL Insert
    $sql = "INSERT INTO products (product_name, description, price, category_id, image_file) 
            VALUES ('$name', '$desc', '$price', '$cat_id', '$file_name')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('เพิ่มเมนูอาหารเรียบร้อยแล้ว!');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มเมนูอาหาร - ร้านอาหาร บักปึก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>เพิ่มเมนูอาหารใหม่ (Backend)</h4>
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
                            // ดึงข้อมูลหมวดหมู่มาแสดงใน Dropdown
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
                    <input type="file" name="product_image" class="form-control" required>
                </div>

                <button type="submit" name="submit" class="btn btn-success">บันทึกข้อมูล</button>
                <a href="#" class="btn btn-secondary">ย้อนกลับ</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
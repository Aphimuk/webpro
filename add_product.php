<?php
require_once ('connect.php');
session_start(); // ต้องมี start session เพื่อใช้ alert

if (isset($_POST['submit'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    
    $sql = "INSERT INTO products (product_name, description, price, category_id) 
            VALUES ('$name', '$desc', '$price', '$cat_id')";

    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
        $target_dir = __DIR__ . "/img/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $countfiles = count($_FILES['product_images']['name']);
        $success_count = 0;
        
        for($i = 0; $i < $countfiles; $i++){
            $filename = basename($_FILES['product_images']['name'][$i]);
            if($filename != ""){
                $target_file = $target_dir . $filename;
                if(move_uploaded_file($_FILES['product_images']['tmp_name'][$i], $target_file)){
                    $conn->query("INSERT INTO product_images (product_id, image_file) VALUES ('$last_id', '$filename')");
                    $success_count++;
                    if($success_count == 1){
                        $conn->query("UPDATE products SET image_file='$filename' WHERE product_id='$last_id'");
                    }
                }
            }
        }

        // --- ใช้ Session Alert แทน Popup ---
        $_SESSION['alert_msg'] = "✅ เพิ่มเมนู '$name' เรียบร้อยแล้ว (รูปภาพ: $success_count รูป)";
        $_SESSION['alert_type'] = "success";
        header("Location: admin_panel.php?page=products");
        exit();

    } else {
        $_SESSION['alert_msg'] = "❌ เกิดข้อผิดพลาด: " . $conn->error;
        $_SESSION['alert_type'] = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มเมนูใหม่ - บักปึก Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow border-0">
            <div class="card-header text-white" style="background-color: #FF6D00;">
                <h4 class="mb-0">🍗 เพิ่มเมนูอาหารใหม่</h4>
            </div>
            <div class="card-body p-4">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อเมนูอาหาร</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">รายละเอียด</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ราคา (บาท)</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">หมวดหมู่</label>
                            <select name="category_id" class="form-control">
                                <?php
                                $result_cat = $conn->query("SELECT * FROM categories");
                                while($row = $result_cat->fetch_assoc()) {
                                    echo "<option value='".$row['category_id']."'>".$row['category_name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">รูปภาพอาหาร (เลือกได้หลายรูป)</label>
                        <input type="file" name="product_images[]" class="form-control" multiple="multiple" accept="image/*" required>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="admin_panel.php?page=products" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" name="submit" class="btn btn-success px-4">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
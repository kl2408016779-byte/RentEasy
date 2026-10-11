<?php
require_once 'config/db.php';
session_start();

// Dapatkan peranan dari sesi sama ada user_role atau role
$current_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['role']) ? $_SESSION['role'] : null);

// Pastikan hanya Landlord atau Admin yang boleh akses
if (!isset($_SESSION['user_id']) || ($current_role !== 'landlord' && $current_role !== 'admin')) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $location = trim($_POST['location']);
    $landlord_id = $_SESSION['user_id'];
    
    // Gabungkan kemudahan (facilities) yang dipilih menjadi satu teks
    $facilities_arr = isset($_POST['facilities']) ? $_POST['facilities'] : [];
    $facilities = implode(', ', $facilities_arr);

    if (empty($title) || empty($price) || empty($location)) {
        $error = "Sila lengkapkan maklumat wajib (Tajuk, Harga, Lokasi).";
    } else {
        // Masukkan data rumah berserta kemudahan
        $stmt = $conn->prepare("INSERT INTO houses (landlord_id, title, description, price, location, facilities) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdss", $landlord_id, $title, $description, $price, $location, $facilities);
        
        if ($stmt->execute()) {
            $house_id = $stmt->insert_id;
            
            // Proses muat naik gambar ke jadual house_images
            if (isset($_FILES['house_image']) && $_FILES['house_image']['error'] === 0) {
                $file_name = $_FILES['house_image']['name'];
                $file_tmp = $_FILES['house_image']['tmp_name'];
                $new_filename = time() . '_' . preg_replace("/\s+/", "_", $file_name);
                
                // Pastikan folder uploads wujud
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                $destination = 'uploads/' . $new_filename;
                
                if (move_uploaded_file($file_tmp, $destination)) {
                    $img_stmt = $conn->prepare("INSERT INTO house_images (house_id, image_path) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $house_id, $destination);
                    $img_stmt->execute();
                }
            }
            
            $success = "Rumah berjaya diiklankan!";
        } else {
            $error = "Ralat pangkalan data: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RentEasy - Tambah Rumah Sewa</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid #dee2e6; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 22px; font-weight: bold; color: #e00b25; text-decoration: none; }
        .form-container { max-width: 600px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .checkbox-group { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 5px; }
        .btn-submit { background: #6c757d; color: white; border: none; padding: 12px 20px; width: 100%; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background: #5a6268; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🏡 RentEasy</a>
    <div>
        <a href="index.php" style="margin-right: 15px; text-decoration: none; color: #333; font-weight: bold;">Home</a>
        <?php if ($current_role === 'admin'): ?>
            <a href="admin_dashboard.php" style="color: #343a40; text-decoration: none; font-weight: bold; margin-right: 15px;">Admin Dashboard</a>
        <?php endif; ?>
        <a href="logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold;">Logout</a>
    </div>
</nav>

<div class="form-container">
    <h2>Tambah Iklan Rumah / Bilik Sewa</h2>
    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Lengkapkan maklumat kediaman khas untuk kemudahan pelajar UPTM.</p>

    <?php if (!empty($error)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= $success; ?></div>
    <?php endif; ?>

    <form action="add_house.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Tajuk Iklan (Cth: Bilik Single Dekat UPTM)</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label>Lokasi / Kawasan (Cth: Taman Shamelin Perkasa)</label>
            <input type="text" name="location" required>
        </div>

        <div class="form-group">
            <label>Harga Sebulan (RM)</label>
            <input type="number" step="0.01" name="price" required>
        </div>

        <div class="form-group">
            <label>Penerangan / Keterangan Lanjut</label>
            <textarea name="description" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label>Kemudahan Asas (Facilities):</label>
            <div class="checkbox-group">
                <label><input type="checkbox" name="facilities[]" value="WiFi"> WiFi Percuma</label>
                <label><input type="checkbox" name="facilities[]" value="Mesin Basuh"> Mesin Basuh</label>
                <label><input type="checkbox" name="facilities[]" value="Peti Sejuk"> Peti Sejuk</label>
                <label><input type="checkbox" name="facilities[]" value="Aircond"> Penghawa Dingin (Aircond)</label>
                <label><input type="checkbox" name="facilities[]" value="Bilik Air Sendiri"> Bilik Air Sendiri (Ensuite)</label>
                <label><input type="checkbox" name="facilities[]" value="Katil & Tilam"> Katil & Tilam</label>
            </div>
        </div>

        <div class="form-group">
            <label>Gambar Rumah / Bilik</label>
            <input type="file" name="house_image" accept="image/*" required>
        </div>

        <button type="submit" class="btn-submit">Muat Naik Iklan</button>
    </form>
</div>

</body>
</html>
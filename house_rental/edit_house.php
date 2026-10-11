<?php
require_once 'config/db.php';
session_start();

// Dapatkan peranan dari sesi sama ada user_role atau role
$current_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['role']) ? $_SESSION['role'] : null);

// Semak log masuk dan pastikan peranan adalah landlord atau admin
if (!isset($_SESSION['user_id']) || ($current_role !== 'landlord' && $current_role !== 'admin')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Dapatkan ID rumah dari URL
if (!isset($_GET['id'])) {
    header("Location: my_listings.php");
    exit();
}

$house_id = $_GET['id'];

// Ambil data rumah: Jika admin, boleh edit semua rumah. Jika landlord, hanya rumah sendiri.
if ($current_role === 'admin') {
    $stmt = $conn->prepare("SELECT * FROM houses WHERE id = ?");
    $stmt->bind_param("i", $house_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM houses WHERE id = ? AND landlord_id = ?");
    $stmt->bind_param("ii", $house_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_listings.php");
    exit();
}

$house = $result->fetch_assoc();

// Proses kemaskini apabila borang dihantar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($location) || empty($price)) {
        $error = "Sila isi semua ruangan yang wajib!";
    } else {
        if ($current_role === 'admin') {
            $update_stmt = $conn->prepare("UPDATE houses SET title = ?, location = ?, price = ?, description = ? WHERE id = ?");
            $update_stmt->bind_param("ssdsi", $title, $location, $price, $description, $house_id);
        } else {
            $update_stmt = $conn->prepare("UPDATE houses SET title = ?, location = ?, price = ?, description = ? WHERE id = ? AND landlord_id = ?");
            $update_stmt->bind_param("ssdsii", $title, $location, $price, $description, $house_id, $user_id);
        }

        if ($update_stmt->execute()) {
            $success = "Maklumat rumah berjaya dikemaskini!";
            $house['title'] = $title;
            $house['location'] = $location;
            $house['price'] = $price;
            $house['description'] = $description;
        } else {
            $error = "Ralat semasa mengemaskini data.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Property - RentEasy</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid #dee2e6; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 22px; font-weight: bold; color: #e00b25; text-decoration: none; }
        .form-container { max-width: 600px; background: #ffffff; padding: 30px; margin: 30px auto; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        .form-group textarea { resize: vertical; height: 120px; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .btn-back { color: #666; text-decoration: none; font-size: 14px; display: inline-block; margin-bottom: 15px; }
        .btn-submit { background: #6c757d; color: white; border: none; padding: 12px 20px; width: 100%; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background: #5a6268; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🏡 RentEasy</a>
    <div>
        <a href="my_listings.php" style="margin-right: 15px; color: #333; text-decoration: none; font-weight: bold;">My Listings</a>
        <a href="logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold;">Logout</a>
    </div>
</nav>

<div class="container" style="max-width: 1100px; margin: 30px auto; padding: 0 20px;">
    <div class="form-container">
        <a href="my_listings.php" class="btn-back">&larr; Kembali ke Senarai Rumah</a>
        <h2>Edit Property Details</h2>
        <hr style="border: none; border-top: 1px solid #eee; margin-bottom: 20px;">

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-success"><?= $success; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Property Title:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($house['title']); ?>" required>
            </div>

            <div class="form-group">
                <label>Location:</label>
                <input type="text" name="location" value="<?= htmlspecialchars($house['location']); ?>" required>
            </div>

            <div class="form-group">
                <label>Price (RM):</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($house['price']); ?>" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <textarea name="description"><?= htmlspecialchars($house['description']); ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Update Property</button>
        </form>
    </div>
</div>

</body>
</html>
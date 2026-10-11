<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Ambil senarai rumah yang di-favourite oleh user ini sahaja
$query = "SELECT houses.*, users.name as landlord_name 
          FROM favorites 
          JOIN houses ON favorites.house_id = houses.id 
          JOIN users ON houses.landlord_id = users.id 
          WHERE favorites.user_id = ? 
          ORDER BY favorites.id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favourites - RentEasy</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #e00b25;
            text-decoration: none;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .nav-links a {
            text-decoration: none;
            color: #495057;
            font-weight: bold;
            font-size: 14px;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .btn-grey {
            background-color: #6c757d;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
        }
        .btn-grey:hover {
            background-color: #5a6268;
        }
        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        .property-card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }
        .property-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 14px;
        }
        .property-body {
            padding: 15px;
        }
        .property-title {
            font-size: 18px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 8px;
            text-decoration: none;
            display: block;
        }
        .property-location {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .property-price {
            font-size: 20px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 15px;
        }
        .property-price span {
            font-size: 13px;
            font-weight: normal;
            color: #6c757d;
        }
        .property-footer {
            padding: 15px;
            border-top: 1px solid #dee2e6;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .landlord-name {
            font-size: 13px;
            color: #6c757d;
        }
        .fav-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            font-size: 18px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🏡 RentEasy</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="favorites.php" style="color: #000;">Favourites</a>
        <span style="color: #495057; font-size: 14px;">Hi, <?= htmlspecialchars($user_name) ?></span>
        <a href="logout.php" style="color: #dc3545;">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Hartanah Kegemaran Anda ❤️</h2>
    <div class="property-grid" style="margin-top: 20px;">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($house = $result->fetch_assoc()): ?>
                <div class="property-card">
                    <div>
                        <?php
                            $hId = $house['id'];
                            $imgQ = $conn->query("SELECT image_path FROM house_images WHERE house_id = $hId LIMIT 1");
                            $firstImg = $imgQ->fetch_assoc();
                        ?>
                        <a href="toggle_favorite.php?id=<?= $hId; ?>" class="fav-btn" title="Remove from Favourite">❤️</a>

                        <?php if ($firstImg && file_exists($firstImg['image_path'])): ?>
                            <img src="<?= htmlspecialchars($firstImg['image_path']); ?>" class="property-img" alt="Property Image">
                        <?php else: ?>
                            <div class="property-img">📷 No Image Available</div>
                        <?php endif; ?>

                        <div class="property-body">
                            <a href="house_details.php?id=<?= $house['id']; ?>" class="property-title"><?= htmlspecialchars($house['title']); ?></a>
                            <div class="property-location">📍 <?= htmlspecialchars($house['location']); ?></div>
                            <div class="property-price">RM <?= number_format($house['price'], 2); ?> <span>/ month</span></div>
                        </div>
                    </div>

                    <div class="property-footer">
                        <span class="landlord-name">Oleh: <?= htmlspecialchars($house['landlord_name']); ?></span>
                        <a href="house_details.php?id=<?= $house['id']; ?>" class="btn-grey">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                <h3>Tiada rumah dalam senarai kegemaran.</h3>
                <p>Anda boleh klik ikon hati (❤️) pada mana-mana rumah di halaman utama.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
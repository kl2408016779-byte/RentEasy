<?php
require_once 'config/db.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Carian dan Penapisan (Search, Location, Min Price, Max Price)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : '';

$query = "SELECT houses.*, users.name as landlord_name FROM houses JOIN users ON houses.landlord_id = users.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (houses.title LIKE ? OR houses.description LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if (!empty($location)) {
    $query .= " AND houses.location LIKE ?";
    $params[] = "%" . $location . "%";
    $types .= "s";
}

if ($min_price !== '') {
    $query .= " AND houses.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price !== '') {
    $query .= " AND houses.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$query .= " ORDER BY houses.id DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RentEasy - Student Accommodation UPTM</title>
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
        .nav-links a:hover {
            color: #212529;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .hero-section {
            text-align: center;
            margin-bottom: 40px;
        }
        .hero-section h1 {
            font-size: 32px;
            color: #343a40;
            margin-bottom: 10px;
        }
        .hero-section p {
            color: #6c757d;
            font-size: 16px;
        }
        /* Kotak Carian & Penapis */
        .search-bar-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-bar-container input, .search-bar-container select {
            flex: 1;
            min-width: 160px;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
        }
        /* Butang Kelabu (Grey) */
        .btn-grey {
            background-color: #6c757d !important;
            color: #ffffff !important;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }
        .btn-grey:hover {
            background-color: #5a6268 !important;
        }
        /* Butang Admin */
        .btn-admin {
            background-color: #343a40 !important;
            color: #ffffff !important;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }
        .btn-admin:hover {
            background-color: #23272b !important;
        }
        /* Grid Rumah */
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
        .property-title:hover {
            color: #495057;
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
        <?php if ($user_id): ?>
            <a href="favorites.php">Favourites</a>
            <a href="inbox.php" style="color: #e00b25;">💬 Inbox</a>
            
            <?php if ($user_role === 'landlord' || $user_role === 'admin'): ?>
                <a href="my_listings.php" style="color: #e00b25;">My Listings</a>
            <?php endif; ?>
            
            <?php if ($user_role === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn-admin">⚙️ Admin Dashboard</a>
            <?php endif; ?>

            <span style="color: #495057; font-size: 14px;">Hi, <?= htmlspecialchars($user_name ?: 'User') ?></span>
            
            <?php if ($user_role === 'landlord'): ?>
                <a href="add_house.php" class="btn-grey" style="padding: 6px 12px; font-size: 13px;">Add Property</a>
            <?php endif; ?>
            
            <a href="logout.php" style="color: #dc3545;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-grey" style="padding: 6px 12px; font-size: 13px;">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <div class="hero-section">
        <h1>Find Your Student Accommodation Near UPTM</h1>
        <p>Cari rumah sewa atau bilik selesa berdekatan Universiti Poly-Tech Malaysia (UPTM) Cheras.</p>
    </div>

    <!-- Kotak Carian & Tapisan Harga & Kawasan UPTM -->
    <form method="GET" action="index.php" class="search-bar-container">
        <input type="text" name="search" placeholder="Cari tajuk/keterangan..." value="<?= htmlspecialchars($search); ?>">
        
        <!-- Pilihan Kawasan Sekitar UPTM -->
        <select name="location">
            <option value="">-- Semua Kawasan UPTM --</option>
            <option value="Taman Maluri" <?= ($location === 'Taman Maluri') ? 'selected' : ''; ?>>Taman Maluri</option>
            <option value="Pandan Jaya" <?= ($location === 'Pandan Jaya') ? 'selected' : ''; ?>>Pandan Jaya</option>
            <option value="Jalan Cheras" <?= ($location === 'Jalan Cheras') ? 'selected' : ''; ?>>Jalan Cheras / Miharja</option>
            <option value="Taman Pertama" <?= ($location === 'Taman Pertama') ? 'selected' : ''; ?>>Taman Pertama</option>
            <option value="Maluri Cheras" <?= ($location === 'Maluri Cheras') ? 'selected' : ''; ?>>Maluri (Kawasan Sekitar)</option>
        </select>

        <!-- Penapis Harga Min & Max -->
        <input type="number" name="min_price" placeholder="Min Harga (RM)" value="<?= htmlspecialchars($min_price); ?>" style="min-width: 120px;">
        <input type="number" name="max_price" placeholder="Max Harga (RM)" value="<?= htmlspecialchars($max_price); ?>" style="min-width: 120px;">

        <button type="submit" class="btn-grey">Tapis</button>
    </form>

    <!-- Senarai Rumah / Senarai Hartanah -->
    <div class="property-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($house = $result->fetch_assoc()): ?>
                <div class="property-card">
                    <div>
                        <?php
                            $hId = $house['id'];
                            $imgQ = $conn->query("SELECT image_path FROM house_images WHERE house_id = $hId LIMIT 1");
                            $firstImg = $imgQ->fetch_assoc();
                        ?>

                        <?php if ($user_id): ?>
                            <?php
                                $favCheck = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND house_id = ?");
                                $favCheck->bind_param("ii", $user_id, $hId);
                                $favCheck->execute();
                                $isFav = $favCheck->get_result()->num_rows > 0;
                            ?>
                            <a href="toggle_favorite.php?id=<?= $hId; ?>" class="fav-btn" title="Toggle Favourite">
                                <?= $isFav ? '❤️' : '🤍'; ?>
                            </a>
                        <?php endif; ?>

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
                        <a href="house_details.php?id=<?= $house['id']; ?>" class="btn-grey" style="padding: 6px 12px; font-size: 13px;">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                <h3>Tiada rumah atau bilik dijumpai.</h3>
                <p>Cuba tetapkan julat harga atau kawasan lain.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
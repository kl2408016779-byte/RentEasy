<?php
require_once 'config/db.php';
session_start();

// Dapatkan peranan dari mana-mana kunci sesi yang mungkin digunakan (user_role atau role)
$current_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['role']) ? $_SESSION['role'] : null);

// Semak keselamatan: pastikan hanya admin yang boleh masuk
if (!isset($_SESSION['user_id']) || $current_role !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

// Ambil senarai semua pengguna
$usersResult = $conn->query("SELECT * FROM users ORDER BY id DESC");

// Ambil senarai semua rumah
$housesResult = $conn->query("SELECT houses.*, users.name as landlord_name FROM houses JOIN users ON houses.landlord_id = users.id ORDER BY houses.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - RentEasy</title>
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
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        h2 {
            color: #343a40;
            margin-top: 30px;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            margin-bottom: 40px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        th {
            background-color: #6c757d;
            color: #ffffff;
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
    </style>
</head>
<body>

<nav class="navbar">
    <a href="admin_dashboard.php" class="logo">🏡 RentEasy (Admin)</a>
    <div style="display: flex; align-items: center; gap: 15px;">
        <span style="font-weight: bold; color: #495057; font-size: 14px;">Admin: <?= htmlspecialchars($user_name); ?></span>
        <a href="index.php" style="color: #495057; text-decoration: none; font-weight: bold; font-size: 14px;">Home</a>
        <a href="logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold; font-size: 14px;">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Senarai Pengguna Sistem</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Emel</th>
                <th>Telefon</th>
                <th>Peranan</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $usersResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['phone']); ?></td>
                    <td><strong><?= ucfirst($row['role']); ?></strong></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Senarai Hartanah / Rumah</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tajuk Rumah</th>
                <th>Lokasi</th>
                <th>Harga (RM)</th>
                <th>Tuan Rumah</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($house = $housesResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $house['id']; ?></td>
                    <td><?= htmlspecialchars($house['title']); ?></td>
                    <td><?= htmlspecialchars($house['location']); ?></td>
                    <td>RM <?= number_format($house['price'], 2); ?></td>
                    <td><?= htmlspecialchars($house['landlord_name']); ?></td>
                    <td>
                        <a href="house_details.php?id=<?= $house['id']; ?>" class="btn-grey">Lihat</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
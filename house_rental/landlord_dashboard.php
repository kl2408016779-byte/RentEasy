<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM houses WHERE landlord_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tuan Rumah - RentEasy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: #f7f9fa;">

<div class="navbar">
    <a href="index.php" class="logo">RentEasy</a>
    <div class="nav-links">
        <a href="index.php" class="btn-post">Halaman Utama</a>
        <a href="logout.php" class="btn-login" style="background-color: #333;">Logout</a>
    </div>
</div>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Dashboard Tuan Rumah (<?= htmlspecialchars($_SESSION['name']) ?>)</h2>
        <a href="add_house.php" class="btn-search-red" style="text-decoration: none; padding: 10px 18px; border-radius: 6px;">+ Tambah Iklan Baru</a>
    </div>

    <div style="background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin-bottom: 15px;">Senarai Iklan Saya</h3>

        <?php if ($result->num_rows > 0): ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f2f2f2; text-align: left;">
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Gambar</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Tajuk</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Lokasi</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Harga</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                <img src="<?= htmlspecialchars($row['image'] ? $row['image'] : 'https://via.placeholder.com/100x60?text=No+Image') ?>" style="width: 70px; height: 50px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee; font-weight: bold;"><?= htmlspecialchars($row['title']) ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($row['location']) ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee; color: #e00b25; font-weight: bold;">RM <?= number_format($row['price'], 2) ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                <a href="delete_house.php?id=<?= $row['id'] ?>" onclick="return confirm('Adakah anda yakin ingin memadam iklan ini?');" style="color: red; text-decoration: none; font-weight: bold;">Padam</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #666; padding: 10px 0;">Anda belum mendaftarkan sebarang iklan rumah/bilik sewa.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
require_once 'config/db.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$house_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($house_id === 0) {
    header("Location: index.php");
    exit();
}

// Ambil maklumat rumah, tuan rumah, status ketersediaan, dan tempoh kontrak
$stmt = $conn->prepare("SELECT houses.*, users.name as landlord_name, users.phone as landlord_phone, users.email as landlord_email FROM houses JOIN users ON houses.landlord_id = users.id WHERE houses.id = ?");
$stmt->bind_param("i", $house_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$house = $result->fetch_assoc();

// Jika tuan rumah ialah Anep atau nombor kosong, paksa guna nombor 0109201074
if (strtolower($house['landlord_name']) === 'anep' || empty($house['landlord_phone'])) {
    $house['landlord_phone'] = '0109201074';
}

// Ambil gambar-gambar rumah
$imgQuery = $conn->prepare("SELECT image_path FROM house_images WHERE house_id = ?");
$imgQuery->bind_param("i", $house_id);
$imgQuery->execute();
$images = $imgQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($house['title']); ?> - RentEasy</title>
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
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .details-card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .property-title {
            font-size: 24px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 10px;
        }
        .property-location {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .property-price {
            font-size: 24px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 20px;
        }
        .property-price span {
            font-size: 14px;
            font-weight: normal;
            color: #6c757d;
        }
        .badge-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        .status-available {
            background-color: #d4edda;
            color: #155724;
        }
        .status-occupied {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-booking {
            background-color: #fff3cd;
            color: #856404;
        }
        .info-box-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            border-radius: 6px;
        }
        .info-item span {
            display: block;
            font-size: 12px;
            color: #6c757d;
        }
        .info-item strong {
            font-size: 15px;
            color: #343a40;
        }
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .image-gallery img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #343a40;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        .landlord-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            justify-content: space-between;
        }
        .landlord-info {
            flex: 1;
            min-width: 250px;
        }
        .qr-box {
            background: #ffffff;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-align: center;
        }
        .qr-box img {
            width: 120px;
            height: 120px;
            display: block;
            margin: 0 auto 5px auto;
        }
        .btn-grey {
            background-color: #6c757d;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.2s;
        }
        .btn-grey:hover {
            background-color: #5a6268;
        }
        .btn-whatsapp {
            background-color: #25d366;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.2s;
        }
        .btn-whatsapp:hover {
            background-color: #20ba5a;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🏡 RentEasy</a>
    <a href="index.php" style="color: #495057; text-decoration: none; font-weight: bold; font-size: 14px;">&larr; Kembali ke Laman Utama</a>
</nav>

<div class="container">
    <div class="details-card">
        <div class="property-title"><?= htmlspecialchars($house['title']); ?></div>
        <div class="property-location">📍 <?= htmlspecialchars($house['location']); ?></div>

        <!-- Bahagian Status Rumah & Kawalan Tukar Status untuk Landlord -->
        <?php 
            $status = isset($house['status']) ? $house['status'] : 'Available';
            
            if ($status === 'Available') {
                $statusClass = 'status-available';
                $statusText = '🟢 Available (Kosong)';
            } elseif ($status === 'Occupied') {
                $statusClass = 'status-occupied';
                $statusText = '🔴 Occupied (Sudah Disewa)';
            } else {
                $statusClass = 'status-booking';
                $statusText = '🟡 On Booking (Dalam Tempahan)';
            }
        ?>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
            <div>
                <span class="badge-status <?= $statusClass; ?>">
                    <?= $statusText; ?>
                </span>
            </div>

            <!-- Borang Tukar Status khusus untuk Landlord pemilik rumah ini -->
            <?php if ($user_id && $user_id == $house['landlord_id']): ?>
                <form action="update_status.php" method="POST" style="display: flex; gap: 8px; align-items: center; background: #f1f3f5; padding: 8px 12px; border-radius: 6px; border: 1px solid #dee2e6;">
                    <input type="hidden" name="house_id" value="<?= $house_id; ?>">
                    <select name="status" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 13px;">
                        <option value="Available" <?= ($status === 'Available') ? 'selected' : ''; ?>>Available</option>
                        <option value="Occupied" <?= ($status === 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                        <option value="On Booking" <?= ($status === 'On Booking') ? 'selected' : ''; ?>>On Booking</option>
                    </select>
                    <button type="submit" style="background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 13px; cursor: pointer; font-weight: bold;">Tukar Status</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="property-price">RM <?= number_format($house['price'], 2); ?> <span>/ bulan</span></div>

        <!-- Kotak Maklumat Tambahan (Tempoh Kontrak & Lokasi) -->
        <div class="info-box-grid">
            <div class="info-item">
                <span>Tempoh Kontrak Sewaan</span>
                <strong><?= htmlspecialchars($house['contract_duration'] ?? '1 Tahun'); ?></strong>
            </div>
            <div class="info-item">
                <span>Kawasan / Lokasi</span>
                <strong><?= htmlspecialchars($house['location']); ?></strong>
            </div>
        </div>

        <!-- Galeri Gambar Rumah -->
        <div class="image-gallery">
            <?php if ($images->num_rows > 0): ?>
                <?php while ($img = $images->fetch_assoc()): ?>
                    <?php if (file_exists($img['image_path'])): ?>
                        <img src="<?= htmlspecialchars($img['image_path']); ?>" alt="House Image">
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="color: #6c757d;">Tiada gambar untuk hartanah ini.</div>
            <?php endif; ?>
        </div>

        <div class="section-title">Keterangan / Deskripsi</div>
        <p style="line-height: 1.6; color: #495057;"><?= nl2br(htmlspecialchars($house['description'])); ?></p>

        <!-- Seksyen Sejarah Penyewa Terdahulu (Previous Tenants) -->
        <div class="section-title">👥 Sejarah Penyewa Terdahulu (Previous Tenants)</div>
        <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 6px; margin-top: 10px;">
            <?php
            $prev_query = $conn->prepare("SELECT * FROM previous_tenants WHERE house_id = ? ORDER BY end_date DESC");
            $prev_query->bind_param("i", $house_id);
            $prev_query->execute();
            $prev_result = $prev_query->get_result();
            ?>

            <?php if ($prev_result->num_rows > 0): ?>
                <ul style="padding-left: 20px; margin: 0;">
                    <?php while ($tenant = $prev_result->fetch_assoc()): ?>
                        <li style="margin-bottom: 6px; color: #495057;">
                            <strong><?= htmlspecialchars($tenant['tenant_name']); ?></strong> 
                            <span style="color: #6c757d; font-size: 13px;">
                                (Tempoh: <?= htmlspecialchars($tenant['start_date']); ?> hingga <?= htmlspecialchars($tenant['end_date']); ?>)
                            </span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p style="color: #6c757d; font-size: 14px; margin: 0;">Tiada rekod penyewa terdahulu untuk rumah ini.</p>
            <?php endif; ?>
        </div>

        <div class="section-title">Maklumat Tuan Rumah (Landlord)</div>
        <div class="landlord-box">
            <div class="landlord-info">
                <p style="margin: 5px 0;"><strong>Nama:</strong> <?= htmlspecialchars($house['landlord_name']); ?></p>
                <p style="margin: 5px 0;"><strong>Emel:</strong> <?= htmlspecialchars($house['landlord_email']); ?></p>
                <p style="margin: 5px 0;"><strong>No. Telefon:</strong> <?= htmlspecialchars($house['landlord_phone']); ?></p>

                <!-- Butang Hubungi (WhatsApp & Chat) -->
                <?php if ($user_id && $user_id != $house['landlord_id']): ?>
                    <?php 
                        $rawPhone = preg_replace('/[^0-9]/', '', $house['landlord_phone']);
                        if (substr($rawPhone, 0, 1) === '0') {
                            $cleanPhone = '6' . $rawPhone;
                        } else {
                            $cleanPhone = $rawPhone;
                        }

                        $waMessage = urlencode("Hai, saya berminat dengan hartanah anda: " . $house['title']);
                        $waLink = "https://wa.me/{$cleanPhone}?text={$waMessage}";
                    ?>
                    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="<?= $waLink; ?>" target="_blank" class="btn-whatsapp">💬 WhatsApp Tuan Rumah</a>
                        <a href="chat.php?house_id=<?= $house['id']; ?>&receiver_id=<?= $house['landlord_id']; ?>" class="btn-grey">💬 Chat dalam Sistem</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Paparan QR Code untuk WhatsApp -->
            <?php if ($user_id && $user_id != $house['landlord_id']): ?>
                <div class="qr-box">
                    <?php 
                        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($waLink);
                    ?>
                    <img src="<?= $qrApiUrl; ?>" alt="WhatsApp QR Code">
                    <span style="font-size: 11px; color: #6c757d; display: block;">Scan untuk WhatsApp</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
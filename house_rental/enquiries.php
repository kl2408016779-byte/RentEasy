<?php
require_once 'config/db.php';
session_start();

// Semak sama ada pengguna sudah log masuk
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Ambil senarai mesej untuk rumah milik landlord ini
$sql = "SELECT enquiries.*, houses.title as house_title, users.name as tenant_name, users.email as tenant_email 
        FROM enquiries 
        JOIN houses ON enquiries.house_id = houses.id 
        JOIN users ON enquiries.tenant_id = users.id 
        WHERE enquiries.landlord_id = ? 
        ORDER BY enquiries.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Enquiries - RentEasy</title>
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
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1000px;
            margin: 0 auto;
        }
        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #e00b25;
            text-decoration: none;
        }
        .msg-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border-left: 4px solid #e00b25;
        }
        .msg-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .msg-header h4 {
            font-size: 16px;
            color: #333;
            margin: 0;
        }
        .msg-date {
            font-size: 12px;
            color: #888;
        }
        .msg-body {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            color: #444;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .reply-box {
            background: #e2f0d9;
            padding: 10px 12px;
            border-radius: 6px;
            color: #2b542c;
            font-size: 14px;
            margin-bottom: 12px;
            border-left: 3px solid #28a745;
        }
        .btn-reply {
            background-color: #28a745;
            color: white;
            padding: 6px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
        }
        .btn-reply:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">🏡 RentEasy</a>
        <div>
            <a href="my_listings.php" style="margin-right: 15px; color: #333; text-decoration: none; font-weight: bold;">My Listings</a>
            <a href="logout.php" style="color: #e00b25; text-decoration: none; font-weight: bold;">Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="margin: 30px auto; max-width: 800px; padding: 0 20px;">
    <h2 style="margin-bottom: 20px;">📩 Tenant Enquiries</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="msg-card">
                <div class="msg-header">
                    <h4>🏠 <?= htmlspecialchars($row['house_title']); ?></h4>
                    <span class="msg-date"><?= date('d M Y, h:i A', strtotime($row['created_at'])); ?></span>
                </div>
                <p style="font-size: 14px; color: #555; margin-bottom: 10px;">
                    From: <strong><?= htmlspecialchars($row['tenant_name']); ?></strong> (✉ <a href="mailto:<?= htmlspecialchars($row['tenant_email']); ?>"><?= htmlspecialchars($row['tenant_email']); ?></a>)
                </p>
                
                <!-- Mesej Tenant -->
                <div class="msg-body">
                    <strong>Mesej:</strong><br>
                    <?= nl2br(htmlspecialchars($row['message'])); ?>
                </div>

                <!-- Paparkan Balasan Jika Ada -->
                <?php if (!empty($row['reply'])): ?>
                    <div class="reply-box">
                        <strong>Balasan Anda:</strong><br>
                        <?= nl2br(htmlspecialchars($row['reply'])); ?>
                    </div>
                <?php endif; ?>

                <!-- Butang Reply -->
                <div>
                    <a href="reply_enquiry.php?id=<?= $row['id']; ?>" class="btn-reply">
                        <?= !empty($row['reply']) ? 'Edit Reply' : '💬 Reply Enquiry'; ?>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #777; background: #fff; border-radius: 8px; border: 1px solid #dee2e6;">
            <p style="margin: 0;">No enquiries received yet.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
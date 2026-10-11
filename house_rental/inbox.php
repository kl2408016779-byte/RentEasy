<?php
require_once 'config/db.php';
session_start();

// Semak sama ada pengguna sudah log masuk
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil senarai perbualan unik yang melibatkan pengguna ini
$query = "
    SELECT 
        m.*, 
        h.title AS house_title, 
        h.id AS house_id,
        sender.name AS sender_name, 
        receiver.name AS receiver_name,
        receiver.id AS receiver_id_alt,
        sender.id AS sender_id_alt
    FROM messages m
    JOIN houses h ON m.house_id = h.id
    JOIN users sender ON m.sender_id = sender.id
    JOIN users receiver ON m.receiver_id = receiver.id
    WHERE m.id IN (
        SELECT MAX(id) 
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ? 
        GROUP BY house_id, LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
    )
    ORDER BY m.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mesej / Inbox - RentEasy</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid #dee2e6; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 22px; font-weight: bold; color: #e00b25; text-decoration: none; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .inbox-card { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-decoration: none; color: inherit; transition: 0.2s; }
        .inbox-card:hover { border-color: #e00b25; background: #fffcfc; }
        .inbox-info h4 { margin: 0 0 5px 0; font-size: 16px; color: #333; }
        .inbox-info p { margin: 0 0 5px 0; font-size: 14px; color: #666; }
        .inbox-info small { color: #999; font-size: 12px; }
        .btn-chat { background: #e00b25; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-chat:hover { background: #c50920; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🏡 RentEasy</a>
    <div>
        <a href="enquiries.php" style="margin-right: 15px; color: #333; text-decoration: none; font-weight: bold;">Enquiries</a>
        <a href="logout.php" style="color: #e00b25; text-decoration: none; font-weight: bold;">Logout</a>
    </div>
</nav>

<div class="container">
    <h2 style="margin-bottom: 20px;">💬 Mesej / Direct Messages</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
                // Tentukan siapa lawan berbual (partner chat)
                $partner_id = ($row['sender_id'] == $user_id) ? $row['receiver_id'] : $row['sender_id'];
                $partner_name = ($row['sender_id'] == $user_id) ? $row['receiver_name'] : $row['sender_name'];
            ?>
            <a href="chat.php?house_id=<?= $row['house_id']; ?>&receiver_id=<?= $partner_id; ?>" class="inbox-card">
                <div class="inbox-info">
                    <h4>Perbualan dengan: <?= htmlspecialchars($partner_name); ?></h4>
                    <p><strong>Rumah:</strong> <?= htmlspecialchars($row['house_title']); ?></p>
                    <p style="color: #444; font-size: 13px; margin-top: 5px;"><?= htmlspecialchars(substr($row['message'], 0, 60)) . (strlen($row['message']) > 60 ? '...' : ''); ?></p>
                    <small><?= date('d M Y, h:i A', strtotime($row['created_at'])); ?></small>
                </div>
                <div>
                    <span class="btn-chat">Buka Chat</span>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #777; background: #fff; border-radius: 8px; border: 1px solid #dee2e6;">
            <p style="margin: 0;">Belum ada sebarang mesej atau perbualan.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
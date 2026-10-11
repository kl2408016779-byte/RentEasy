<?php
require_once 'config/db.php';
session_start();

// Semak sama ada pengguna sudah log masuk
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$house_id = isset($_GET['house_id']) ? intval($_GET['house_id']) : 0;
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

if ($house_id === 0 || $receiver_id === 0) {
    die("Maklumat tidak sah.");
}

// Ambil maklumat rumah
$houseQ = $conn->prepare("SELECT * FROM houses WHERE id = ?");
$houseQ->bind_param("i", $house_id);
$houseQ->execute();
$house = $houseQ->get_result()->fetch_assoc();

// Tentukan siapa landlord dan siapa tenant
$landlord_id = $house['landlord_id'];
$tenant_id = ($user_id == $landlord_id) ? $receiver_id : $user_id;

$receiverQ = $conn->prepare("SELECT id, name FROM users WHERE id = ?");
$receiverQ->bind_param("i", $receiver_id);
$receiverQ->execute();
$receiver = $receiverQ->get_result()->fetch_assoc();

// Proses hantar mesej jika borang diserah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // 1. Simpan ke jadual 'messages' (untuk paparan chat live)
        $ins = $conn->prepare("INSERT INTO messages (house_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $ins->bind_param("iiis", $house_id, $user_id, $receiver_id, $message);
        $ins->execute();

        // 2. Jika penghantar adalah tenant, pastikan ia turut dimasukkan ke dalam jadual 'enquiries'
        if ($user_id != $landlord_id) {
            // Semak sama ada enquiry untuk rumah ini dan tenant ini sudah wujud
            $checkEnq = $conn->prepare("SELECT id FROM enquiries WHERE house_id = ? AND tenant_id = ? AND landlord_id = ?");
            $checkEnq->bind_param("iii", $house_id, $tenant_id, $landlord_id);
            $checkEnq->execute();
            $enqRes = $checkEnq->get_result();

            if ($enqRes->num_rows > 0) {
                // Kemaskini mesej terkini dalam enquiry jika perlu atau biarkan
                $updateEnq = $conn->prepare("UPDATE enquiries SET message = ? WHERE house_id = ? AND tenant_id = ?");
                $updateEnq->bind_param("sii", $message, $house_id, $tenant_id);
                $updateEnq->execute();
            } else {
                // Cipta rekod enquiry baharu
                $insEnq = $conn->prepare("INSERT INTO enquiries (house_id, tenant_id, landlord_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $insEnq->bind_param("iiis", $house_id, $tenant_id, $landlord_id, $message);
                $insEnq->execute();
            }
        }

        header("Location: chat.php?house_id=$house_id&receiver_id=$receiver_id");
        exit();
    }
}

// Ambil senarai mesej antara dua pengguna ini untuk rumah ini
$msgQ = $conn->prepare("
    SELECT * FROM messages 
    WHERE house_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
    ORDER BY created_at ASC
");
$msgQ->bind_param("iiiii", $house_id, $user_id, $receiver_id, $receiver_id, $user_id);
$msgQ->execute();
$messages = $msgQ->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat - <?= htmlspecialchars($house['title']); ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid #dee2e6; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 22px; font-weight: bold; color: #e00b25; text-decoration: none; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .chat-box { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; display: flex; flex-direction: column; height: 500px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
        .chat-header { padding: 15px; border-bottom: 1px solid #dee2e6; background: #f8f9fa; font-weight: bold; font-size: 16px; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #fdfdfd; }
        .message { max-width: 70%; padding: 10px 15px; border-radius: 8px; font-size: 14px; line-height: 1.4; }
        .message.sent { background: #e00b25; color: #fff; align-self: flex-end; border-bottom-right-radius: 0; }
        .message.received { background: #e9ecef; color: #333; align-self: flex-start; border-bottom-left-radius: 0; }
        .message-time { font-size: 10px; margin-top: 5px; text-align: right; opacity: 0.8; }
        .chat-input-form { display: flex; border-top: 1px solid #dee2e6; padding: 10px; background: #fff; }
        .chat-input-form input { flex: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; outline: none; }
        .chat-input-form button { background: #e00b25; color: #fff; border: none; padding: 10px 20px; margin-left: 10px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .chat-input-form button:hover { background: #c50920; }
        .back-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #6c757d; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🏡 RentEasy</a>
    <a href="logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold; font-size: 14px;">Logout</a>
</nav>

<div class="container">
    <a href="house_details.php?id=<?= $house_id; ?>" class="back-link">&larr; Kembali ke Maklumat Rumah</a>
    
    <div class="chat-box">
        <div class="chat-header">
            Perbualan dengan <?= htmlspecialchars($receiver['name']); ?> mengenai: <em><?= htmlspecialchars($house['title']); ?></em>
        </div>
        
        <div class="chat-messages">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="message <?= ($msg['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                        <div><?= nl2br(htmlspecialchars($msg['message'])); ?></div>
                        <div class="message-time"><?= date('d M Y, h:i A', strtotime($msg['created_at'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; color: #6c757d; margin-top: auto; margin-bottom: auto;">
                    Belum ada mesej lagi. Mulakan perbualan di bawah!
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="chat-input-form">
            <input type="text" name="message" placeholder="Taip mesej anda di sini..." required autocomplete="off">
            <button type="submit">Hantar</button>
        </form>
    </div>
</div>

</body>
</html>
<?php
require_once 'config/db.php';
session_start();

// Semak sama ada pengguna sudah log masuk
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Dapatkan ID enquiry dari URL
if (!isset($_GET['id'])) {
    header("Location: enquiries.php");
    exit();
}

$enquiry_id = $_GET['id'];

// Ambil maklumat enquiry (semak sama ada rumah ini milik pengguna yang sedang log masuk)
$query = "SELECT e.*, h.title AS house_title, u.name AS tenant_name, u.email AS tenant_email 
          FROM enquiries e 
          JOIN houses h ON e.house_id = h.id 
          JOIN users u ON e.tenant_id = u.id 
          WHERE e.id = ? AND h.landlord_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $enquiry_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: enquiries.php");
    exit();
}

$data = $result->fetch_assoc();
$success = '';
$error = '';

// Proses apabila landlord hantar balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_message = trim($_POST['reply']);

    if (!empty($reply_message)) {
        // Simpan balasan ke dalam database
        $update_stmt = $conn->prepare("UPDATE enquiries SET reply = ? WHERE id = ?");
        $update_stmt->bind_param("si", $reply_message, $enquiry_id);

        if ($update_stmt->execute()) {
            $success = "Balasan berjaya dihantar kepada penyewa!";
            $data['reply'] = $reply_message; // Kemaskini paparan terus
        } else {
            $error = "Ralat semasa menghantar balasan.";
        }
    } else {
        $error = "Mesej balasan tidak boleh kosong!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reply Enquiry - RentEasy</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid #dee2e6; padding: 15px 30px; }
        .nav-container { display: flex; justify-content: space-between; align-items: center; max-width: 1000px; margin: 0 auto; }
        .logo { font-size: 22px; font-weight: bold; color: #e00b25; text-decoration: none; }
        .chat-container {
            max-width: 700px;
            background: #ffffff;
            padding: 30px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .message-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .reply-box {
            background: #e2f0d9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            height: 120px;
            resize: vertical;
        }
        .btn-back {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 15px;
        }
        .btn-submit {
            background-color: #e00b25;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-submit:hover {
            background-color: #c50920;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">🏡 RentEasy</a>
        <div>
            <a href="enquiries.php" style="margin-right: 15px; color: #333; text-decoration: none; font-weight: bold;">Enquiries</a>
            <a href="logout.php" style="color: #e00b25; text-decoration: none; font-weight: bold;">Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="padding: 0 20px;">
    <div class="chat-container">
        <a href="enquiries.php" class="btn-back">&larr; Kembali ke Senarai Enquiries</a>
        <h2>Reply to Tenant Enquiry</h2>
        <hr style="border: none; border-top: 1px solid #eee; margin-bottom: 20px;">

        <p><strong>House:</strong> <?= htmlspecialchars($data['house_title']); ?></p>
        <p><strong>Tenant:</strong> <?= htmlspecialchars($data['tenant_name']); ?> (<?= htmlspecialchars($data['tenant_email']); ?>)</p>
        <br>

        <!-- Mesej daripada Tenant -->
        <div class="message-box">
            <strong>Mesej daripada Penyewa:</strong>
            <p style="margin-top: 8px;"><?= nl2br(htmlspecialchars($data['message'])); ?></p>
            <small style="color: #666;"><?= $data['created_at']; ?></small>
        </div>

        <!-- Jika sudah ada balasan sebelum ini -->
        <?php if (!empty($data['reply'])): ?>
            <div class="reply-box">
                <strong>Balasan Anda (Landlord):</strong>
                <p style="margin-top: 8px;"><?= nl2br(htmlspecialchars($data['reply'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= $success; ?></div>
        <?php endif; ?>

        <!-- Borang untuk Landlord Balas -->
        <form action="" method="POST">
            <div class="form-group">
                <label><strong>Tulis Balasan Anda:</strong></label>
                <textarea name="reply" placeholder="Taip mesej balasan anda di sini..." required><?php echo isset($data['reply']) ? htmlspecialchars($data['reply']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn-submit" style="width: 100%; padding: 12px; font-size: 16px;">Hantar Balasan</button>
        </form>
    </div>
</div>

</body>
</html>
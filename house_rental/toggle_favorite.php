<?php
require_once 'config/db.php';
session_start();

// Semak sama ada pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$house_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($house_id > 0) {
    // Semak sama ada rumah ini sudah ada dalam senarai favourite pengguna
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND house_id = ?");
    $stmt->bind_param("ii", $user_id, $house_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah ada, buang dari favourite (DELETE)
        $del = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND house_id = ?");
        $del->bind_param("ii", $user_id, $house_id);
        $del->execute();
    } else {
        // Jika belum ada, masukkan ke dalam favourite (INSERT)
        $ins = $conn->prepare("INSERT INTO favorites (user_id, house_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $house_id);
        $ins->execute();
    }
}

// Bawa balik pengguna ke halaman sebelum ini (index atau halaman asal)
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: " . $redirect);
exit();
<?php
require_once 'config/db.php';
session_start();

// Semak sama ada pengguna adalah landlord yang sah
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $house_id = intval($_POST['house_id']);
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];

    // Pastikan rumah ini milik landlord yang sedang login
    $check = $conn->prepare("SELECT id FROM houses WHERE id = ? AND landlord_id = ?");
    $check->bind_param("ii", $house_id, $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Kemaskini status dalam database
        $update = $conn->prepare("UPDATE houses SET status = ? WHERE id = ?");
        $update->bind_param("si", $status, $house_id);
        $update->execute();
    }

    header("Location: house_details.php?id=" . $house_id);
    exit();
}
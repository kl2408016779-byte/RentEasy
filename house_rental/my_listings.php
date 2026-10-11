<?php
require_once 'config/db.php';
session_start();

// Dapatkan peranan dari mana-mana kunci sesi yang mungkin digunakan
$current_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['role']) ? $_SESSION['role'] : null);

// Semak sama ada pengguna sudah login dan merupakan seorang 'landlord' atau 'admin'
if (!isset($_SESSION['user_id']) || ($current_role !== 'landlord' && $current_role !== 'admin')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fungsi untuk Padam Rumah (Delete Listing)
if (isset($_GET['delete'])) {
    $house_id = $_GET['delete'];

    if ($current_role === 'admin') {
        $stmt = $conn->prepare("DELETE FROM houses WHERE id = ?");
        $stmt->bind_param("i", $house_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM houses WHERE id = ? AND landlord_id = ?");
        $stmt->bind_param("ii", $house_id, $user_id);
    }
    
    if ($stmt->execute()) {
        header("Location: my_listings.php?msg=deleted");
        exit();
    }
}

// Ambil senarai rumah: Jika admin, ambil semua. Jika landlord, ambil rumah milik sendiri sahaja.
if ($current_role === 'admin') {
    $stmt = $conn->prepare("SELECT houses.*, users.name as landlord_name FROM houses JOIN users ON houses.landlord_id = users.id ORDER BY houses.id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM houses WHERE landlord_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Property Listings - RentEasy</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid #dee2e6; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 22px; font-weight: bold; color: #e00b25; text-decoration: none; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .table-container { background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background-color: #f8f9fa; color: #333; font-weight: bold; }
        .btn-edit { background-color: #ffc107; color: #333; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; margin-right: 5px; display: inline-block; }
        .btn-edit:hover { background-color: #e0a800; }
        .btn-view { background-color: #007bff; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; margin-right: 5px; display: inline-block; }
        .btn-view:hover { background-color: #0056b3; }
        .btn-delete { background-color: #e00b25; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; display: inline-block; }
        .btn-delete:hover { background-color: #c0081e; }
        .btn-add { background-color: #6c757d; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-add:hover { background-color: #5a6268; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🏡 RentEasy</a>
    <div>
        <a href="index.php" style="margin-right: 15px; color: #333; text-decoration: none; font-weight: bold;">Home</a>
        <?php if ($current_role === 'admin'): ?>
            <a href="admin_dashboard.php" style="margin-right: 15px; color: #343a40; text-decoration: none; font-weight: bold;">Admin Dashboard</a>
        <?php endif; ?>
        <a href="enquiries.php" style="margin-right: 15px; color: #333; text-decoration: none; font-weight: bold;">📩 Enquiries</a>
        <a href="logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold;">Logout</a>
    </div>
</nav>

<div class="container">
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb; margin-bottom: 20px; padding: 10px; border-radius: 4px;">
            Property listing has been successfully deleted!
        </div>
    <?php endif; ?>

    <div class="dashboard-header">
        <h2>My Property Listings</h2>
        <a href="add_house.php" class="btn-add">+ Add New Property</a>
    </div>

    <div class="table-container">
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Price (RM)</th>
                        <?php if ($current_role === 'admin'): ?><th>Landlord</th><?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['title']); ?></strong></td>
                            <td>📍 <?= htmlspecialchars($row['location']); ?></td>
                            <td>RM <?= number_format($row['price'], 2); ?></td>
                            <?php if ($current_role === 'admin'): ?>
                                <td><?= htmlspecialchars($row['landlord_name'] ?? '-'); ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="edit_house.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                                <a href="house_details.php?id=<?= $row['id']; ?>" class="btn-view" target="_blank">View</a>
                                <a href="my_listings.php?delete=<?= $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this listing?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 30px; color: #666;">
                <p>You haven't posted any properties yet.</p>
                <a href="add_house.php" class="btn-add" style="margin-top: 10px; display: inline-block;">Post Your First Property</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
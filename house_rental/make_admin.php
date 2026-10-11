<?php
require_once 'config/db.php';

// Emel anda telah ditetapkan di sini secara automatik
$target_email = "meorrrrahman@gmail.com";

// Kemaskini peranan (role) pengguna tersebut kepada admin
$stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
$stmt->bind_param("s", $target_email);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<div style='font-family: Arial; padding: 40px; text-align: center; color: #155724; background: #d4edda; border-radius: 8px; max-width: 500px; margin: 50px auto;'>";
        echo "<h2>Tahniah, Berjaya!</h2>";
        echo "<p>Akaun dengan emel <strong>$target_email</strong> kini telah dinaik taraf menjadi <strong>Admin</strong>.</p>";
        echo "<br><a href='login.php' style='padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;'>Log Masuk Sekarang</a>";
        echo "</div>";
    } else {
        echo "<div style='font-family: Arial; padding: 40px; text-align: center; color: #721c24; background: #f8d7da; border-radius: 8px; max-width: 500px; margin: 50px auto;'>";
        echo "<h2>Ralat!</h2>";
        echo "<p>Emel <strong>$target_email</strong> tidak dijumpai di dalam pangkalan data.</p>";
        echo "<p>Sila pastikan anda sudah mendaftar akaun menggunakan emel ini di halaman pendaftaran sistem RentEasy sebelum menjalankan fail ini.</p>";
        echo "</div>";
    }
} else {
    echo "Ralat sistem: " . $conn->error;
}
?>
<?php
require_once 'config/db.php';

$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    house_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Jadual 'messages' berjaya dicipta! <br><a href='index.php'>Kembali ke Laman Utama</a>";
} else {
    echo "Ralat semasa mencipta jadual: " . $conn->error;
}
?>
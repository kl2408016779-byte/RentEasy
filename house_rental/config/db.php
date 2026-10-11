<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'house_rental';
$port = 3307; // Tukar ke 3306 jika MySQL anda guna port standard

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
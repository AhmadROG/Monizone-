<?php
// db.php - Fail sambungan database

$servername = "localhost";
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP password (kosong)
$dbname = "sales_iou_db"; // Pastikan ejaan sama dengan database awak

// Buat connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Gagal sambung database: " . mysqli_connect_error());
}

// Kita start session kat sini supaya tak perlu ulang dalam setiap page lain
session_start();
?>
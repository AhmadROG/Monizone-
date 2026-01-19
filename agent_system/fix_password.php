<?php
include 'db.php';

// Kita set password baru: 'password123'
$password_baru = password_hash('password123', PASSWORD_DEFAULT);

// 1. Update password Admin
$sql_admin = "UPDATE Users SET password = '$password_baru' WHERE username = 'admin1'";

// 2. Update password Ejen
$sql_agent = "UPDATE Agent SET password = '$password_baru' WHERE username = 'agent1'";

if (mysqli_query($conn, $sql_admin) && mysqli_query($conn, $sql_agent)) {
    echo "<h1>BERJAYA! Password telah di-reset.</h1>";
    echo "<p>Sekarang password untuk 'admin1' dan 'agent1' adalah: <b>password123</b></p>";
    echo "<a href='login.php'>Klik sini untuk Login semula</a>";
} else {
    echo "Error updating record: " . mysqli_error($conn);
}
?>
<?php
include 'db.php';

echo "<h2>🕵️‍♂️ Misi Mencari Punca Masalah Login</h2>";

$username_check = 'admin1'; // Kita test user ni
$password_check = 'password123'; // Password yang kita nak test

// 1. Cek adakah user wujud?
$sql = "SELECT * FROM Users WHERE username = '$username_check'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $hash_dalam_db = $row['password'];
    
    echo "<p>✅ User <b>$username_check</b> DIJUMPAI dalam database.</p>";
    echo "<p>🔑 Hash yang disimpan: <br><code>$hash_dalam_db</code></p>";
    echo "<p>📏 Panjang Hash: " . strlen($hash_dalam_db) . " karakter (Sepatutnya 60)</p>";
    
    // 2. Test Verify
    if (password_verify($password_check, $hash_dalam_db)) {
        echo "<h3 style='color:green'>✅ KEPUTUSAN: Password Verify BERJAYA!</h3>";
        echo "<p>Masalah mungkin pada borang login.php awak (mungkin ada space pada input).</p>";
    } else {
        echo "<h3 style='color:red'>❌ KEPUTUSAN: Password Verify GAGAL!</h3>";
        echo "<p>Hash dalam database tak padan dengan 'password123'.</p>";
        
        // 3. Auto-Fix (Reset semula password secara paksa)
        $new_hash = password_hash($password_check, PASSWORD_DEFAULT);
        $update = "UPDATE Users SET password='$new_hash' WHERE username='$username_check'";
        mysqli_query($conn, $update);
        echo "<hr><p>🛠 <b>AUTO-FIX:</b> Saya dah reset password admin1 sekali lagi.</p>";
        echo "<p>Hash Baru: <code>$new_hash</code></p>";
        echo "<p>Sila cuba login semula sekarang.</p>";
    }
} else {
    echo "<h3 style='color:red'>❌ User '$username_check' TIADA dalam database!</h3>";
    echo "<p>Sila run SQL INSERT semula.</p>";
}
?>
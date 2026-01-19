<?php
include 'db.php';

// Masukkan ID ejen awak (biasanya 1, atau tengok dalam table Agent)
// Kalau login session ada, kita guna session. Kalau tak, kita assume ID=1
$agent_id = isset($_SESSION['agent_id']) ? $_SESSION['agent_id'] : 1; 

echo "<h1>🕵️‍♂️ Misi Cari Hutang Hilang</h1>";
echo "<h3>Checking Agent ID: $agent_id</h3>";

// 1. Cek Table IOU_Ledger
echo "<h4>1. Data dalam IOU_Ledger:</h4>";
$sql = "SELECT * FROM IOU_Ledger WHERE agent_id='$agent_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Type</th><th>Amount</th><th>Date</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['ledger_id'] . "</td>";
        echo "<td>" . $row['transaction_type'] . "</td>"; // Patut keluar DEBIT
        echo "<td>RM " . $row['amount'] . "</td>";       // Patut ada nilai
        echo "<td>" . $row['transaction_date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ Kosong! Tiada rekod hutang langsung.</p>";
    echo "<p>Punca: Staf belum tekan LULUS atau button LULUS ada error.</p>";
}

// 2. Cek Total Kiraan
$q_debit = mysqli_query($conn, "SELECT SUM(amount) as total FROM IOU_Ledger WHERE agent_id='$agent_id' AND transaction_type='DEBIT'");
$d_debit = mysqli_fetch_assoc($q_debit);
$total = $d_debit['total'];

echo "<h4>2. Total Hutang System Kira: RM " . number_format((float)$total, 2) . "</h4>";
?>
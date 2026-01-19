<?php
include 'db.php';

// Cek Security: Hanya Agent
if (!isset($_SESSION['agent_id'])) {
    header("Location: login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];
include 'header.php';
?>

<div class="row">
    <div class="col-md-12 mb-3">
        <h2><i class="fas fa-history"></i> Sejarah Transaksi</h2>
        <p class="text-muted">Rekod pergerakan hutang (Audit vs Bayaran).</p>
    </div>

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Tarikh</th>
                            <th>Keterangan</th>
                            <th>Jenis Transaksi</th>
                            <th>Jumlah (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Kita tarik dari IOU_Ledger sebab ini buku akaun paling tepat
                        $sql = "SELECT * FROM IOU_Ledger WHERE agent_id='$agent_id' ORDER BY transaction_date DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $type = $row['transaction_type'];
                                $amount = $row['amount'];
                                $date = date('d/m/Y h:i A', strtotime($row['transaction_date']));
                                
                                // Logic Warna & Tulisan
                                if ($type == 'DEBIT') {
                                    // DEBIT = HUTANG BERTAMBAH (Ambil barang)
                                    $badge = "<span class='badge bg-danger'>HUTANG MASUK</span>";
                                    $color = "text-danger";
                                    $symbol = "+"; // Hutang naik
                                } else {
                                    // CREDIT = HUTANG DIBAYAR
                                    $badge = "<span class='badge bg-success'>BAYARAN</span>";
                                    $color = "text-success";
                                    $symbol = "-"; // Hutang turun
                                }
                                ?>
                                <tr>
                                    <td><?php echo $date; ?></td>
                                    <td><?php echo $row['description']; ?></td>
                                    <td><?php echo $badge; ?></td>
                                    <td class="fw-bold <?php echo $color; ?>">
                                        <?php echo $symbol; ?> RM <?php echo number_format($amount, 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>Tiada rekod transaksi.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="dashboard_agent.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
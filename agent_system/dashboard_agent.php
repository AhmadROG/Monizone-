<?php
include 'db.php';

// Cek Security: Mesti login sebagai AGENT
if (!isset($_SESSION['agent_id'])) {
    header("Location: login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];

include 'header.php';

// --- LOGIK KIRA HUTANG (Ini yang awak tertinggal tadi) ---
// 1. Kira Total DEBIT (Hutang Masuk/Audit)
$q_debit = mysqli_query($conn, "SELECT SUM(amount) as total FROM IOU_Ledger WHERE agent_id='$agent_id' AND transaction_type='DEBIT'");
$d_debit = mysqli_fetch_assoc($q_debit);
$total_debit = $d_debit['total'] ?? 0;

// 2. Kira Total CREDIT (Bayaran Dah Dibuat)
$q_credit = mysqli_query($conn, "SELECT SUM(amount) as total FROM IOU_Ledger WHERE agent_id='$agent_id' AND transaction_type='CREDIT'");
$d_credit = mysqli_fetch_assoc($q_credit);
$total_credit = $d_credit['total'] ?? 0;

// 3. Baki Bersih
$baki_hutang = $total_debit - $total_credit;

// Warna Kotak: Merah (Hutang) / Hijau (Clear)
$bg_color = ($baki_hutang > 0) ? "bg-danger" : "bg-success";
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Selamat Datang, <?php echo $_SESSION['full_name']; ?>!</h2>
        <p class="text-muted">Laporan Audit & Restock Mingguan.</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="card <?php echo $bg_color; ?> text-white shadow">
            <div class="card-body">
                <h6 class="card-title">BAKI HUTANG (IOU)</h6>
                <h2 class="fw-bold">RM <?php echo number_format($baki_hutang, 2); ?></h2>
                <small>Jumlah perlu dibayar ke HQ</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-boxes fa-3x text-primary mb-3"></i>
                <h5>Audit & Restock</h5>
                <p class="text-muted small">Isi unit terjual & harga hutang.</p>
                <a href="agent_request.php" class="btn btn-primary w-100">Isi Borang Audit</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                <h5>Bayar Hutang</h5>
                <p class="text-muted small">Upload resit bayaran.</p>
                <a href="agent_payment.php" class="btn btn-success w-100">Upload Resit</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-history fa-3x text-secondary mb-3"></i>
                <h5>Sejarah Transaksi</h5>
                <p class="text-muted small">Rekod audit & bayaran.</p>
                <a href="agent_history.php" class="btn btn-outline-secondary w-100">Lihat Rekod</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
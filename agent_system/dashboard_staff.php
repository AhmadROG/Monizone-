<?php
include 'db.php';

// Cek Security: Mesti login DAN role = staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2>Hai, <?php echo $_SESSION['full_name']; ?>! (Staf)</h2>
                    <p class="mb-0">Uruskan inventori, jualan kaunter, dan audit ejen.</p>
                </div>
                <i class="fas fa-user-tie fa-4x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <h5 class="text-muted border-bottom pb-2"><i class="fas fa-users"></i> Pengurusan Ejen</h5>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm border-primary">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-check fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Semakan Audit & Restock</h5>
                <p class="card-text small">Luluskan permintaan stok ejen & rekod hutang.</p>
                <a href="staff_orders.php" class="btn btn-primary w-100">Semak Audit Baru</a>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3 mt-2">
        <h5 class="text-muted border-bottom pb-2"><i class="fas fa-boxes"></i> Inventori & Jualan</h5>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-cubes fa-3x text-success mb-3"></i>
                <h5 class="card-title">Stok Produk</h5>
                <p class="card-text small">Tambah produk baru, update stok gudang & lihat senarai.</p>
                <a href="staff_products.php" class="btn btn-success w-100">Urus Stok</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-cash-register fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Jualan Direct / Manual</h5>
                <p class="card-text small">Rekod jualan Walk-in, Shopee, TikTok (Bukan Ejen).</p>
                <a href="staff_direct_sale.php" class="btn btn-warning w-100 fw-bold">Rekod Jualan</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-chart-bar fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Laporan Bulanan</h5>
                <p class="card-text small">Lihat jadual matriks Stock Out & Sales (Jan - Dis).</p>
                <a href="staff_monthly_report.php" class="btn btn-danger w-100">Lihat Laporan</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
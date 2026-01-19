<?php
include 'db.php';

// Cek Security: Hanya Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'header.php';

// Statistik Ringkas (Untuk Admin Nampak Sekilas Pandang)
$total_agent = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM Users WHERE role='agent'"));
$total_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(hq_stock + warehouse_stock) as total FROM Product"))['total'];
$pending_req = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM Stock_requests WHERE status='Pending'"));
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-dark text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-user-shield"></i> Dashboard Admin</h2>
                    <p class="mb-0">Pantau pengguna, stok HQ, dan prestasi jualan syarikat.</p>
                </div>
                <div class="text-end">
                    <h5 class="mb-0">Total Ejen: <?php echo $total_agent; ?></h5>
                    <small>Pending Order: <?php echo $pending_req; ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <h5 class="text-muted border-bottom pb-2"><i class="fas fa-users-cog"></i> Pengurusan Pengguna</h5>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 border-primary shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Senarai Pengguna</h5>
                <p class="card-text small">Urus pendaftaran Ejen & Staf baru.</p>
                <a href="admin_users.php" class="btn btn-primary w-100">Urus User</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 border-warning shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-user-plus fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Approval Ejen Baru</h5>
                <p class="card-text small">Luluskan pendaftaran ejen yang memohon.</p>
                <a href="admin_approval.php" class="btn btn-warning w-100 fw-bold">Semak Permohonan</a>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3 mt-2">
        <h5 class="text-muted border-bottom pb-2"><i class="fas fa-chart-line"></i> Laporan & Inventori</h5>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                <h5 class="card-title">Stok HQ & Gudang</h5>
                <p class="card-text small">Pantau stok induk dan stok cawangan.</p>
                <a href="staff_products.php" class="btn btn-success w-100">Lihat Stok</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice-dollar fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Laporan Kewangan</h5>
                <p class="card-text small">Lihat laporan bulanan 2025 & 2026.</p>
                <a href="staff_monthly_report.php" class="btn btn-danger w-100">Buka Laporan</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-3x text-info mb-3"></i>
                <h5 class="card-title">Semakan Order Ejen</h5>
                <p class="card-text small">Pantau order ejen yang masuk.</p>
                <a href="staff_orders.php" class="btn btn-info text-white w-100">Lihat Order</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
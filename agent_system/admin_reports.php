<?php
include 'db.php';

// Cek Security: Hanya Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'header.php';

// --- DATA UNTUK KAD & GRAF ---

// 1. Total Stok Keluar (Semua masa)
$q_out = mysqli_query($conn, "SELECT SUM(quantity_requested) as total FROM Stock_Request_items i JOIN Stock_requests r ON i.request_id = r.request_id WHERE r.status='Approved'");
$d_out = mysqli_fetch_assoc($q_out);
$total_stok_keluar = $d_out['total'] ?? 0;

// 2. Total Hutang Ejen (Belum Bayar) - Anggaran duit tertunggak
// (Debit - Credit)
$q_hutang = mysqli_query($conn, "SELECT 
    (SELECT SUM(amount) FROM IOU_Ledger WHERE transaction_type='DEBIT') - 
    (SELECT SUM(amount) FROM IOU_Ledger WHERE transaction_type='CREDIT') as baki");
$d_hutang = mysqli_fetch_assoc($q_hutang);
$total_hutang_luar = $d_hutang['baki'] ?? 0;

// 3. Produk Paling Laris (Top 1)
$q_top = mysqli_query($conn, "SELECT p.product_name, SUM(i.quantity_requested) as total_sold 
                              FROM Stock_Request_items i 
                              JOIN Product p ON i.product_id = p.product_id 
                              JOIN Stock_requests r ON i.request_id = r.request_id 
                              WHERE r.status='Approved' 
                              GROUP BY i.product_id 
                              ORDER BY total_sold DESC LIMIT 1");
$d_top = mysqli_fetch_assoc($q_top);
$top_product = $d_top['product_name'] ?? "Tiada Data";

?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-chart-line"></i> Laporan Prestasi & Analisis Stok</h2>
        <p class="text-muted">Analisis jualan ejen dan cadangan restock gudang.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">TOTAL STOK KELUAR</h6>
                        <h2 class="fw-bold"><?php echo $total_stok_keluar; ?> Unit</h2>
                    </div>
                    <i class="fas fa-dolly fa-3x opacity-50"></i>
                </div>
                <small>Jumlah unit yang telah diaudit & restock.</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">HUTANG TERTUNGGAK</h6>
                        <h2 class="fw-bold">RM <?php echo number_format($total_hutang_luar, 2); ?></h2>
                    </div>
                    <i class="fas fa-hand-holding-usd fa-3x opacity-50"></i>
                </div>
                <small>Nilai duit syarikat di tangan ejen.</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">PRODUK TERLARIS</h6>
                        <h3 class="fw-bold"><?php echo $top_product; ?></h3>
                    </div>
                    <i class="fas fa-crown fa-3x opacity-50"></i>
                </div>
                <small>Paling banyak diminta oleh ejen.</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Graf Jualan Mengikut Produk</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-7 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-robot"></i> Cadangan Restock Pintar</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Stok Gudang</th>
                            <th class="text-center">Total Keluar</th>
                            <th>Status / Cadangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Logic untuk table ramalan
                        $sql_analisis = "SELECT p.product_name, p.warehouse_stock, 
                                        COALESCE(SUM(i.quantity_requested), 0) as total_sold
                                        FROM Product p
                                        LEFT JOIN Stock_Request_items i ON p.product_id = i.product_id
                                        LEFT JOIN Stock_requests r ON i.request_id = r.request_id AND r.status='Approved'
                                        GROUP BY p.product_id";
                        
                        $res_analisis = mysqli_query($conn, $sql_analisis);
                        
                        // Array untuk simpan data graf (Label & Data)
                        $labels = [];
                        $data_points = [];

                        while($row = mysqli_fetch_assoc($res_analisis)){
                            $nama = $row['product_name'];
                            $stok = $row['warehouse_stock'];
                            $jual = $row['total_sold'];
                            
                            // Simpan data untuk Graf
                            $labels[] = $nama;
                            $data_points[] = $jual;

                            // FORMULA CADANGAN RESTOCK
                            // Kita anggap "Average Sales" = Total Jual (sebab data masih sikit)
                            // Jika Stok Gudang < Total Jual, maksudnya stok kritikal
                            if ($stok == 0) {
                                $status = "<span class='badge bg-danger'>HABIS! (Restock Segera)</span>";
                            } elseif ($stok < 10) {
                                $status = "<span class='badge bg-warning text-dark'>KRITIKAL (Beli Stok)</span>";
                            } elseif ($stok < $jual) {
                                $status = "<span class='badge bg-warning text-dark'>RENDAH (Alert)</span>";
                            } else {
                                $status = "<span class='badge bg-success'>SELAMAT</span>";
                            }
                            ?>
                            <tr>
                                <td><?php echo $nama; ?></td>
                                <td class="text-center fw-bold"><?php echo $stok; ?></td>
                                <td class="text-center"><?php echo $jual; ?></td>
                                <td><?php echo $status; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <small class="text-muted fst-italic">*Cadangan berdasarkan baki stok vs kadar jualan ejen.</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart');
    new Chart(ctx, {
        type: 'doughnut', // Jenis Graf: 'bar', 'line', 'pie', 'doughnut'
        data: {
            labels: <?php echo json_encode($labels); ?>, // Nama Produk dari PHP
            datasets: [{
                label: 'Unit Terjual',
                data: <?php echo json_encode($data_points); ?>, // Jumlah Jual dari PHP
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderWidth: 1
            }]
        }
    });
</script>
<div class="row mt-3">
    <div class="col-md-12">
        <a href="dashboard_admin.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>
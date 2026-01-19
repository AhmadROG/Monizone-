<?php
include 'db.php';

// Cek Security: Benarkan Staff ATAU Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

include 'header.php';

// --- LOGIK PILIH TAHUN ---
// Kalau user tak pilih tahun, automatik ambil tahun semasa (2026)
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2><i class="fas fa-calendar-alt"></i> Laporan Bulanan (Stock Out & Sales)</h2>
        <p class="text-muted">Data stok keluar dan jualan untuk tahun: <strong class="text-primary" style="font-size: 1.2em;"><?php echo $selected_year; ?></strong></p>
    </div>
    
    <div class="col-md-4 text-end">
        <div class="btn-group shadow" role="group">
            <a href="staff_monthly_report.php?year=2025" class="btn btn-outline-primary <?php echo ($selected_year == 2025) ? 'active fw-bold' : ''; ?>">
                Tahun 2025
            </a>
            <a href="staff_monthly_report.php?year=2026" class="btn btn-outline-primary <?php echo ($selected_year == 2026) ? 'active fw-bold' : ''; ?>">
                Tahun 2026
            </a>
        </div>
    </div>
</div>

<div class="card shadow mb-5">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">1. UNIT STOCK OUT (Tahun <?php echo $selected_year; ?>)</h5>
        <small>Gabungan Ejen + Manual Sales</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center table-striped table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th class="text-start" style="width: 200px;">PRODUK</th>
                        <?php 
                        $months = ["JAN", "FEB", "MAR", "APR", "MEI", "JUN", "JUL", "OGS", "SEP", "OKT", "NOV", "DIS"];
                        foreach($months as $m) { echo "<th>$m</th>"; }
                        ?>
                        <th class="bg-warning text-dark">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Tarik semua produk (termasuk IBNUSINA baru)
                    $q_pro = mysqli_query($conn, "SELECT * FROM Product ORDER BY category ASC, product_name ASC");
                    
                    if(mysqli_num_rows($q_pro) > 0) {
                        while($prod = mysqli_fetch_assoc($q_pro)) {
                            $pid = $prod['product_id'];
                            $grand_total = 0;
                            
                            echo "<tr>";
                            echo "<td class='text-start fw-bold'>".$prod['product_name']."</td>";
                            
                            for ($m=1; $m<=12; $m++) {
                                // 1. Unit dari Ejen (Audit Approved)
                                $sql_agent = "SELECT SUM(i.quantity_requested) as total FROM Stock_Request_items i 
                                              JOIN Stock_requests r ON i.request_id = r.request_id 
                                              WHERE i.product_id='$pid' AND r.status='Approved' 
                                              AND MONTH(r.request_date)='$m' AND YEAR(r.request_date)='$selected_year'";
                                $res_agent = mysqli_fetch_assoc(mysqli_query($conn, $sql_agent));
                                $qty_agent = $res_agent['total'] ?? 0;

                                // 2. Unit dari Manual/Data Lama (Direct Sales)
                                $sql_direct = "SELECT SUM(quantity) as total FROM Direct_Sales 
                                               WHERE product_id='$pid' 
                                               AND MONTH(sale_date)='$m' AND YEAR(sale_date)='$selected_year'";
                                $res_direct = mysqli_fetch_assoc(mysqli_query($conn, $sql_direct));
                                $qty_direct = $res_direct['total'] ?? 0;

                                // Campur dua-dua
                                $total_month = $qty_agent + $qty_direct;
                                $grand_total += $total_month;

                                // Paparan: Kalau 0 letak dash '-'
                                $display = ($total_month > 0) ? $total_month : "-";
                                echo "<td>$display</td>";
                            }
                            // Total Hujung
                            echo "<td class='fw-bold bg-warning'>$grand_total</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='14'>Tiada produk dalam database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">2. NILAI JUALAN / SALES (RM) (Tahun <?php echo $selected_year; ?>)</h5>
        <small>Anggaran Nilai Jualan</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th class="text-start" style="width: 200px;">PRODUK</th>
                        <?php foreach($months as $m) { echo "<th>$m</th>"; } ?>
                        <th class="bg-success text-white">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Reset pointer query produk ke atas balik
                    mysqli_data_seek($q_pro, 0); 
                    
                    while($prod = mysqli_fetch_assoc($q_pro)) {
                        $pid = $prod['product_id'];
                        $grand_total_rm = 0;
                        
                        echo "<tr>";
                        echo "<td class='text-start fw-bold'>".$prod['product_name']."</td>";
                        
                        for ($m=1; $m<=12; $m++) {
                            // 1. RM Ejen (Qty * Declared Price)
                            $sql_agent = "SELECT SUM(i.quantity_requested * i.price_at_request) as total_rm FROM Stock_Request_items i 
                                          JOIN Stock_requests r ON i.request_id = r.request_id 
                                          WHERE i.product_id='$pid' AND r.status='Approved' 
                                          AND MONTH(r.request_date)='$m' AND YEAR(r.request_date)='$selected_year'";
                            $res_agent = mysqli_fetch_assoc(mysqli_query($conn, $sql_agent));
                            $rm_agent = $res_agent['total_rm'] ?? 0;

                            // 2. RM Manual/Data Lama (Total Price column)
                            $sql_direct = "SELECT SUM(total_price) as total_rm FROM Direct_Sales 
                                           WHERE product_id='$pid' 
                                           AND MONTH(sale_date)='$m' AND YEAR(sale_date)='$selected_year'";
                            $res_direct = mysqli_fetch_assoc(mysqli_query($conn, $sql_direct));
                            $rm_direct = $res_direct['total_rm'] ?? 0;

                            $total_rm_month = $rm_agent + $rm_direct;
                            $grand_total_rm += $total_rm_month;

                            $display = ($total_rm_month > 0) ? number_format($total_rm_month, 0) : "-";
                            echo "<td>$display</td>";
                        }
                        
                        echo "<td class='fw-bold bg-success text-white'>".number_format($grand_total_rm, 2)."</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-3 mb-5">
    <div class="col-md-12">
        <?php 
        // Logik: Kalau Admin -> balik Admin, Kalau Staff -> balik Staff
        $back_link = ($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_staff.php'; 
        ?>
        
        <a href="<?php echo $back_link; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <button onclick="window.print()" class="btn btn-primary float-end">
            <i class="fas fa-print"></i> Cetak / Save PDF
        </button>
    </div>
</div>

<?php include 'footer.php'; ?>
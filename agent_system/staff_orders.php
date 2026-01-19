<?php
include 'db.php';

// Cek Security: Benarkan Staff ATAU Admin
// (Sebelum ni cuma 'staff' je, sekarang kita tambah 'admin')
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

$msg = "";

// LOGIK LULUS
if (isset($_POST['approve_btn'])) {
    $request_id = $_POST['req_id'];
    $agent_id = $_POST['agent_id'];
    
    mysqli_begin_transaction($conn);
    try {
        $staff_id = $_SESSION['user_id'];
        mysqli_query($conn, "UPDATE Stock_requests SET status='Approved', approved_by_user_id='$staff_id' WHERE request_id='$request_id'");

        // Kira Total Hutang guna Harga yang Ejen dah isytihar tadi
        $total_hutang = 0;
        $q_items = mysqli_query($conn, "SELECT * FROM Stock_Request_items WHERE request_id='$request_id'");
        
        while ($item = mysqli_fetch_assoc($q_items)) {
            $pid = $item['product_id'];
            $qty = $item['quantity_requested']; 
            $price = $item['price_at_request']; // Ini harga yang ejen taip
            
            $subtotal = $qty * $price;
            $total_hutang += $subtotal;

            // Tolak Stok
            mysqli_query($conn, "UPDATE Product SET warehouse_stock = warehouse_stock - $qty WHERE product_id='$pid'");
        }

        // Rekod IOU
        $desc = "Audit #" . $request_id;
        $date = date('Y-m-d H:i:s');
        $sql_iou = "INSERT INTO IOU_Ledger (agent_id, transaction_type, amount, transaction_date, description, related_request_id) 
                    VALUES ('$agent_id', 'DEBIT', '$total_hutang', '$date', '$desc', '$request_id')";
        mysqli_query($conn, $sql_iou);

        mysqli_commit($conn);
        $msg = "<div class='alert alert-success'>Audit diluluskan. Hutang direkod: RM $total_hutang</div>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// LOGIK REJECT
if (isset($_POST['reject_btn'])) {
    $request_id = $_POST['req_id'];
    mysqli_query($conn, "UPDATE Stock_requests SET status='Rejected' WHERE request_id='$request_id'");
    $msg = "<div class='alert alert-warning'>Laporan ditolak.</div>";
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-check-double"></i> Semakan Audit</h2>
        <?php echo $msg; ?>
    </div>

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Ejen</th>
                            <th>Laporan Audit</th>
                            <th>Nilai Hutang (Declared)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT r.*, a.full_name FROM Stock_requests r 
                                JOIN Agent a ON r.agent_id = a.agent_id 
                                WHERE r.status = 'Pending' ORDER BY r.request_date DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) == 0) echo "<tr><td colspan='4' class='text-center'>Tiada laporan.</td></tr>";

                        while ($row = mysqli_fetch_assoc($result)):
                            $req_id = $row['request_id'];
                        ?>
                        <tr>
                            <td><?php echo $row['full_name']; ?></td>
                            <td>
                                <ul class="mb-0">
                                <?php
                                $total = 0;
                                $q_item = mysqli_query($conn, "SELECT i.*, p.product_name FROM Stock_Request_items i JOIN Product p ON i.product_id = p.product_id WHERE i.request_id='$req_id'");
                                while ($item = mysqli_fetch_assoc($q_item)) {
                                    $sub = $item['quantity_requested'] * $item['price_at_request'];
                                    $total += $sub;
                                    echo "<li>" . $item['product_name'] . " (x" . $item['quantity_requested'] . ") <br><small class='text-muted'>Harga Lapor: RM " . $item['price_at_request'] . "/unit</small></li>";
                                }
                                ?>
                                </ul>
                            </td>
                            <td class="fw-bold text-danger">RM <?php echo number_format($total, 2); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="req_id" value="<?php echo $req_id; ?>">
                                    <input type="hidden" name="agent_id" value="<?php echo $row['agent_id']; ?>">
                                    <button name="approve_btn" class="btn btn-success btn-sm w-100 mb-1">Sah & Lulus</button>
                                    <button name="reject_btn" class="btn btn-danger btn-sm w-100">Tolak</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3 mb-5">
    <div class="col-md-12">
        <?php 
        // Kalau role dia admin, balik ke dashboard_admin.php
        // Kalau bukan (staff), balik ke dashboard_staff.php
        $back_link = ($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_staff.php'; 
        ?>
        
        <a href="<?php echo $back_link; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <?php if(basename($_SERVER['PHP_SELF']) == 'staff_monthly_report.php'): ?>
        <button onclick="window.print()" class="btn btn-primary float-end">
            <i class="fas fa-print"></i> Cetak / Save PDF
        </button>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

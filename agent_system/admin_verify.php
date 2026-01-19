<?php
include 'db.php';

// Cek Security: Hanya Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// LOGIK 1: ADMIN TEKAN SAHKAN (APPROVE)
if (isset($_POST['verify_btn'])) {
    $payment_id = $_POST['pay_id'];
    $agent_id = $_POST['agent_id'];
    $amount = $_POST['amount'];
    $admin_id = $_SESSION['user_id'];

    mysqli_begin_transaction($conn);
    try {
        // A. Update Status Payment jadi 'Approved'
        mysqli_query($conn, "UPDATE Payment SET status='Approved', verified_by_user_id='$admin_id' WHERE payment_id='$payment_id'");

        // B. Masukkan Rekod ke IOU LEDGER (CREDIT = Bayar Hutang)
        // Ini step paling penting supaya hutang ejen berkurang
        $desc = "Bayaran ID #" . $payment_id;
        $date = date('Y-m-d H:i:s');
        
        $sql_iou = "INSERT INTO IOU_Ledger (agent_id, transaction_type, amount, transaction_date, description, related_payment_id) 
                    VALUES ('$agent_id', 'CREDIT', '$amount', '$date', '$desc', '$payment_id')";
        
        if (!mysqli_query($conn, $sql_iou)) {
            throw new Exception("Gagal update lejar.");
        }

        mysqli_commit($conn);
        $msg = "<div class='alert alert-success'>Bayaran berjaya disahkan! Hutang ejen telah ditolak sebanyak RM $amount.</div>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// LOGIK 2: REJECT (Kalau resit palsu/kabur)
if (isset($_POST['reject_btn'])) {
    $payment_id = $_POST['pay_id'];
    $admin_id = $_SESSION['user_id'];
    mysqli_query($conn, "UPDATE Payment SET status='Rejected', verified_by_user_id='$admin_id' WHERE payment_id='$payment_id'");
    $msg = "<div class='alert alert-warning'>Bayaran ditolak. Hutang tidak berubah.</div>";
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-file-invoice-dollar"></i> Pengesahan Bayaran Ejen</h2>
        <p>Semak resit bayaran dan sahkan untuk tolak hutang ejen.</p>
        <?php echo $msg; ?>
    </div>

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Senarai Bayaran Menunggu (Pending)</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tarikh & Masa</th>
                            <th>Nama Ejen</th>
                            <th>Jumlah Bayaran</th>
                            <th>Bukti Resit</th>
                            <th>Tindakan Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Tarik payment status Pending
                        $sql = "SELECT p.*, a.full_name FROM Payment p 
                                JOIN Agent a ON p.agent_id = a.agent_id 
                                WHERE p.status = 'Pending' 
                                ORDER BY p.payment_date DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='5' class='text-center text-muted'>Tiada bayaran baru untuk disahkan.</td></tr>";
                        }

                        while ($row = mysqli_fetch_assoc($result)):
                            $pid = $row['payment_id'];
                            $img_path = "uploads/" . $row['proof_filepath'];
                        ?>
                        <tr>
                            <td>
                                <?php echo date('d/m/Y', strtotime($row['payment_date'])); ?><br>
                                <small class="text-muted">ID: #<?php echo $pid; ?></small>
                            </td>
                            <td class="fw-bold text-primary"><?php echo $row['full_name']; ?></td>
                            <td class="fw-bold text-success">RM <?php echo number_format($row['amount'], 2); ?></td>
                            
                            <td>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#modalResit<?php echo $pid; ?>">
                                    <img src="<?php echo $img_path; ?>" alt="Resit" class="img-thumbnail" style="height: 60px;">
                                    <br><small>Lihat Besar</small>
                                </a>

                                <div class="modal fade" id="modalResit<?php echo $pid; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Bukti Bayaran - <?php echo $row['full_name']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="<?php echo $img_path; ?>" class="img-fluid" alt="Resit Penuh">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <form method="POST" action="" onsubmit="return confirm('Adakah duit sudah masuk akaun bank? Sahkan?');">
                                    <input type="hidden" name="pay_id" value="<?php echo $pid; ?>">
                                    <input type="hidden" name="agent_id" value="<?php echo $row['agent_id']; ?>">
                                    <input type="hidden" name="amount" value="<?php echo $row['amount']; ?>">
                                    
                                    <button type="submit" name="verify_btn" class="btn btn-success btn-sm w-100 mb-1">
                                        <i class="fas fa-check"></i> Sahkan Duit Masuk
                                    </button>
                                </form>

                                <form method="POST" action="" onsubmit="return confirm('Tolak bayaran ini?');">
                                    <input type="hidden" name="pay_id" value="<?php echo $pid; ?>">
                                    <button type="submit" name="reject_btn" class="btn btn-danger btn-sm w-100">
                                        <i class="fas fa-times"></i> Tolak (Resit Palsu)
                                    </button>
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

<div class="row mt-3">
    <div class="col-md-12">
        <a href="dashboard_admin.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>
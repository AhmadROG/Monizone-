<?php
include 'db.php';

// Cek Security: Hanya Agent
if (!isset($_SESSION['agent_id'])) {
    header("Location: login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];
$msg = "";

// LOGIK: UPLOAD RESIT
if (isset($_POST['pay_btn'])) {
    $amount = (float) $_POST['amount'];
    $date = $_POST['payment_date'];
    
    // Proses Upload Gambar
    $target_dir = "uploads/";
    $file_name = basename($_FILES["receipt_img"]["name"]);
    // Kita tambah timestamp kat nama file supaya tak duplicate (cth: 12345_resit.jpg)
    $final_name = time() . "_" . $file_name;
    $target_file = $target_dir . $final_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Cek fail gambar ke tak
    $check = getimagesize($_FILES["receipt_img"]["tmp_name"]);
    if($check !== false) {
        // Cek saiz (max 5MB)
        if ($_FILES["receipt_img"]["size"] > 5000000) {
            $msg = "<div class='alert alert-danger'>Fail terlalu besar (Max 5MB).</div>";
            $uploadOk = 0;
        } else {
            // Cuba upload
            if (move_uploaded_file($_FILES["receipt_img"]["tmp_name"], $target_file)) {
                // Berjaya upload, simpan dalam database
                $sql = "INSERT INTO Payment (agent_id, payment_date, amount, proof_filepath, status) 
                        VALUES ('$agent_id', '$date', '$amount', '$final_name', 'Pending')";
                
                if (mysqli_query($conn, $sql)) {
                    $msg = "<div class='alert alert-success'>Bayaran berjaya dihantar! Tunggu Admin sahkan.</div>";
                } else {
                    $msg = "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>Gagal upload gambar. Sila cuba lagi.</div>";
            }
        }
    } else {
        $msg = "<div class='alert alert-danger'>Fail bukan gambar. Sila upload JPG/PNG.</div>";
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Bayar Hutang</h4>
            </div>
            <div class="card-body">
                
                <?php echo $msg; ?>
                <p class="text-muted">Sila buat pindahan wang ke akaun syarikat dan muat naik resit di sini.</p>
                
                <div class="alert alert-warning text-center">
                    <strong>Bank Islam: 1234 5678 9012</strong><br>
                    Nama: Syarikat ABC Sdn Bhd
                </div>

                <form method="POST" action="" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label>Jumlah Bayaran (RM)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label>Tarikh Bayaran</label>
                        <input type="date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label>Bukti Resit (Gambar)</label>
                        <input type="file" name="receipt_img" class="form-control" required accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, JPEG sahaja.</small>
                    </div>

                    <button type="submit" name="pay_btn" class="btn btn-success w-100">
                        <i class="fas fa-upload"></i> Hantar Resit
                    </button>
                    
                    <a href="dashboard_agent.php" class="btn btn-outline-secondary w-100 mt-2">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 mb-4">
    <div class="col-md-12">
        <a href="dashboard_agent.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>
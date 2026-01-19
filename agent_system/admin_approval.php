<?php
include 'db.php';

// Cek Security: HANYA ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// LOGIK 1: APPROVE (LULUSKAN)
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    // Tukar status jadi 'Active'
    mysqli_query($conn, "UPDATE Users SET status='Active' WHERE user_id='$id'");
    $msg = "<div class='alert alert-success'>Ejen berjaya diluluskan! Mereka kini boleh login.</div>";
}

// LOGIK 2: REJECT (TOLAK & PADAM)
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    // Padam terus dari database
    mysqli_query($conn, "DELETE FROM Users WHERE user_id='$id'");
    $msg = "<div class='alert alert-danger'>Permohonan ditolak. Data dipadam.</div>";
}

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-user-check"></i> Kelulusan Ejen Baru</h2>
        <p class="text-muted">Senarai permohonan pendaftaran yang menunggu kelulusan.</p>
        <?php if(isset($msg)) echo $msg; ?>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Senarai Menunggu (Pending)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Nama Penuh</th>
                        <th>Username</th>
                        <th>No. Telefon</th>
                        <th>Email</th>
                        <th>Tarikh Daftar</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Cari user yang role='agent' DAN status='Pending'
                    $sql = "SELECT * FROM Users WHERE role='agent' AND status='Pending' ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Format tarikh cantik sikit
                            $date = date('d/m/Y h:i A', strtotime($row['created_at']));
                            ?>
                            <tr>
                                <td><strong><?php echo $row['full_name']; ?></strong></td>
                                <td><?php echo $row['username']; ?></td>
                                <td class="text-center"><?php echo $row['phone']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td class="text-center small"><?php echo $date; ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="admin_approval.php?approve=<?php echo $row['user_id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Luluskan ejen ini?')">
                                            <i class="fas fa-check"></i> Lulus
                                        </a>
                                        <a href="admin_approval.php?reject=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak dan padam permohonan ini?')">
                                            <i class="fas fa-times"></i> Tolak
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class="text-center text-muted p-4"><i>Tiada permohonan baru buat masa ini.</i></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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
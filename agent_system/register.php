<?php
include 'db.php';
include 'header.php';

$message = "";

// Jika butang DAFTAR ditekan
if (isset($_POST['register_btn'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $password = $_POST['password'];
    $role = $_POST['role']; // admin, staff, atau agent

    // 1. Hash Password (Ini yang paling penting!)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 2. Tentukan nak simpan dalam table mana
    if ($role == 'agent') {
        // Simpan ke table AGENT
        // Kita letak IC & Phone kosong dulu (NULL) sebab ni quick register
        $sql = "INSERT INTO Agent (username, password, full_name) VALUES ('$username', '$hashed_password', '$full_name')";
    } else {
        // Simpan ke table USERS (Admin/Staff)
        $sql = "INSERT INTO Users (username, password, full_name, role) VALUES ('$username', '$hashed_password', '$full_name', '$role')";
    }

    // 3. Jalankan SQL
    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Pendaftaran Berjaya! Sila <a href="login.php">Log Masuk di sini</a>.</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal mendaftar: ' . mysqli_error($conn) . '</div>';
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h4><i class="fas fa-user-plus"></i> Daftar Akaun Baru</h4>
            </div>
            <div class="card-body">
                
                <?php echo $message; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="Cth: adminbaru">
                    </div>
                    
                    <div class="mb-3">
                        <label>Nama Penuh</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Cth: Ahmad Admin">
                    </div>

                    <div class="mb-3">
                        <label>Peranan (Role)</label>
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="staff">Staf</option>
                            <option value="agent">Ejen</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                    </div>

                    <button type="submit" name="register_btn" class="btn btn-success w-100">Daftar Sekarang</button>
                </form>
            </div>
            <div class="card-footer text-center">
                Sudah ada akaun? <a href="login.php">Log Masuk di sini</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
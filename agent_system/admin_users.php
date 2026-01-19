<?php
include 'db.php';

// Cek Security: Hanya Admin boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// LOGIK 1: Tambah User Baru
if (isset($_POST['add_user_btn'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $role = $_POST['role'];

    // Cek username dah wujud ke belum?
    $cek_user = mysqli_query($conn, "SELECT username FROM Users WHERE username='$username' UNION SELECT username FROM Agent WHERE username='$username'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        $alert = "<div class='alert alert-danger'>Username '$username' sudah digunakan! Sila guna nama lain.</div>";
    } else {
        if ($role == 'agent') {
            $phone = $_POST['phone_number'];
            $sql = "INSERT INTO Agent (username, password, full_name, phone_number) VALUES ('$username', '$password', '$full_name', '$phone')";
        } else {
            $sql = "INSERT INTO Users (username, password, full_name, role) VALUES ('$username', '$password', '$full_name', '$role')";
        }

        if (mysqli_query($conn, $sql)) {
            $alert = "<div class='alert alert-success'>Berjaya tambah pengguna baru!</div>";
        } else {
            $alert = "<div class='alert alert-danger'>Gagal: " . mysqli_error($conn) . "</div>";
        }
    }
}

// LOGIK 2: Padam User (Delete)
if (isset($_GET['delete_admin'])) {
    $id = $_GET['delete_admin'];
    mysqli_query($conn, "DELETE FROM Users WHERE user_id='$id'");
    header("Location: admin_users.php"); // Refresh page
}
if (isset($_GET['delete_agent'])) {
    $id = $_GET['delete_agent'];
    mysqli_query($conn, "DELETE FROM Agent WHERE agent_id='$id'");
    header("Location: admin_users.php");
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2><i class="fas fa-users-cog"></i> Pengurusan Pengguna</h2>
        <p>Tambah Staf atau Ejen baru ke dalam sistem.</p>
        <?php if(isset($alert)) echo $alert; ?>
    </div>

    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tambah Pengguna Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Username (Untuk Login)</label>
                        <input type="text" name="username" class="form-control" required placeholder="Cth: ejenali">
                    </div>
                    <div class="mb-3">
                        <label>Nama Penuh</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Cth: Ali Bin Abu">
                    </div>
                    <div class="mb-3">
                        <label>Peranan (Role)</label>
                        <select name="role" class="form-select" id="roleSelect" onchange="togglePhoneField()">
                            <option value="staff">Staf Inventori</option>
                            <option value="admin">Admin</option>
                            <option value="agent">Ejen Jualan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 d-none" id="phoneField">
                        <label>No. Telefon</label>
                        <input type="text" name="phone_number" class="form-control" placeholder="Cth: 0123456789">
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="*******">
                    </div>
                    <button type="submit" name="add_user_btn" class="btn btn-primary w-100">Simpan Pengguna</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Senarai Admin & Staf</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_users = mysqli_query($conn, "SELECT * FROM Users ORDER BY role ASC");
                        while($row = mysqli_fetch_assoc($q_users)):
                        ?>
                        <tr>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <span class="badge <?php echo ($row['role']=='admin') ? 'bg-danger':'bg-info'; ?>">
                                    <?php echo strtoupper($row['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['username'] != 'admin1'): // Elak padam diri sendiri ?>
                                <a href="admin_users.php?delete_admin=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Padam user ini?')">Padam</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Senarai Ejen Jualan</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>No. Tel</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_agent = mysqli_query($conn, "SELECT * FROM Agent ORDER BY agent_id DESC");
                        while($row = mysqli_fetch_assoc($q_agent)):
                        ?>
                        <tr>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['phone_number']; ?></td>
                            <td>
                                <a href="admin_users.php?delete_agent=<?php echo $row['agent_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Padam ejen ini?')">Padam</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Script simple untuk tunjuk field phone kalau pilih 'Agent'
function togglePhoneField() {
    var role = document.getElementById("roleSelect").value;
    var phoneDiv = document.getElementById("phoneField");
    if(role === 'agent'){
        phoneDiv.classList.remove('d-none');
    } else {
        phoneDiv.classList.add('d-none');
    }
}
</script>

<div class="row mt-3">
    <div class="col-md-12">
        <a href="dashboard_admin.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>

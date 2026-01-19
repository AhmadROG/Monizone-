<?php
// Paparkan error jika ada (untuk debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
include 'header.php';

$error = "";

// Jika butang LOGIN ditekan
if (isset($_POST['login_btn'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // 1. Cek dalam table USERS (Admin / Staff / Ejen Baru)
    $query_user = "SELECT * FROM Users WHERE username='$username'";
    $result_user = mysqli_query($conn, $query_user);

    if (mysqli_num_rows($result_user) > 0) {
        $row = mysqli_fetch_assoc($result_user);
        
        // A. Verify Password
        if (password_verify($password, $row['password'])) {
            
            // B. CEK STATUS (Ini yang baru tambah)
            if ($row['status'] == 'Pending') {
                $error = "Maaf, akaun anda sedang menunggu kelulusan Admin.";
            } else {
                // Kalau Active, baru set session
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['full_name'] = $row['full_name'];

                // C. Redirect ikut role (Admin / Staff / Agent)
                if ($row['role'] == 'admin') {
                    header("Location: dashboard_admin.php");
                } elseif ($row['role'] == 'staff') {
                    header("Location: dashboard_staff.php");
                } else {
                    // Kalau dia agent tapi duduk dalam table Users
                    header("Location: dashboard_agent.php");
                }
                exit();
            }

        } else {
            $error = "Password Salah!";
        }
    } 
    // 2. Kalau tak jumpa, Cek dalam table AGENT (Untuk Ejen Lama)
    else {
        $query_agent = "SELECT * FROM Agent WHERE username='$username'";
        $result_agent = mysqli_query($conn, $query_agent);

        if (mysqli_num_rows($result_agent) > 0) {
            $row = mysqli_fetch_assoc($result_agent);
            if (password_verify($password, $row['password'])) {
                // Set Session Agent Lama
                $_SESSION['agent_id'] = $row['agent_id'];
                $_SESSION['role'] = 'agent';
                $_SESSION['full_name'] = $row['full_name'];

                header("Location: dashboard_agent.php");
                exit();
            } else {
                $error = "Password Ejen Salah!";
            }
        } else {
            $error = "Username tidak dijumpai!";
        }
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4><i class="fas fa-lock"></i> Log Masuk Sistem</h4>
            </div>
            <div class="card-body">
                
                <?php if($error != ""): ?>
                    <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
                    </div>
                    
                    <div class="mb-3">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="passwordInput" class="form-control" required placeholder="Masukkan password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login_btn" class="btn btn-primary w-100">Masuk</button>
                </form>
            </div>
            <div class="card-footer text-center">
                Belum ada akaun? <a href="register.php">Daftar di sini</a>
                <br>
                <small class="text-muted">Sistem Pengurusan Stok & Ejen</small>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var input = document.getElementById("passwordInput");
    var icon = document.getElementById("toggleIcon");
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash"); 
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye"); 
    }
}
</script>

<?php include 'footer.php'; ?>
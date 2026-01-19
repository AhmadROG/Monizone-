<?php
include 'db.php';

// Cek Security: Benarkan Staff ATAU Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

// LOGIK: SIMPAN JUALAN DIRECT
if (isset($_POST['submit_sale'])) {
    $pid = $_POST['product_id'];
    $qty = (int) $_POST['qty'];
    $price = (float) $_POST['price']; // Harga Jualan (RM)
    $channel = $_POST['channel'];
    $staff_id = $_SESSION['user_id'];
    $total_sales = $qty * $price;

    // 1. Masukkan rekod jualan
    $sql = "INSERT INTO Direct_Sales (product_id, quantity, total_price, channel, recorded_by_staff_id) 
            VALUES ('$pid', '$qty', '$total_sales', '$channel', '$staff_id')";
    
    if (mysqli_query($conn, $sql)) {
        // 2. Tolak Stok Gudang
        mysqli_query($conn, "UPDATE Product SET warehouse_stock = warehouse_stock - $qty WHERE product_id='$pid'");
        $msg = "<div class='alert alert-success'>Jualan $channel direkodkan! Stok ditolak.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-cash-register"></i> Rekod Jualan Langsung (Direct)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Gunakan borang ini untuk jualan Walk-in, Shopee, TikTok, dll (Bukan Ejen).</p>
                <?php if(isset($msg)) echo $msg; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label>Pilih Produk</label>
                        <select name="product_id" class="form-select" required>
                            <?php
                            $q = mysqli_query($conn, "SELECT * FROM Product ORDER BY product_name ASC");
                            while($r = mysqli_fetch_assoc($q)) {
                                echo "<option value='".$r['product_id']."'>".$r['product_name']." (Stok: ".$r['warehouse_stock'].")</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Kuantiti</label>
                            <input type="number" name="qty" class="form-control" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Harga Jualan Seunit (RM)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Platform / Saluran</label>
                        <select name="channel" class="form-select">
                            <option value="Walk-in">Walk-in (Kedai)</option>
                            <option value="Shopee">Shopee</option>
                            <option value="TikTok">TikTok Shop</option>
                            <option value="Whatsapp">Whatsapp (HQ)</option>
                        </select>
                    </div>

                    <button type="submit" name="submit_sale" class="btn btn-warning w-100 fw-bold">Rekod & Tolak Stok</button>
                    <a href="dashboard_staff.php" class="btn btn-secondary w-100 mt-2">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
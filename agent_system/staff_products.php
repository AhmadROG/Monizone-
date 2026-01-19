<?php
include 'db.php';

// Cek Security: Benarkan Staff ATAU Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

// LOGIK 1: Tambah Produk Baru
if (isset($_POST['add_product_btn'])) {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $hq_stock = (int) $_POST['hq_stock'];
    $warehouse_stock = (int) $_POST['warehouse_stock'];

    $sql = "INSERT INTO Product (product_name, category, hq_stock, warehouse_stock, price) VALUES ('$name', '$category', '$hq_stock', '$warehouse_stock', '0')";
    if (mysqli_query($conn, $sql)) {
        $alert = "<div class='alert alert-success'>Produk berjaya didaftarkan!</div>";
    } else {
        $alert = "<div class='alert alert-danger'>Gagal: " . mysqli_error($conn) . "</div>";
    }
}

// LOGIK 2: TOP UP STOK HQ (Supplier -> HQ)
if (isset($_POST['topup_hq_btn'])) {
    $pid = $_POST['product_id'];
    $qty_in = (int) $_POST['topup_qty'];
    
    // Tambah stok ke HQ
    mysqli_query($conn, "UPDATE Product SET hq_stock = hq_stock + $qty_in WHERE product_id='$pid'");
    $alert = "<div class='alert alert-success'>Stok HQ berjaya ditambah sebanyak $qty_in unit!</div>";
}

// LOGIK 3: PINDAH STOK (HQ -> Gudang)
if (isset($_POST['restock_btn'])) {
    $pid = $_POST['product_id'];
    $qty_transfer = (int) $_POST['restock_qty'];
    
    // Cek stok HQ cukup tak
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT hq_stock FROM Product WHERE product_id='$pid'"));
    
    if ($cek['hq_stock'] >= $qty_transfer) {
        mysqli_query($conn, "UPDATE Product SET 
                             hq_stock = hq_stock - $qty_transfer, 
                             warehouse_stock = warehouse_stock + $qty_transfer 
                             WHERE product_id='$pid'");
        $alert = "<div class='alert alert-info'>Berjaya pindah $qty_transfer unit ke Gudang.</div>";
    } else {
        $alert = "<div class='alert alert-danger'>Gagal! Stok HQ tak cukup.</div>";
    }
}

// LOGIK 4: Padam Produk
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM Product WHERE product_id='$id'");
    header("Location: staff_products.php");
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2><i class="fas fa-boxes"></i> Pengurusan Stok & Bekalan (HQ)</h2>
        <p>Pantau stok di HQ (Master), stok di Gudang (Branch), dan urus pemindahan stok.</p>
        <?php if(isset($alert)) echo $alert; ?>
    </div>

    <div class="col-md-3">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-plus"></i> Daftar Produk Baru</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Nama Produk</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Kategori</label>
                        <select name="category" class="form-select">
                            <option value="Eyewear">Eyewear (Spek Mata)</option>
                            <option value="Gelang">Gelang (Pure Winners)</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Stok Awal HQ</label>
                        <input type="number" name="hq_stock" class="form-control" required placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label>Stok Awal Gudang</label>
                        <input type="number" name="warehouse_stock" class="form-control" required placeholder="0">
                    </div>
                    <button type="submit" name="add_product_btn" class="btn btn-primary w-100">Simpan Produk</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Senarai Inventori & Kawalan Stok</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm align-middle">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th rowspan="2" style="width: 25%;">Produk</th>
                                <th class="bg-info text-white" style="width: 20%;">Stok Induk (HQ)</th>
                                <th class="bg-success text-white" style="width: 20%;">Stok Gudang</th>
                                <th class="bg-warning text-dark" style="width: 25%;">Pindah Stok (HQ->Gudang)</th>
                                <th rowspan="2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM Product ORDER BY category ASC, product_name ASC";
                            $q_pro = mysqli_query($conn, $sql);
                            
                            while($row = mysqli_fetch_assoc($q_pro)):
                                // Warna amaran kalau stok sikit
                                $hq_color = ($row['hq_stock'] < 50) ? 'text-danger' : 'text-dark';
                                $wh_color = ($row['warehouse_stock'] < 10) ? 'text-danger' : 'text-success';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $row['product_name']; ?></strong><br>
                                    <small class="text-muted"><?php echo $row['category']; ?></small>
                                </td>
                                
                                <td class="bg-light text-center">
                                    <h5 class="fw-bold <?php echo $hq_color; ?> mb-1"><?php echo $row['hq_stock']; ?></h5>
                                    
                                    <form method="POST" class="d-flex justify-content-center mt-2">
                                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                        <div class="input-group input-group-sm" style="width: 100px;">
                                            <input type="number" name="topup_qty" class="form-control" placeholder="+" min="1" required>
                                            <button class="btn btn-outline-success" type="submit" name="topup_hq_btn" title="Tambah Stok dari Supplier">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>

                                <td class="text-center">
                                    <h4 class="fw-bold <?php echo $wh_color; ?>"><?php echo $row['warehouse_stock']; ?></h4>
                                    <small class="text-muted">Unit Sedia Ada</small>
                                </td>

                                <td class="text-center">
                                    <form method="POST" class="d-flex justify-content-center">
                                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                        <div class="input-group input-group-sm" style="width: 110px;">
                                            <input type="number" name="restock_qty" class="form-control" placeholder="Qty" min="1" required>
                                            <button class="btn btn-info text-white" type="submit" name="restock_btn" title="Ambil dari HQ">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </form>
                                    <small class="text-muted fst-italic">Ambil dari HQ</small>
                                </td>

                                <td class="text-center">
                                    <a href="staff_products.php?delete=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Padam produk ini? Data sales akan hilang!')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
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
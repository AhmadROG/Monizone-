<?php
include 'db.php';

if (!isset($_SESSION['agent_id'])) {
    header("Location: login.php");
    exit();
}
$agent_id = $_SESSION['agent_id'];
$msg = "";

// LOGIK: Hantar Order
if (isset($_POST['submit_order'])) {
    $quantities = $_POST['qty']; // Array Quantity
    $prices = $_POST['price_declared']; // Array Harga yang ejen isi
    
    $found_item = false;
    $date = date('Y-m-d H:i:s');
    
    // Create Header Order
    $sql_header = "INSERT INTO Stock_requests (agent_id, request_date, status) VALUES ('$agent_id', '$date', 'Pending')";
    
    if (mysqli_query($conn, $sql_header)) {
        $request_id = mysqli_insert_id($conn);

        foreach ($quantities as $product_id => $qty) {
            $qty = (int)$qty;
            
            // Ambil harga yang ejen taip, kalau tak isi kita anggap 0
            $declared_price = isset($prices[$product_id]) ? (float)$prices[$product_id] : 0;

            if ($qty > 0) {
                $found_item = true;
                // Simpan Quantity DAN Harga yang ejen declare
                $sql_item = "INSERT INTO Stock_Request_items (request_id, product_id, quantity_requested, price_at_request) 
                             VALUES ('$request_id', '$product_id', '$qty', '$declared_price')";
                mysqli_query($conn, $sql_item);
            }
        }

        if ($found_item) {
            $msg = "<div class='alert alert-success'>Laporan audit dihantar! Tunggu pengesahan staf.</div>";
        } else {
            mysqli_query($conn, "DELETE FROM Stock_requests WHERE request_id='$request_id'");
            $msg = "<div class='alert alert-warning'>Sila isi kuantiti sekurang-kurangnya satu barang.</div>";
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Borang Audit & Restock</h4>
            </div>
            <div class="card-body">
                <?php echo $msg; ?>
                <div class="alert alert-info">
                    <small><i class="fas fa-info-circle"></i> Sila masukkan jumlah unit yang terjual. Harga Hutang adalah harga kos yang anda perlu bayar kepada syarikat (Seunit).</small>
                </div>

                <form method="POST" action="">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Produk</th>
                                <th>Stok Gudang</th>
                                <th style="width: 150px;">Unit Terjual (Qty)</th>
                                <th style="width: 200px;">Harga Hutang Seunit (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($conn, "SELECT * FROM Product ORDER BY product_name ASC");
                            while ($row = mysqli_fetch_assoc($query)) {
                                $pid = $row['product_id'];
                                $stok = $row['warehouse_stock'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $row['product_name']; ?></strong><br>
                                        <small class="text-muted"><?php echo $row['category']; ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo $stok; ?></span></td>
                                    
                                    <td>
                                        <input type="number" name="qty[<?php echo $pid; ?>]" class="form-control text-center" min="0" placeholder="0">
                                    </td>
                                    
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">RM</span>
                                            <input type="number" step="0.01" name="price_declared[<?php echo $pid; ?>]" class="form-control" placeholder="0.00">
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                            }
                            ?>
                        </tbody>
                    </table>

                    <button type="submit" name="submit_order" class="btn btn-primary w-100 mt-3">Hantar Laporan Audit</button>
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
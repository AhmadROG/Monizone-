<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengurusan Ejen & IOU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; margin-bottom: 20px; }
        .navbar-brand { font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<?php 
// LOGIK NAVIGASI PINTAR
$dashboard_link = "#"; // Default link
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        $dashboard_link = "dashboard_admin.php";
    } elseif ($_SESSION['role'] == 'staff') {
        $dashboard_link = "dashboard_staff.php";
    } elseif ($_SESSION['role'] == 'agent') {
        $dashboard_link = "dashboard_agent.php";
    }
}
?>

<?php if (isset($_SESSION['user_id']) || isset($_SESSION['agent_id'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $dashboard_link; ?>">
        <i class="fas fa-home me-2"></i> Sistem IOU
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <span class="nav-link text-white me-3">
                Hai, <?php echo $_SESSION['full_name'] ?? 'User'; ?>
            </span>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-danger text-white px-3" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Log Keluar
            </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<?php endif; ?>

<div class="container">
<?php
session_start();
require 'function.php';

if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'user') {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['login']['user_id'];
$nama_user = $_SESSION['login']['name'];

// Query Data
$query = "SELECT bugs.*, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          WHERE bugs.user_id = $user_id
          ORDER BY bugs.created_at DESC";

$bugs = query($query);

// Hitung Statistik
$total_laporan = count($bugs);
$selesai = 0;
$proses = 0;
foreach ($bugs as $b) {
    if ($b['status'] == 'Resolved' || $b['status'] == 'Closed') $selesai++;
    if ($b['status'] == 'In Progress' || $b['status'] == 'Assigned') $proses++;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard User | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/skin.css">
    
    <style>
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: rgba(59, 130, 246, 0.2) !important; }
        .clickable-row a { position: relative; z-index: 2; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <span class="nav-link text-white mr-3">Halo, <b><?= $nama_user; ?></b></span>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <img src="assets/img/logo1.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">BugTracker</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="profil.php" class="nav-link">
              <i class="nav-icon fas fa-user"></i><p>Profil Saya</p>
            </a>
          </li>
          <li class="nav-header">AKSES</li>
          <li class="nav-item">
            <a href="auth/logout.php" class="nav-link bg-danger">
              <i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0 text-white font-weight-bold">Dashboard User</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box mb-3 bg-info elevation-2">
              <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Laporan</span>
                <span class="info-box-number"><?= $total_laporan; ?></span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box mb-3 bg-warning elevation-2">
              <span class="info-box-icon text-white"><i class="fas fa-sync-alt"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-white">Sedang Diproses</span>
                <span class="info-box-number text-white"><?= $proses; ?></span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box mb-3 bg-success elevation-2">
              <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Selesai</span>
                <span class="info-box-number"><?= $selesai; ?></span>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="card card-outline card-primary">
              <div class="card-header border-0">
                <h3 class="card-title text-white">Riwayat Laporan Saya</h3>
                <div class="card-tools">
                   <a href="form-bug.php" class="btn btn-sm btn-primary shadow-sm">
                      <i class="fas fa-plus mr-1"></i> Lapor Bug
                   </a>
                </div>
              </div>
              <div class="card-body table-responsive">
                <table id="example1" class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th width="5%">No</th>
                      <th>Judul Masalah</th>
                      <th>Kategori</th>
                      <th>Prioritas</th>
                      <th>Tanggal</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if(empty($bugs)) : ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat laporan.</td></tr>
                    <?php else : ?>
                        <?php $i = 1; foreach ($bugs as $row) : ?>
                        <tr class="clickable-row" onclick="window.location.href='detail-laporan.php?id=<?= $row['bug_id']; ?>'">
                          <td><?= $i++; ?></td>
                          <td><span class="font-weight-bold text-info"><?= htmlspecialchars($row['title']); ?></span></td>
                          <td><?= $row['category_name']; ?></td>
                          <td>
                            <?php if($row['priority_name'] == 'Critical'): ?><span class="badge badge-danger">Critical</span>
                            <?php elseif($row['priority_name'] == 'High'): ?><span class="badge badge-warning">High</span>
                            <?php else: ?><span class="badge badge-info"><?= $row['priority_name']; ?></span><?php endif; ?>
                          </td>
                          <td style="color: #ddd;"><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                          <td>
                            <?php 
                                $st = $row['status']; $badge = 'secondary';
                                if($st == 'Open') $badge = 'danger';
                                if($st == 'Assigned') $badge = 'info';
                                if($st == 'In Progress') $badge = 'primary';
                                if($st == 'Resolved') $badge = 'success';
                            ?>
                            <span class="badge badge-<?= $badge; ?>"><?= $st; ?></span>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
  
  <footer class="main-footer">
    <strong>Copyright &copy; 2026 BugTracker.</strong>
  </footer>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true, "autoWidth": false, "lengthChange": false, "searching": true, "pageLength": 5, "order": []
    });
  });
</script>
</body>
</html>
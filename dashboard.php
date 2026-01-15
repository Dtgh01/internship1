<?php
session_start();
require 'function.php';

// 1. CEK LOGIN & ROLE
if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}
if ($_SESSION['login']['role'] == 'admin') {
    header("Location: admin/index.php");
    exit;
}
if ($_SESSION['login']['role'] == 'developer') {
    header("Location: developer/index.php");
    exit;
}

$user_id = $_SESSION['login']['user_id'];
$nama_user = $_SESSION['login']['name'];

// 2. STATISTIK
$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE user_id = $user_id");
$total_lapor = mysqli_fetch_assoc($q1)['total'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE user_id = $user_id AND status = 'Resolved'");
$total_selesai = mysqli_fetch_assoc($q2)['total'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE user_id = $user_id AND status IN ('Assigned', 'In Progress', 'Testing')");
$total_proses = mysqli_fetch_assoc($q3)['total'];

// 3. RIWAYAT
$query = "SELECT bugs.*, categories.category_name 
          FROM bugs
          JOIN categories ON bugs.category_id = categories.category_id
          WHERE bugs.user_id = $user_id
          ORDER BY bugs.created_at DESC";
$history = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
  
  <style>
      /* Sedikit custom biar makin cakep */
      .bg-gradient-primary-custom {
          background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
      }
      .card-primary:not(.card-outline) > .card-header {
          background-color: #007bff;
      }
  </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark navbar-primary border-bottom-0">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link font-weight-bold text-white">Dashboard</span>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <span class="nav-link text-white">
            Halo, <b><?= $nama_user; ?></b>
        </span>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <span class="brand-text font-weight-bold px-3">BugTracker System</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard Saya</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="form-bug.php" class="nav-link">
              <i class="nav-icon fas fa-edit"></i>
              <p>Buat Laporan Baru</p>
            </a>
          </li>
          <li class="nav-header">AKUN</li>
          <li class="nav-item">
            <a href="profil.php" class="nav-link">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>Edit Profil</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="auth/logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Ringkasan Aktivitas</h1>
          </div>
          <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="form-bug.php" class="btn btn-primary shadow">
                    <i class="fas fa-plus mr-1"></i> Lapor Bug Baru
                </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= $total_lapor; ?></h3>
                <p>Total Laporan Saya</p>
              </div>
              <div class="icon">
                <i class="fas fa-folder-open"></i>
              </div>
            </div>
          </div>
          
          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><?= $total_proses; ?></h3>
                <p>Sedang Diproses</p>
              </div>
              <div class="icon">
                <i class="fas fa-sync-alt fa-spin"></i>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $total_selesai; ?></h3>
                <p>Laporan Selesai (Fix)</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            
            <div class="card card-primary shadow-sm">
              <div class="card-header">
                <h3 class="card-title text-white">
                    <i class="fas fa-history mr-1"></i> Riwayat Laporan Terakhir
                </h3>
              </div>
              
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead class="bg-light">
                    <tr>
                      <th width="5%">No</th>
                      <th>Tanggal</th>
                      <th>Judul Masalah</th>
                      <th>Kategori</th>
                      <th>Status</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($history)) : ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i>Belum ada data laporan.</i></td></tr>
                    <?php else : ?>
                        <?php $i=1; foreach ($history as $row) : ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <b><?= $row['title']; ?></b>
                            </td>
                            <td><?= $row['category_name']; ?></td>
                            <td>
                                <?php 
                                    $st = $row['status'];
                                    $badge = 'secondary'; 
                                    if($st == 'Open') $badge = 'danger';
                                    if($st == 'Assigned') $badge = 'info';
                                    if($st == 'In Progress') $badge = 'primary';
                                    if($st == 'Testing') $badge = 'warning';
                                    if($st == 'Resolved') $badge = 'success';
                                    if($st == 'Closed') $badge = 'dark';
                                ?>
                                <span class="badge badge-<?= $badge; ?> px-2"><?= $st; ?></span>
                            </td>
                            <td>
                                <a href="detail-laporan.php?id=<?= $row['bug_id']; ?>" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
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
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
<?php
session_start();
require '../function.php';

// 1. CEK LOGIN & ROLE DEVELOPER
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    header("Location: ../auth/login.php");
    exit;
}

$dev_id = $_SESSION['login']['user_id'];
$dev_name = $_SESSION['login']['name'];

// 2. HITUNG STATISTIK KERJAAN (Biar ada kotak warna-warni)
// Total Semua Tugas yang dikasih ke dia
$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE assigned_to = $dev_id");
$total_tugas = mysqli_fetch_assoc($q1)['total'];

// Tugas yang masih 'Gantung' (Assigned, In Progress, Testing)
$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE assigned_to = $dev_id AND status IN ('Assigned', 'In Progress', 'Testing')");
$pending_tugas = mysqli_fetch_assoc($q2)['total'];

// Tugas yang udah Beres (Resolved, Closed)
$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE assigned_to = $dev_id AND status IN ('Resolved', 'Closed')");
$selesai_tugas = mysqli_fetch_assoc($q3)['total'];


// 3. AMBIL DATA BUG (Tabel Utama)
$query = "SELECT bugs.*, categories.category_name, priorities.priority_name, users.name as pelapor
          FROM bugs
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          JOIN users ON bugs.user_id = users.user_id
          WHERE bugs.assigned_to = $dev_id
          ORDER BY FIELD(bugs.status, 'Assigned', 'In Progress', 'Testing', 'Resolved', 'Closed'), bugs.updated_at DESC";

$jobs = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Developer Workspace | BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  
  <style>
      /* CSS Tambahan Biar Header Tabel Biru */
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
        <span class="nav-link font-weight-bold text-white">Developer Workspace 👨‍💻</span>
      </li>
    </ul>
    
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <span class="nav-link text-white">
            Halo, <b><?= $dev_name; ?></b>
        </span>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <span class="brand-text font-weight-bold px-3">BugTracker Dev</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="index.php" class="nav-link active">
              <i class="nav-icon fas fa-laptop-code"></i>
              <p>Daftar Tugas</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../profil.php" class="nav-link">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>Edit Profil</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link">
              <i class="nav-icon fas fa-power-off text-danger"></i>
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
            <h1 class="m-0 text-dark">Antrian Pekerjaan</h1>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= $total_tugas; ?></h3>
                <p>Total Tugas Masuk</p>
              </div>
              <div class="icon">
                <i class="fas fa-tasks"></i>
              </div>
            </div>
          </div>
          
          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-warning">
              <div class="inner text-white">
                <h3><?= $pending_tugas; ?></h3>
                <p>Sedang Dikerjakan</p>
              </div>
              <div class="icon">
                <i class="fas fa-fire"></i>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $selesai_tugas; ?></h3>
                <p>Tugas Selesai (Fixed)</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-double"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            
            <div class="card card-primary shadow-sm">
              <div class="card-header">
                <h3 class="card-title text-white">
                    <i class="fas fa-list-ul mr-1"></i> List Bug Assigned
                </h3>
              </div>
              
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead class="bg-light">
                    <tr>
                      <th width="5%">ID</th>
                      <th>Masalah</th>
                      <th>Prioritas</th>
                      <th>Pelapor</th>
                      <th>Status</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($jobs)) : ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i>Belum ada tugas masuk. Santai dulu bro! 😎</i></td></tr>
                    <?php else : ?>
                        <?php foreach ($jobs as $job) : ?>
                        <tr>
                            <td>#<?= $job['bug_id']; ?></td>
                            <td>
                                <b><?= $job['title']; ?></b><br>
                                <small class="text-muted"><?= $job['category_name']; ?></small>
                            </td>
                            <td>
                                <?php if($job['priority_name'] == 'Critical') : ?>
                                    <span class="badge badge-danger">Critical 🔥</span>
                                <?php elseif($job['priority_name'] == 'High') : ?>
                                    <span class="badge badge-warning">High ⚠️</span>
                                <?php else : ?>
                                    <span class="badge badge-info"><?= $job['priority_name']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $job['pelapor']; ?></td>
                            <td>
                                <?php 
                                    $st = $job['status'];
                                    $badge = 'secondary';
                                    if($st == 'Assigned') $badge = 'info';
                                    if($st == 'In Progress') $badge = 'primary';
                                    if($st == 'Testing') $badge = 'warning';
                                    if($st == 'Resolved') $badge = 'success';
                                    if($st == 'Closed') $badge = 'dark';
                                ?>
                                <span class="badge badge-<?= $badge; ?> px-2"><?= $st; ?></span>
                            </td>
                            <td>
                                <a href="kerjaan.php?id=<?= $job['bug_id']; ?>" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-tools mr-1"></i> Proses
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
    </div>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2026 BugTracker.</strong> All rights reserved.
  </footer>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>
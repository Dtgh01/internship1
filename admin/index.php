<?php
session_start();
require '../function.php';

if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}

// LOGIKA HITUNG
$total_bug    = count(query("SELECT * FROM bugs"));
$total_open   = count(query("SELECT * FROM bugs WHERE status = 'Open'"));
$total_closed = count(query("SELECT * FROM bugs WHERE status = 'Resolved' OR status = 'Closed'"));
$total_users  = count(query("SELECT * FROM users WHERE role IN ('user', 'developer')"));

// AMBIL 5 DATA TERBARU
$recent_bugs = query("SELECT bugs.*, users.name as pelapor, priorities.priority_name 
                      FROM bugs 
                      JOIN users ON bugs.user_id = users.user_id
                      JOIN priorities ON bugs.priority_id = priorities.priority_id
                      ORDER BY bugs.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/skin.css">
    
    <style>
        /* Agar Widget & Tabel terasa 'Clickable' */
        .info-box { cursor: pointer; transition: transform 0.2s; }
        .info-box:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.3) !important; }
        
        .clickable-row { cursor: pointer; transition: background 0.2s; }
        .clickable-row:hover { background-color: rgba(59, 130, 246, 0.1) !important; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto"><li class="nav-item"><span class="nav-link text-white">Dashboard Admin</span></li></ul>
  </nav>

  <?php include 'templates/sidebar-home.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0 text-white font-weight-bold">Command Center</h1></div></div></div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          
          <div class="col-12 col-sm-6 col-md-3" onclick="window.location.href='data-bug.php'">
            <div class="info-box bg-dark border border-secondary shadow-sm">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-bug"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-muted">Total Laporan</span>
                <span class="info-box-number text-white"><?= $total_bug; ?></span>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3" onclick="window.location.href='data-bug.php?status=Open'">
            <div class="info-box bg-dark border border-secondary shadow-sm">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-envelope-open-text"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-muted">Perlu Tindakan (Open)</span>
                <span class="info-box-number text-white"><?= $total_open; ?></span>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3" onclick="window.location.href='data-bug.php?status=Resolved'">
            <div class="info-box bg-dark border border-secondary shadow-sm">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-muted">Selesai (Closed)</span>
                <span class="info-box-number text-white"><?= $total_closed; ?></span>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3" onclick="window.location.href='active-acc.php'">
            <div class="info-box bg-dark border border-secondary shadow-sm">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users text-white"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-muted">User Terdaftar</span>
                <span class="info-box-number text-white"><?= $total_users; ?></span>
              </div>
            </div>
          </div>

        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline bg-dark border-secondary">
                    <div class="card-header border-0">
                        <h3 class="card-title text-white font-weight-bold">Laporan Masuk Terbaru</h3>
                        <div class="card-tools">
                            <a href="data-bug.php" class="btn btn-tool text-white">Lihat Semua</a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-valign-middle text-white">
                            <thead>
                            <tr>
                                <th>Judul Masalah</th>
                                <th>Prioritas</th>
                                <th>Pelapor</th>
                                <th>Status</th>
                                <th class="text-right">Waktu</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($recent_bugs as $rb): ?>
                            <tr class="clickable-row" onclick="window.location.href='detail-bug.php?id=<?= $rb['bug_id']; ?>'">
                                <td>
                                    <span class="font-weight-bold text-info"><?= $rb['title']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ($rb['priority_name']=='Critical'?'danger':'info'); ?>">
                                        <?= $rb['priority_name']; ?>
                                    </span>
                                </td>
                                <td><?= $rb['pelapor']; ?></td>
                                <td>
                                    <span class="badge badge-<?= ($rb['status']=='Open'?'danger':'success'); ?>">
                                        <?= $rb['status']; ?>
                                    </span>
                                </td>
                                <td class="text-right text-muted">
                                    <small><i class="fas fa-clock mr-1"></i> <?= date('d M H:i', strtotime($rb['created_at'])); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong> &copy; 2026 BugTracker.</strong></footer>
</div>
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>
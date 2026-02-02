<?php
session_start();
require '../function.php';

// Cek Akses Developer
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    header("Location: ../auth/login.php"); exit;
}

$dev_id = $_SESSION['login']['user_id'];

// HITUNG STATISTIK (Hanya milik developer ini)
// 1. Tugas Baru Masuk (Assigned)
$new_tasks = count(query("SELECT * FROM bugs WHERE assigned_to = $dev_id AND status = 'Assigned'"));

// 2. Sedang Dikerjakan (In Progress)
$active_tasks = count(query("SELECT * FROM bugs WHERE assigned_to = $dev_id AND status = 'In Progress'"));

// 3. Selesai (Resolved/Closed)
$finished_tasks = count(query("SELECT * FROM bugs WHERE assigned_to = $dev_id AND (status = 'Resolved' OR status = 'Closed')"));

// AMBIL DAFTAR TUGAS AKTIF (Assigned & In Progress)
// Diurutkan berdasarkan Prioritas (Critical duluan) lalu Tanggal
$my_bugs = query("SELECT bugs.*, users.name as pelapor, priorities.priority_name, categories.category_name
                  FROM bugs 
                  JOIN users ON bugs.user_id = users.user_id
                  JOIN priorities ON bugs.priority_id = priorities.priority_id
                  JOIN categories ON bugs.category_id = categories.category_id
                  WHERE bugs.assigned_to = $dev_id 
                  AND bugs.status IN ('Assigned', 'In Progress')
                  ORDER BY bugs.priority_id DESC, bugs.updated_at ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Developer Dashboard | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/skin.css">
    
    <style>
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
    <ul class="navbar-nav ml-auto"><li class="nav-item"><span class="nav-link text-white">Dashboard Developer</span></li></ul>
  </nav>

  <?php include 'sidebar.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0 text-white font-weight-bold">My Workspace</h1></div></div></div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          
          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box bg-dark border border-secondary shadow-sm">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-circle"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-muted">Tugas Baru Masuk</span>
                <span class="info-box-number text-white text-lg"><?= $new_tasks; ?></span>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box bg-dark border border-secondary shadow-sm">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-laptop-code text-white"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-muted">Sedang Dikerjakan</span>
                <span class="info-box-number text-white text-lg"><?= $active_tasks; ?></span>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box bg-dark border border-secondary shadow-sm">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-double"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-muted">Berhasil Diselesaikan</span>
                <span class="info-box-number text-white text-lg"><?= $finished_tasks; ?></span>
              </div>
            </div>
          </div>

        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline bg-dark border-secondary">
                    <div class="card-header border-0">
                        <h3 class="card-title text-white font-weight-bold">
                            <i class="fas fa-list-ul mr-2"></i>Daftar Antrean Pekerjaan
                        </h3>
                    </div>
                    
                    <div class="card-body table-responsive p-0">
                        <?php if(empty($my_bugs)): ?>
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-coffee fa-3x mb-3"></i><br>
                                <h5>Tidak ada tugas aktif. Santai sejenak!</h5>
                            </div>
                        <?php else: ?>
                            <table class="table table-hover table-valign-middle text-white">
                                <thead>
                                <tr>
                                    <th>Prioritas</th>
                                    <th>Masalah</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($my_bugs as $bug): ?>
                                <tr class="clickable-row" onclick="window.location.href='kerjaan.php?id=<?= $bug['bug_id']; ?>'">
                                    <td>
                                        <span class="badge badge-<?= ($bug['priority_name']=='Critical'?'danger':'info'); ?>">
                                            <?= $bug['priority_name']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-white"><?= $bug['title']; ?></span><br>
                                        <small class="text-muted">Pelapor: <?= $bug['pelapor']; ?></small>
                                    </td>
                                    <td><?= $bug['category_name']; ?></td>
                                    <td>
                                        <?php 
                                            $st = $bug['status'];
                                            $col = ($st == 'Assigned') ? 'danger' : 'warning';
                                        ?>
                                        <span class="text-<?= $col; ?> font-weight-bold">
                                            <i class="fas fa-circle text-xs mr-1"></i> <?= $st; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="kerjaan.php?id=<?= $bug['bug_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-tools mr-1"></i> Perbaiki
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong>Copyright &copy; 2026 BugTracker.</strong></footer>
</div>
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>
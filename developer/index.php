<?php
// --- ERROR REPORTING ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../function.php';

// 1. CEK LOGIN & ROLE
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    header("Location: ../auth/login.php");
    exit;
}

$dev_id = $_SESSION['login']['user_id'];
$dev_name = $_SESSION['login']['name'];

// 2. LOGIKA DATA
// Fungsi hitung data
function hitung($conn, $query) {
    $res = mysqli_query($conn, $query);
    if($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        return $row['total'];
    }
    return 0;
}

$total_tugas   = hitung($conn, "SELECT COUNT(*) as total FROM bugs WHERE assigned_to = $dev_id");
$pending_tugas = hitung($conn, "SELECT COUNT(*) as total FROM bugs WHERE assigned_to = $dev_id AND status IN ('Assigned', 'In Progress', 'Testing')");
$selesai_tugas = hitung($conn, "SELECT COUNT(*) as total FROM bugs WHERE assigned_to = $dev_id AND status IN ('Resolved', 'Closed')");

// Query List Tugas
$query = "SELECT bugs.*, categories.category_name, priorities.priority_name, users.name as pelapor
          FROM bugs
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          JOIN users ON bugs.user_id = users.user_id
          WHERE bugs.assigned_to = $dev_id
          ORDER BY bugs.updated_at DESC";

$jobs = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Developer Dashboard | BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  
  <style>
      /* Custom Style untuk Header Card */
      .card-primary:not(.card-outline) > .card-header {
          background-color: #007bff;
      }
      /* Biar foto bulat sempurna */
      .user-image-circle {
          width: 35px;
          height: 35px;
          object-fit: cover;
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
        <span class="nav-link font-weight-bold">Developer Workspace 👨‍💻</span>
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
    <a href="#" class="brand-link text-center">
      <span class="brand-text font-weight-bold">BugTracker Dev</span>
    </a>

    <div class="sidebar">
      
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
           <?php 
            // Cek foto di session
            $foto_profil = (!empty($_SESSION['login']['foto'])) 
                           ? '../assets/uploads/'.$_SESSION['login']['foto'] 
                           : '../assets/dist/img/avatar5.png';
           ?>
          <img src="<?= $foto_profil; ?>" class="img-circle elevation-2 user-image-circle" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block font-weight-bold"><?= $dev_name; ?></a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          
          <li class="nav-header">MENU UTAMA</li>
          
          <li class="nav-item">
            <a href="index.php" class="nav-link active">
              <i class="nav-icon fas fa-laptop-code"></i>
              <p>Daftar Tugas</p>
            </a>
          </li>
          
          <li class="nav-item mt-1">
            <a href="../profil.php" class="nav-link bg-info">
              <i class="nav-icon fas fa-user-edit"></i>
              <p>Edit Profil</p>
            </a>
          </li>

          <li class="nav-header"></li>
          
          <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link bg-danger" onclick="return confirm('Yakin ingin keluar?');">
              <i class="nav-icon fas fa-sign-out-alt"></i>
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
            <h1 class="m-0 text-dark">Daftar Pekerjaan</h1>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-info shadow-sm">
              <div class="inner">
                <h3><?= $total_tugas; ?></h3>
                <p>Total Tugas</p>
              </div>
              <div class="icon">
                <i class="fas fa-inbox"></i>
              </div>
            </div>
          </div>
          
          <div class="col-12 col-sm-6 col-md-4">
            <div class="small-box bg-warning shadow-sm">
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
            <div class="small-box bg-success shadow-sm">
              <div class="inner">
                <h3><?= $selesai_tugas; ?></h3>
                <p>Selesai</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="card card-primary card-outline shadow-sm">
              <div class="card-header border-0">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-list-ul mr-1"></i> List Bug Assigned
                </h3>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                  <thead class="bg-light">
                    <tr>
                      <th width="5%" class="text-center">ID</th>
                      <th>Masalah / Bug</th>
                      <th>Prioritas</th>
                      <th>Status Saat Ini</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($jobs)) : ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-glass-cheers fa-3x mb-3 text-gray-300"></i><br>
                                <i>Belum ada tugas masuk.</i>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($jobs as $job) : ?>
                        <tr>
                            <td class="text-center align-middle">#<?= $job['bug_id']; ?></td>
                            <td class="align-middle">
                                <b class="text-primary"><?= $job['title']; ?></b><br>
                                <small class="text-muted">
                                    <i class="fas fa-tag mr-1"></i><?= $job['category_name']; ?> | 
                                    Pelapor: <?= $job['pelapor']; ?>
                                </small>
                            </td>
                            <td class="align-middle">
                                <?php if($job['priority_name'] == 'Critical') : ?>
                                    <span class="badge badge-danger px-2 py-1">Critical 🔥</span>
                                <?php elseif($job['priority_name'] == 'High') : ?>
                                    <span class="badge badge-warning px-2 py-1">High ⚠️</span>
                                <?php else : ?>
                                    <span class="badge badge-info px-2 py-1"><?= $job['priority_name']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-primary px-3 py-1"><?= $job['status']; ?></span>
                            </td>
                            <td class="align-middle">
                                <a href="kerjaan.php?id=<?= $job['bug_id']; ?>" class="btn btn-sm btn-success shadow-sm px-3 rounded-pill font-weight-bold">
                                    <i class="fas fa-tools mr-1"></i> PROSES
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
    <strong>Copyright &copy; 2026 BugTracker.</strong>
  </footer>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>
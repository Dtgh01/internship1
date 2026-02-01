<?php
session_start();
require 'function.php';

// 1. Cek Login
if (!isset($_SESSION['login'])) { header("Location: auth/login.php"); exit; }

$id_bug = $_GET['id'];

// 2. Query Data Utama Bug
$query = "SELECT bugs.*, 
          u_pelapor.name as pelapor, 
          u_dev.name as developer,
          categories.category_name, 
          priorities.priority_name 
          FROM bugs 
          JOIN users u_pelapor ON bugs.user_id = u_pelapor.user_id
          LEFT JOIN users u_dev ON bugs.assigned_to = u_dev.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          WHERE bug_id = $id_bug";

$data = query($query);
if (empty($data)) { echo "<script>window.location='dashboard.php';</script>"; exit; }
$bug = $data[0];

// Validasi Akses (User tidak boleh intip punya orang lain)
if ($_SESSION['login']['role'] == 'user' && $bug['user_id'] != $_SESSION['login']['user_id']) {
    echo "<script>window.location='dashboard.php';</script>"; exit;
}

// 3. QUERY HISTORY REAL-TIME
// Kita ambil dari tabel 'bug_status_history' yang sudah ada di sistem Admin
$q_history = "SELECT h.*, u.name as actor_name, u.role as actor_role 
              FROM bug_status_history h
              LEFT JOIN users u ON h.changed_by = u.user_id
              WHERE h.bug_id = $id_bug
              ORDER BY h.changed_at DESC"; // Urutkan dari TERBARU ke TERLAMA
$histories = query($q_history);

// Helper Cek Lampiran
$ext = pathinfo($bug['attachment'], PATHINFO_EXTENSION);
$is_image = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
$is_pdf   = in_array(strtolower($ext), ['pdf']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tracking Laporan | BugTracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/skin.css">
    
    <style>
        /* Styling Timeline agar Gelap & Elegan */
        .timeline > div > .timeline-item {
            background-color: rgba(30, 41, 59, 0.6); 
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #cbd5e1; box-shadow: none;
        }
        .timeline > div > .timeline-item > .timeline-header {
            color: #fff; border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.95rem; font-weight: 600;
        }
        .timeline > div > .timeline-item > .time { color: #94a3b8; font-size: 0.85rem;}
        .timeline:before { background-color: #334155; } /* Garis vertikal */
        
        .actor-highlight { color: #3b82f6; font-weight: 700; } /* Biru untuk nama orang */
        .status-badge { font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <img src="assets/img/logotrimhub.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">BugTracker</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="profil.php" class="nav-link"><i class="nav-icon fas fa-user"></i><p>Profil Saya</p></a></li>
          <li class="nav-header">AKSES</li>
          <li class="nav-item"><a href="auth/logout.php" class="nav-link bg-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Keluar</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0 text-white font-weight-bold">Detail & Tracking</h1></div>
          <div class="col-sm-6 text-right">
              <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
            
            <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title text-white font-weight-bold">#<?= $bug['bug_id']; ?> - <?= htmlspecialchars($bug['title']); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-4 border-right border-secondary">
                                <small class="text-muted d-block">Prioritas</small>
                                <span class="badge badge-<?= ($bug['priority_name']=='Critical'?'danger':'info'); ?>"><?= $bug['priority_name']; ?></span>
                            </div>
                            <div class="col-4 border-right border-secondary">
                                <small class="text-muted d-block">Developer</small>
                                <b class="text-white"><?= !empty($bug['developer']) ? $bug['developer'] : '-'; ?></b>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Status Akhir</small>
                                <b class="text-white"><?= $bug['status']; ?></b>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-primary font-weight-bold">Deskripsi Masalah</h6>
                            <p class="text-white bg-dark p-3 rounded" style="line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($bug['description'])); ?>
                            </p>
                        </div>
                        
                        <?php if(!empty($bug['attachment'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-paperclip mr-2"></i>Bukti Lampiran</h6>
                            <?php if($is_image): ?>
                                <div class="text-center bg-dark p-3 rounded">
                                    <img src="assets/uploads/<?= $bug['attachment']; ?>" class="img-fluid rounded" style="max-height: 400px;">
                                </div>
                            <?php elseif($is_pdf): ?>
                                <embed src="assets/uploads/<?= $bug['attachment']; ?>" type="application/pdf" width="100%" height="400px">
                            <?php else: ?>
                                <a href="assets/uploads/<?= $bug['attachment']; ?>" class="btn btn-default btn-block"><i class="fas fa-download mr-1"></i> Download File</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <h5 class="text-white mb-3"><i class="fas fa-history mr-2"></i>Riwayat Aktivitas</h5>
                
                <?php if(empty($histories)): ?>
                    <div class="alert alert-secondary">Belum ada riwayat aktivitas.</div>
                <?php else: ?>
                
                <div class="timeline">
                    <?php foreach($histories as $log): ?>
                        
                        <?php 
                            // Tentukan Icon & Warna berdasarkan Status Baru
                            $icon = 'fa-clock'; $bg = 'bg-gray';
                            
                            switch($log['new_status']) {
                                case 'Open':       $icon='fa-envelope'; $bg='bg-blue'; break;
                                case 'Assigned':   $icon='fa-user-check'; $bg='bg-warning'; break;
                                case 'In Progress':$icon='fa-tools'; $bg='bg-orange'; break;
                                case 'Testing':    $icon='fa-vial'; $bg='bg-purple'; break;
                                case 'Resolved':   $icon='fa-check-double'; $bg='bg-green'; break;
                                case 'Closed':     $icon='fa-lock'; $bg='bg-secondary'; break;
                            }
                        ?>

                        <div>
                            <i class="fas <?= $icon; ?> <?= $bg; ?>"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> <?= date('d M Y, H:i', strtotime($log['changed_at'])); ?></span>
                                
                                <h3 class="timeline-header">
                                    <span class="actor-highlight"><?= $log['actor_name']; ?></span> 
                                    <span class="text-muted text-sm">(<?= $log['actor_role']; ?>)</span>
                                </h3>

                                <div class="timeline-body">
                                    <?php if(empty($log['old_status'])): ?>
                                        Membuat laporan bug baru dengan status <b class="text-primary">OPEN</b>.
                                    <?php else: ?>
                                        Mengubah status dari <span class="text-muted"><?= $log['old_status']; ?></span> 
                                        menjadi <span class="<?= ($log['new_status']=='Resolved'?'text-success':'text-warning'); ?> font-weight-bold"><?= $log['new_status']; ?></span>.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                    
                    <div>
                        <i class="fas fa-history bg-gray"></i>
                    </div>
                </div>
                
                <?php endif; ?>
            </div>

        </div>
      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong>Copyright &copy; 2026 BugTracker.</strong></footer>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
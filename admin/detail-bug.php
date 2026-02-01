<?php
session_start();
require '../function.php';

// Cek Akses
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}

$id = $_GET['id'];

// 1. PROSES UPDATE (ASSIGN DEV & UBAH STATUS)
if (isset($_POST['update_bug'])) {
    $assigned_to = $_POST['assigned_to']; // ID Developer
    $new_status  = $_POST['status'];
    $old_status  = $_POST['old_status'];
    $admin_id    = $_SESSION['login']['user_id'];

    // Update Tabel Bugs
    // Jika assigned_to kosong (belum dipilih), set NULL
    $assign_sql = empty($assigned_to) ? "NULL" : "'$assigned_to'";
    
    $query = "UPDATE bugs SET 
              assigned_to = $assign_sql, 
              status = '$new_status', 
              updated_at = NOW() 
              WHERE bug_id = $id";
    
    mysqli_query($conn, $query);

    // Cek apakah Status Berubah? Jika ya, catat di History
    if ($new_status != $old_status) {
        $log_query = "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_at, changed_by) 
                      VALUES ('$id', '$old_status', '$new_status', NOW(), '$admin_id')";
        mysqli_query($conn, $log_query);
    }

    echo "<script>alert('Laporan berhasil diperbarui!'); window.location='detail-bug.php?id=$id';</script>";
}

// 2. AMBIL DATA BUG LENGKAP
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
          WHERE bug_id = $id";
$bug = query($query)[0];

// 3. AMBIL DATA HISTORY
$histories = query("SELECT h.*, u.name as actor, u.role as actor_role 
                    FROM bug_status_history h
                    JOIN users u ON h.changed_by = u.user_id
                    WHERE bug_id = $id 
                    ORDER BY changed_at DESC");

// 4. AMBIL LIST DEVELOPER (Untuk Dropdown)
$developers = query("SELECT * FROM users WHERE role = 'developer'");

// Helper File
$ext = pathinfo($bug['attachment'], PATHINFO_EXTENSION);
$is_image = in_array(strtolower($ext), ['jpg', 'jpeg', 'png']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Kontrol | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/skin.css">
    
    <style>
        .timeline-item { background: rgba(30, 41, 59, 0.6) !important; border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .timeline-header { color: #fff !important; border-bottom: 1px solid rgba(255,255,255,0.1) !important; }
        .timeline-item > .time { color: #94a3b8 !important; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
  </nav>

  <?php include 'templates/sidebar-home.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0 text-white font-weight-bold">Control Room #<?= $bug['bug_id']; ?></h1></div>
          <div class="col-sm-6 text-right">
              <a href="data-bug.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
            
            <div class="col-md-8">
                
                <div class="card card-primary card-outline shadow-lg mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-white font-weight-bold"><i class="fas fa-cogs mr-2"></i>Tindakan Admin</h3>
                    </div>
                    <form action="" method="POST">
                        <div class="card-body">
                            <input type="hidden" name="old_status" value="<?= $bug['status']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-white">Assign Developer (Tugaskan Ke)</label>
                                        <select name="assigned_to" class="form-control bg-dark border-secondary text-white">
                                            <option value="">-- Belum Ditunjuk --</option>
                                            <?php foreach($developers as $dev): ?>
                                                <option value="<?= $dev['user_id']; ?>" <?= ($bug['assigned_to'] == $dev['user_id']) ? 'selected' : ''; ?>>
                                                    <?= $dev['name']; ?> (Dev)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-white">Update Status</label>
                                        <select name="status" class="form-control bg-dark border-secondary text-white">
                                            <option value="Open" <?= ($bug['status']=='Open')?'selected':''; ?>>Open (Baru)</option>
                                            <option value="Assigned" <?= ($bug['status']=='Assigned')?'selected':''; ?>>Assigned (Diterima)</option>
                                            <option value="In Progress" <?= ($bug['status']=='In Progress')?'selected':''; ?>>In Progress (Dikerjakan)</option>
                                            <option value="Resolved" <?= ($bug['status']=='Resolved')?'selected':''; ?>>Resolved (Selesai)</option>
                                            <option value="Closed" <?= ($bug['status']=='Closed')?'selected':''; ?>>Closed (Tutup Tiket)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <button type="submit" name="update_bug" class="btn btn-primary font-weight-bold btn-block">
                                <i class="fas fa-save mr-2"></i> SIMPAN PERUBAHAN & UPDATE HISTORY
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card card-dark card-outline">
                    <div class="card-header">
                        <h3 class="card-title text-white font-weight-bold"><?= $bug['title']; ?></h3>
                    </div>
                    <div class="card-body">
                         <div class="row mb-3 text-muted">
                            <div class="col-4">Pelapor: <b class="text-white"><?= $bug['pelapor']; ?></b></div>
                            <div class="col-4">Kategori: <b class="text-white"><?= $bug['category_name']; ?></b></div>
                            <div class="col-4">Prioritas: <span class="badge badge-<?= ($bug['priority_name']=='Critical'?'danger':'info'); ?>"><?= $bug['priority_name']; ?></span></div>
                        </div>
                        
                        <div class="p-3 mb-3 rounded" style="background: rgba(255,255,255,0.05);">
                            <label class="text-info">Deskripsi:</label>
                            <p class="text-white mb-0"><?= nl2br($bug['description']); ?></p>
                        </div>

                        <?php if(!empty($bug['attachment'])): ?>
                            <label class="text-info">Bukti Lampiran:</label><br>
                            <?php if($is_image): ?>
                                <img src="../assets/uploads/<?= $bug['attachment']; ?>" class="img-fluid rounded border border-secondary" style="max-height: 400px;">
                            <?php else: ?>
                                <a href="../assets/uploads/<?= $bug['attachment']; ?>" class="btn btn-default" target="_blank"><i class="fas fa-file-pdf mr-1"></i> Lihat File PDF</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <h5 class="text-white mb-3"><i class="fas fa-history mr-2"></i>Riwayat Tracking</h5>
                <div class="timeline">
                    <?php foreach($histories as $log): ?>
                        <div>
                            <i class="fas fa-circle bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> <?= date('d M H:i', strtotime($log['changed_at'])); ?></span>
                                <h3 class="timeline-header">
                                    <span class="text-primary font-weight-bold"><?= $log['actor']; ?></span> 
                                    <small>(<?= $log['actor_role']; ?>)</small>
                                </h3>
                                <div class="timeline-body">
                                    <?php if(empty($log['old_status'])): ?>
                                        Membuat Laporan Baru
                                    <?php else: ?>
                                        Mengubah status: <br>
                                        <del class="text-muted"><?= $log['old_status']; ?></del> 
                                        <i class="fas fa-arrow-right mx-1 text-xs"></i> 
                                        <b class="text-success"><?= $log['new_status']; ?></b>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div><i class="fas fa-clock bg-gray"></i></div>
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
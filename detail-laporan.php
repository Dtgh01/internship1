<?php
session_start();
require 'function.php';

if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

$id_bug = (int)$_GET['id'];
$my_id  = $_SESSION['login']['user_id'];

// Ambil Data Bug (Pastikan punya user sendiri)
$query = "SELECT bugs.*, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          WHERE bugs.bug_id = $id_bug AND bugs.user_id = $my_id";
$bug = query($query);

if (empty($bug)) {
    echo "<script>alert('Data tidak ditemukan atau bukan milik Anda!'); window.location='dashboard.php';</script>";
    exit;
}
$bug = $bug[0];

// Ambil History (Timeline)
$histories = query("SELECT h.*, u.name as pengubah 
                    FROM bug_status_history h
                    JOIN users u ON h.changed_by = u.user_id
                    WHERE h.bug_id = $id_bug 
                    ORDER BY h.changed_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Laporan #<?= $id_bug; ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm">
    <div class="container">
      <span class="navbar-brand font-weight-bold">BugTracker User</span>
      <div class="ml-auto">
          <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
      </div>
    </div>
  </nav>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container">
        <h1 class="m-0">Detail Laporan <small>#<?= $bug['bug_id']; ?></small></h1>
      </div>
    </div>

    <div class="content">
      <div class="container">
        <div class="row">
            
            <div class="col-md-7">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><?= $bug['title']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Kategori:</small><br>
                                <b><?= $bug['category_name']; ?></b>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Prioritas:</small><br>
                                <span class="badge badge-warning"><?= $bug['priority_name']; ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Deskripsi:</small>
                            <div class="p-3 bg-light rounded border">
                                <?= nl2br($bug['description']); ?>
                            </div>
                        </div>
                        <?php if($bug['attachment']) : ?>
                            <hr>
                            <small class="text-muted">Bukti Lampiran:</small><br>
                            <a href="assets/uploads/<?= $bug['attachment']; ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-1">
                                <i class="fas fa-image"></i> Lihat Gambar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card card-info card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Jejak Penanganan</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Status / Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($histories as $h): ?>
                                <tr>
                                    <td>
                                        <small><?= date('d M, H:i', strtotime($h['changed_at'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-primary"><?= $h['new_status']; ?></span>
                                        <br>
                                        <small class="text-muted">oleh: <?= $h['pengubah']; ?></small>
                                        </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($histories)): ?>
                                    <tr><td colspan="2" class="text-center text-muted">Laporan baru dibuat.</td></tr>
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

  <footer class="main-footer text-center">
    <strong>Copyright &copy; 2026 BugTracker.</strong>
  </footer>
</div>
</body>
</html>
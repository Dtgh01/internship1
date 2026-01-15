<?php
session_start();
require '../function.php';

// 1. CEK LOGIN & ROLE
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int)$_GET['id'];
$dev_id = $_SESSION['login']['user_id'];
$dev_name = $_SESSION['login']['name'];

// 2. AMBIL DATA BUG + EMAIL PELAPOR
$q = "SELECT bugs.*, 
             users.email as email_pelapor, 
             users.name as nama_pelapor,
             categories.category_name, 
             priorities.priority_name
      FROM bugs 
      JOIN users ON bugs.user_id = users.user_id
      JOIN categories ON bugs.category_id = categories.category_id
      JOIN priorities ON bugs.priority_id = priorities.priority_id
      WHERE bug_id = $id AND assigned_to = $dev_id";

$bug_data = query($q);

if (empty($bug_data)) {
    echo "<script>alert('Tugas tidak ditemukan atau bukan jatah Anda!'); window.location='index.php';</script>";
    exit;
}
$bug = $bug_data[0];

// 3. PROSES UPDATE STATUS
if (isset($_POST['update_status'])) {
    $status_baru = $_POST['status'];
    $catatan     = htmlspecialchars($_POST['catatan']); 
    
    // A. Update Status di Database
    mysqli_query($conn, "UPDATE bugs SET status = '$status_baru', updated_at = NOW() WHERE bug_id = $id");

    // B. Catat History
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) 
                         VALUES ($id, '{$bug['status']}', '$status_baru', $dev_id)");

    // C. KIRIM NOTIFIKASI EMAIL KE PELAPOR
    $subjek = "[BugTracker] Update Status Bug #$id";
    $pesan  = "Halo {$bug['nama_pelapor']},\n\nStatus laporan Anda '{$bug['title']}' telah diperbarui menjadi: $status_baru.\n\nCatatan Developer:\n$catatan\n\nSilakan cek detail di dashboard Anda.\nTerima kasih.";
    
    kirimNotifikasi($bug['email_pelapor'], $subjek, $pesan);

    echo "<script>alert('Status berhasil diupdate & Notifikasi dikirim ke Pelapor!'); window.location='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Proses Bug #<?= $id; ?> | BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  
  <style>
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
        <span class="nav-link font-weight-bold text-white">Form Penanganan Masalah</span>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <span class="brand-text font-weight-bold px-3">BugTracker Dev</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item">
            <a href="index.php" class="nav-link active">
              <i class="nav-icon fas fa-arrow-left"></i>
              <p>Kembali ke List</p>
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
            <h1 class="m-0">Penanganan Masalah</h1>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          
          <div class="col-md-7">
            <div class="card card-info card-outline shadow-sm">
              <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-bug mr-1"></i> Detail Laporan #<?= $bug['bug_id']; ?>
                </h3>
              </div>
              <div class="card-body">
                <dl class="row">
                  <dt class="col-sm-4">Judul Masalah</dt>
                  <dd class="col-sm-8"><?= $bug['title']; ?></dd>

                  <dt class="col-sm-4">Kategori</dt>
                  <dd class="col-sm-8"><?= $bug['category_name']; ?></dd>

                  <dt class="col-sm-4">Prioritas</dt>
                  <dd class="col-sm-8">
                      <?php if($bug['priority_name'] == 'Critical'): ?>
                          <span class="badge badge-danger">Critical 🔥</span>
                      <?php else: ?>
                          <span class="badge badge-warning"><?= $bug['priority_name']; ?></span>
                      <?php endif; ?>
                  </dd>

                  <dt class="col-sm-4">Pelapor</dt>
                  <dd class="col-sm-8"><?= $bug['nama_pelapor']; ?></dd>

                  <dt class="col-sm-4">Deskripsi</dt>
                  <dd class="col-sm-8"><?= nl2br($bug['description']); ?></dd>
                </dl>

                <hr>
                <h5 class="text-info"><i class="fas fa-paperclip"></i> Bukti Lampiran</h5>
                <?php if($bug['attachment']) : ?>
                    <div class="mt-3">
                        <img src="../assets/uploads/<?= $bug['attachment']; ?>" class="img-fluid border rounded shadow-sm" style="max-height: 300px;">
                        <br>
                        <a href="../assets/uploads/<?= $bug['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-info mt-2">
                            <i class="fas fa-expand"></i> Lihat Ukuran Penuh
                        </a>
                    </div>
                <?php else : ?>
                    <p class="text-muted font-italic">Tidak ada lampiran gambar.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-md-5">
            <div class="card card-primary shadow-sm">
              <div class="card-header">
                <h3 class="card-title text-white">
                    <i class="fas fa-edit mr-1"></i> Update Progress
                </h3>
              </div>
              <div class="card-body">
                <form method="POST">
                    
                    <div class="form-group">
                        <label>Status Saat Ini:</label>
                        <input type="text" class="form-control" value="<?= $bug['status']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Ubah Status Menjadi:</label>
                        <select name="status" class="form-control" required>
                            <option value="In Progress" <?= ($bug['status'] == 'In Progress') ? 'selected' : ''; ?>>
                                 In Progress (Sedang Dikerjakan)
                            </option>
                            <option value="Testing" <?= ($bug['status'] == 'Testing') ? 'selected' : ''; ?>>
                                 Testing (Sudah Fix, Perlu Validasi)
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Catatan Teknis (Wajib):</label>
                        <textarea name="catatan" class="form-control" rows="5" placeholder="Contoh: Bug fixed pada line 45, sudah ditest di local aman..." required></textarea>
                        <small class="text-muted">*Catatan ini akan dikirim via email ke admin dan pelapor.</small>
                    </div>

                    <button type="submit" name="update_status" class="btn btn-primary btn-block font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </form>
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
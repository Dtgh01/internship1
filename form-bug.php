<?php
session_start();
require 'function.php';

// Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil Data user buat Sidebar
$nama_user = $_SESSION['login']['name'];

// 1. Ambil Data Kategori & Prioritas buat Dropdown
$categories = query("SELECT * FROM categories");
$priorities = query("SELECT * FROM priorities");

// 2. Logika Submit Laporan
if (isset($_POST['submit'])) {
    // Panggil fungsi insertPengaduan di function.php
    if (insertPengaduan($_POST, $_FILES) > 0) {
        echo "<script>
                alert('Laporan berhasil dikirim! Tim kami akan segera mengeceknya.');
                document.location.href = 'dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal mengirim laporan! Pastikan semua data terisi atau ukuran gambar tidak terlalu besar.');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lapor Bug Baru | BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <span class="brand-text font-weight-bold px-3">BugTracker User</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard Saya</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="form-bug.php" class="nav-link active">
              <i class="nav-icon fas fa-plus-circle"></i>
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
            <h1 class="m-0">Form Pelaporan Bug</h1>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-md-8">
            
            <div class="card card-primary shadow-sm">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Isi Detail Masalah</h3>
              </div>
              
              <form action="" method="POST" enctype="multipart/form-data">
                <div class="card-body">
                  
                  <div class="form-group">
                    <label>Judul Masalah <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Tombol Login Tidak Bisa Diklik..." required>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?= $cat['category_id']; ?>"><?= $cat['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Prioritas / Urgensi <span class="text-danger">*</span></label>
                            <select name="priority_id" class="form-control" required>
                                <option value="">-- Seberapa Mendesak? --</option>
                                <?php foreach ($priorities as $prio) : ?>
                                    <option value="<?= $prio['priority_id']; ?>"><?= $prio['priority_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Deskripsi Lengkap <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Jelaskan kronologi kejadian, pesan error yang muncul, atau langkah-langkah mereproduksi bug tersebut..." required></textarea>
                  </div>

                  <div class="form-group">
                    <label for="exampleInputFile">Bukti Screenshot (Opsional)</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" name="attachment" class="custom-file-input" id="exampleInputFile">
                        <label class="custom-file-label" for="exampleInputFile">Pilih file gambar...</label>
                      </div>
                    </div>
                    <small class="text-muted">Format: JPG, PNG, PDF. Maks: 2MB.</small>
                  </div>

                </div>
                <div class="card-footer">
                  <button type="submit" name="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane mr-1"></i> Kirim Laporan</button>
                  <a href="dashboard.php" class="btn btn-secondary float-right">Batal</a>
                </div>
              </form>
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

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
<script>
$(function () {
  bsCustomFileInput.init();
});
</script>
</body>
</html>
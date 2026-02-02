<?php
session_start();
require 'function.php';

// 1. Cek Login User
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'user') {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['login']['user_id'];
$nama_user = $_SESSION['login']['name'];

// 2. PROSES INPUT DATA
if (isset($_POST['lapor'])) {
    if (tambahBug($_POST) > 0) {
        echo "<script>
                alert('Laporan berhasil dikirim! Menunggu verifikasi Admin.');
                document.location.href = 'dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal mengirim laporan!');
              </script>";
    }
}

// Ambil Data Kategori & Prioritas
$categories = query("SELECT * FROM categories");
$priorities = query("SELECT * FROM priorities");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Laporan | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/skin.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><span class="nav-link text-white mr-3">Halo, <b><?= $nama_user; ?></b></span></li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <img src="assets/img/logo1.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">BugTracker</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="profil.php" class="nav-link"><i class="nav-icon fas fa-user"></i><p>Profil Saya</p></a></li>
          <li class="nav-header">AKSES</li>
          <li class="nav-item"><a href="auth/logout.php" class="nav-link bg-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0 text-white font-weight-bold">Form Laporan Bug</h1></div>
          <div class="col-sm-6 text-right"><a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Batal</a></div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row justify-content-center">
            
            <div class="col-md-8">
                <div class="card card-primary card-outline shadow-lg">
                    <div class="card-header">
                        <h3 class="card-title text-white font-weight-bold"><i class="fas fa-edit mr-2"></i> Isi Detail Masalah</h3>
                    </div>
                    
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="card-body">
                            
                            <input type="hidden" name="user_id" value="<?= $user_id; ?>">

                            <div class="form-group">
                                <label class="text-white">Judul Masalah <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Contoh: Error saat login..." required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-white">Kategori <span class="text-danger">*</span></label>
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
                                        <label class="text-white">Prioritas <span class="text-danger">*</span></label>
                                        <select name="priority_id" class="form-control" required>
                                            <option value="">-- Pilih Prioritas --</option>
                                            <?php foreach ($priorities as $prio) : ?>
                                                <option value="<?= $prio['priority_id']; ?>"><?= $prio['priority_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-white">Deskripsi Detail <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5" placeholder="Jelaskan kronologi error secara rinci..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label class="text-white">Bukti Lampiran</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" name="attachment" class="custom-file-input" id="fileInput" 
                                               accept="image/*,application/pdf" onchange="previewImage(this)">
                                        <label class="custom-file-label" for="fileInput">Pilih file (JPG/PNG/PDF)...</label>
                                    </div>
                                </div>

                                <div id="previewBox" style="display: none;" class="mt-3 text-center bg-dark p-3 rounded border border-secondary">
                                    <span class="d-block text-muted small mb-2">Preview Gambar:</span>
                                    <img id="imgPreview" src="#" alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                                </div>
                                
                                <small class="text-muted d-block mt-2">Maksimal ukuran file 2MB.</small>
                            </div>
                            
                        </div>

                        <div class="card-footer bg-transparent border-top-0 text-right pb-4 pr-4">
                            <button type="submit" name="lapor" class="btn btn-primary font-weight-bold px-4">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Laporan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong>Copyright &copy; 2026 BugTracker.</strong></footer>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>

<script>
// Init Nama File
$(function () {
  bsCustomFileInput.init();
});

// FUNGSI PREVIEW IMAGE (Langsung dipanggil dari HTML)
function previewImage(input) {
    var previewBox = document.getElementById('previewBox');
    var imgPreview = document.getElementById('imgPreview');

    if (input.files && input.files[0]) {
        var file = input.files[0];
        
        // Cek apakah file gambar
        if (file.type.match('image.*')) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                previewBox.style.display = 'block'; // Paksa Muncul
            }
            
            reader.readAsDataURL(file);
        } else {
            // Jika PDF atau lainnya, sembunyikan preview gambar
            previewBox.style.display = 'none';
        }
    } else {
        previewBox.style.display = 'none';
    }
}
</script>
</body>
</html>
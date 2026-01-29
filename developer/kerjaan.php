<?php
// --- NYALAKAN DETEKSI ERROR BIAR GAK BLANK ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../function.php';

// 1. CEK AKSES
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    echo "<script>alert('Akses Ditolak!'); window.location='../auth/login.php';</script>";
    exit;
}

// 2. CEK ID BUG
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Pilih tugas dulu dari daftar!'); window.location='index.php';</script>";
    exit;
}

$id = (int)$_GET['id'];
$dev_id = $_SESSION['login']['user_id'];

// 3. AMBIL DATA BUG
$query = "SELECT bugs.*, users.name as nama_pelapor, users.email as email_pelapor,
          categories.category_name, priorities.priority_name 
          FROM bugs 
          JOIN users ON bugs.user_id = users.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          WHERE bug_id = $id";

$result = mysqli_query($conn, $query);
$bug = mysqli_fetch_assoc($result);

// Validasi Data
if (!$bug) {
    die("Tugas tidak ditemukan!");
}
if ($bug['assigned_to'] != $dev_id) {
    die("Tugas ini bukan jatah Anda!");
}

// 4. PROSES UPDATE
if (isset($_POST['update_status'])) {
    $status_baru = $_POST['status'];
    $catatan     = htmlspecialchars($_POST['catatan']);
    
    // Update Database
    $q_update = "UPDATE bugs SET status = '$status_baru', updated_at = NOW() WHERE bug_id = $id";
    $run = mysqli_query($conn, $q_update);

    if ($run) {
        // Catat History
        mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) 
                             VALUES ($id, '{$bug['status']}', '$status_baru', $dev_id)");
        
        echo "<script>alert('Berhasil! Status berubah jadi $status_baru'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('GAGAL UPDATE! Cek Database.');</script>";
        // Tampilkan error SQL jika gagal
        die("Error MySQL: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Proses Bug #<?= $id; ?> | BugTracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <style>
      .card-primary:not(.card-outline) > .card-header { background-color: #007bff; }
      body { background-color: #f4f6f9; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark navbar-primary border-bottom-0">
     <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-arrow-left mr-1"></i> Kembali ke List</a></li></ul>
  </nav>
  
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link text-center">
      <span class="brand-text font-weight-bold">BugTracker Dev</span>
    </a>

    <div class="sidebar">
      
      <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
        <div class="image">
           <?php 
            $foto_profil = (!empty($_SESSION['login']['foto'])) 
                           ? '../assets/uploads/'.$_SESSION['login']['foto'] 
                           : '../assets/dist/img/avatar5.png';
           ?>
          <img src="<?= $foto_profil; ?>" class="img-circle elevation-2" alt="User Image" style="object-fit:cover; width:35px; height:35px;">
        </div>
        <div class="info">
          <a href="#" class="d-block font-weight-bold"><?= $_SESSION['login']['name']; ?></a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          
          <li class="nav-header">NAVIGASI</li>
          
          <li class="nav-item">
            <a href="index.php" class="nav-link">
              <i class="nav-icon fas fa-arrow-left"></i>
              <p>Kembali ke List</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="../profil.php" class="nav-link">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>Pengaturan Akun</p>
            </a>
          </li>

          <li class="nav-header mt-3">PANDUAN STATUS</li>
          
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-primary"></i>
              <p class="text-sm">In Progress <span class="badge badge-primary right">Coding</span></p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-warning"></i>
              <p class="text-sm">Testing <span class="badge badge-warning right">Uji Coba</span></p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-success"></i>
              <p class="text-sm">Resolved <span class="badge badge-success right">Selesai</span></p>
            </a>
          </li>

          <li class="nav-header"></li>
          <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link bg-danger" onclick="return confirm('Logout sekarang?');">
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
        <h1 class="m-0 text-dark">Penanganan Masalah</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          
          <div class="col-md-7">
            <div class="card card-outline card-info shadow-sm">
              <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-bug mr-1"></i> Detail Laporan #<?= $bug['bug_id']; ?></h3>
              </div>
              <div class="card-body">
                <table class="table table-borderless">
                    <tr><th width="30%">Judul Masalah</th><td><?= $bug['title']; ?></td></tr>
                    <tr><th>Kategori</th><td><?= $bug['category_name']; ?></td></tr>
                    <tr><th>Prioritas</th><td>
                        <span class="badge badge-<?= ($bug['priority_name']=='Critical')?'danger':'warning'; ?>">
                            <?= $bug['priority_name']; ?>
                        </span>
                    </td></tr>
                    <tr><th>Pelapor</th><td><?= $bug['nama_pelapor']; ?></td></tr>
                    <tr><th>Deskripsi</th><td><?= nl2br($bug['description']); ?></td></tr>
                </table>
                <hr>
                <h5 class="text-info"><i class="fas fa-paperclip"></i> Bukti Lampiran</h5>
                <?php if($bug['attachment']) : ?>
                    <img src="../assets/uploads/<?= $bug['attachment']; ?>" class="img-fluid border rounded" style="max-height: 300px;">
                <?php else : ?>
                    <p class="text-muted font-italic">Tidak ada lampiran gambar.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-md-5">
            <div class="card card-primary shadow-sm">
              <div class="card-header">
                <h3 class="card-title text-white"><i class="fas fa-edit mr-1"></i> Update Progress</h3>
              </div>
              <div class="card-body">
                
                <form method="POST">
                    <div class="form-group">
                        <label>Status Saat Ini:</label>
                        <input type="text" class="form-control" value="<?= $bug['status']; ?>" readonly style="background-color: #f2f2f2;">
                    </div>

                    <div class="form-group">
                        <label class="text-primary">Ubah Status Menjadi:</label>
                        <select name="status" class="form-control" required style="border: 2px solid #007bff;">
                            <option value="" disabled selected>-- Pilih Status Baru --</option>
                            <option value="In Progress">In Progress (Sedang Dikerjakan)</option>
                            <option value="Testing">Testing (Tahap Uji Coba)</option>
                            <option value="Resolved">Resolved (Selesai Diperbaiki)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Catatan Teknis:</label>
                        <textarea name="catatan" class="form-control" rows="4" placeholder="Jelaskan apa yang diperbaiki..." required></textarea>
                    </div>

                    <button type="submit" name="update_status" class="btn btn-primary btn-block font-weight-bold py-2">
                        <i class="fas fa-save mr-1"></i> SIMPAN PERUBAHAN
                    </button>
                </form>

              </div>
            </div>
          </div>

        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer"><strong>Copyright &copy; 2026.</strong></footer>
</div>
</body>
</html>
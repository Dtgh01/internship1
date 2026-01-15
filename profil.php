<?php
session_start();
require 'function.php';

// Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

$id = $_SESSION['login']['user_id'];
$user = query("SELECT * FROM users WHERE user_id = $id")[0];

// Tentukan Link Balik sesuai Role
$back_link = 'dashboard.php'; // Default
if ($user['role'] == 'admin') {
    $back_link = 'admin/index.php';
} elseif ($user['role'] == 'developer') {
    $back_link = 'developer/index.php';
}

// LOGIKA UPDATE
if (isset($_POST['update'])) {
    $name = htmlspecialchars($_POST['name']);
    $password_baru = $_POST['password'];

    // 1. Update Nama
    mysqli_query($conn, "UPDATE users SET name = '$name' WHERE user_id = $id");

    // 2. Update Password (Kalau diisi)
    if (!empty($password_baru)) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password = '$password_hash' WHERE user_id = $id");
    }

    // Update Session Nama
    $_SESSION['login']['name'] = $name;

    echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya | BugTracker</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    
    <style>
        body { background-color: #f4f6f9; }
        .profile-user-img { width: 100px; height: 100px; object-fit: cover; border: 3px solid #adb5bd; }
    </style>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm">
    <div class="container">
      <a href="<?= $back_link; ?>" class="navbar-brand">
        <img src="assets/dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8" onerror="this.src='https://via.placeholder.com/150'">
        <span class="brand-text font-weight-bold">BugTracker</span>
      </a>
      
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
        <li class="nav-item">
            <a href="<?= $back_link; ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </li>
      </ul>
    </div>
  </nav>
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"> Pengaturan Profil</h1>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container">
        <div class="row">
          
          <div class="col-md-3">
            <div class="card card-primary card-outline shadow-sm">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle"
                       src="assets/dist/img/user2-160x160.jpg"
                       alt="User profile picture"
                       onerror="this.src='https://via.placeholder.com/150'">
                </div>

                <h3 class="profile-username text-center mt-3 mb-1"><?= $user['name']; ?></h3>
                <p class="text-muted text-center mb-1"><?= $user['email']; ?></p>
                
                <div class="text-center mb-3">
                    <?php if($user['role'] == 'admin'): ?>
                        <span class="badge badge-danger px-3">Administrator</span>
                    <?php elseif($user['role'] == 'developer'): ?>
                        <span class="badge badge-warning px-3">Developer</span>
                    <?php else: ?>
                        <span class="badge badge-success px-3">User / Pelapor</span>
                    <?php endif; ?>
                </div>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Bergabung</b> <a class="float-right text-dark">2026</a>
                  </li>
                  <li class="list-group-item">
                    <b>Status Akun</b> <a class="float-right text-success"><i class="fas fa-check-circle"></i> Aktif</a>
                  </li>
                </ul>

                <a href="<?= $back_link; ?>" class="btn btn-primary btn-block"><b><i class="fas fa-tachometer-alt"></i> Ke Dashboard</b></a>
              </div>
            </div>
          </div>

          <div class="col-md-9">
            <div class="card card-primary card-outline shadow-sm">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Edit Biodata</a></li>
                  <li class="nav-item"><a class="nav-link" href="#password" data-toggle="tab">Ganti Password</a></li>
                </ul>
              </div>
              
              <div class="card-body">
                <div class="tab-content">
                  
                  <div class="active tab-pane" id="settings">
                    <form class="form-horizontal" action="" method="POST">
                      <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                        <div class="col-sm-9">
                          <input type="text" name="name" class="form-control" value="<?= $user['name']; ?>" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Email Address</label>
                        <div class="col-sm-9">
                          <input type="email" class="form-control" value="<?= $user['email']; ?>" readonly disabled style="background-color: #e9ecef;">
                          <small class="text-muted">Email tidak dapat diubah demi keamanan.</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-3 col-sm-9">
                          <button type="submit" name="update" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                        </div>
                      </div>
                    </form>
                  </div>

                  <div class="tab-pane" id="password">
                    <form class="form-horizontal" action="" method="POST">
                      <input type="hidden" name="name" value="<?= $user['name']; ?>">

                      <div class="alert alert-warning">
                        <i class="icon fas fa-exclamation-triangle"></i> Hati-hati! Password akan langsung berubah setelah Anda klik simpan.
                      </div>

                      <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Password Baru</label>
                        <div class="col-sm-9">
                          <input type="password" name="password" class="form-control" placeholder="Masukkan password baru...">
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-3 col-sm-9">
                          <button type="submit" name="update" class="btn btn-danger"><i class="fas fa-key mr-1"></i> Update Password</button>
                        </div>
                      </div>
                    </form>
                  </div>

                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </div>

  <footer class="main-footer text-center">
    <strong>Copyright &copy; 2026 <a href="#">BugTracker System</a>.</strong> All rights reserved.
  </footer>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
<?php
session_start();
require 'function.php';

// 1. Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['login']['user_id'];

// 2. LOGIKA UPDATE PROFIL
if (isset($_POST['update_profil'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = $_POST['password'];
    $gambar_lama = $_POST['gambar_lama'];

    // Cek folder upload, jika tidak ada, buat folder baru
    if (!file_exists('assets/uploads')) {
        mkdir('assets/uploads', 0777, true);
    }

    // Cek Gambar Baru
    if ($_FILES['photo']['error'] === 4) {
        $photo = $gambar_lama;
    } else {
        // Validasi Ekstensi
        $namaFile = $_FILES['photo']['name'];
        $tmpName  = $_FILES['photo']['tmp_name'];
        $ekstensiValid = ['jpg', 'jpeg', 'png'];
        $ekstensi = explode('.', $namaFile);
        $ekstensi = strtolower(end($ekstensi));
        
        if(in_array($ekstensi, $ekstensiValid)) {
            $namaBaru = uniqid() . '.' . $ekstensi;
            move_uploaded_file($tmpName, 'assets/uploads/' . $namaBaru);
            $photo = $namaBaru;
        } else {
            echo "<script>alert('Format file tidak valid! Gunakan JPG/PNG.'); window.location='profil.php';</script>";
            exit;
        }
    }

    // Cek Password
    if (empty($pass)) {
        $query = "UPDATE users SET name = '$nama', email = '$email', photo = '$photo' WHERE user_id = $user_id";
    } else {
        $password_hash = password_hash($pass, PASSWORD_DEFAULT);
        $query = "UPDATE users SET name = '$nama', email = '$email', password = '$password_hash', photo = '$photo' WHERE user_id = $user_id";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['login']['name'] = $nama;
        // Tidak perlu update session photo jika logika tampilan kita ambil langsung dari DB
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('Gagal update: " . mysqli_error($conn) . "');</script>";
    }
}

// 3. Ambil Data Terbaru User (Dipecah biar aman)
$data_user = query("SELECT * FROM users WHERE user_id = $user_id");
$user = $data_user[0]; // Ambil indeks ke-0 secara manual

// 4. LOGIKA TAMPILAN FOTO
$foto_tampil = 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=random&color=fff&size=256'; // Default

// Cek apakah ada foto di database & filenya ada di folder
if (!empty($user['photo'])) {
    $path_foto = 'assets/uploads/' . $user['photo'];
    if (file_exists($path_foto)) {
        $foto_tampil = $path_foto;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/skin.css">

    <style>
        .modal-content {
            background-color: #1e293b;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .modal-header, .modal-footer { border-color: rgba(255,255,255,0.1); }
        .close { color: #fff; opacity: 1; }
        
        /* Efek Hover Foto */
        .profile-img-container {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto;
        }
        .profile-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Agar gambar tidak gepeng */
            border: 3px solid #3b82f6;
            padding: 3px;
        }
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
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a>
          </li>
          <li class="nav-item">
            <a href="profil.php" class="nav-link active"><i class="nav-icon fas fa-user"></i><p>Profil Saya</p></a>
          </li>
          <li class="nav-header">AKSES</li>
          <li class="nav-item">
            <a href="auth/logout.php" class="nav-link bg-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Keluar</p></a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0 text-white font-weight-bold">Profil Pengguna</h1></div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row justify-content-center">
            
            <div class="col-md-5">
                <div class="card card-primary card-outline shadow-lg">
                    <div class="card-body box-profile text-center">
                        <div class="text-center mb-4">
                            <div class="profile-img-container">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?= $foto_tampil; ?>"
                                     alt="Foto Profil">
                            </div>
                        </div>

                        <h3 class="profile-username text-center text-white font-weight-bold"><?= $user['name']; ?></h3>
                        <p class="text-muted text-center text-uppercase mb-4"><?= $user['role']; ?> SYSTEM</p>

                        <ul class="list-group list-group-unbordered mb-4 text-left">
                            <li class="list-group-item bg-transparent border-top-0" style="border-color: rgba(255,255,255,0.1);">
                                <b class="text-white"><i class="fas fa-envelope mr-2"></i>Email</b> 
                                <a class="float-right text-muted"><?= $user['email']; ?></a>
                            </li>
                            <li class="list-group-item bg-transparent" style="border-color: rgba(255,255,255,0.1);">
                                <b class="text-white"><i class="fas fa-calendar mr-2"></i>Bergabung</b> 
                                <a class="float-right text-muted"><?= date('d M Y', strtotime($user['created_at'])); ?></a>
                            </li>
                        </ul>

                        <button type="button" class="btn btn-primary btn-block font-weight-bold py-2" data-toggle="modal" data-target="#modalEdit">
                            <i class="fas fa-edit mr-1"></i> Edit Profil
                        </button>
                    </div>
                </div>
            </div>

        </div>
      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong>Copyright &copy; 2026 BugTracker.</strong></footer>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-user-edit mr-2"></i>Edit Data Diri</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      
      <form action="" method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" name="gambar_lama" value="<?= $user['photo']; ?>">

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" value="<?= $user['name']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= $user['email']; ?>" required>
            </div>

            <div class="form-group">
                <label>Password Baru <small class="text-muted">(Opsional)</small></label>
                <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diganti">
            </div>

            <div class="form-group">
                <label>Ganti Foto Profil</label>
                <div class="custom-file">
                    <input type="file" name="photo" class="custom-file-input" id="customFile" accept="image/*">
                    <label class="custom-file-label" for="customFile">Pilih file...</label>
                </div>
                <small class="text-muted">Format: JPG/PNG. Maksimal 2MB.</small>
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" name="update_profil" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
          </div>
      </form>
    </div>
  </div>
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
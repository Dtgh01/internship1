<?php
session_start();
require 'function.php';

// 1. CEK LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

$id = $_SESSION['login']['user_id'];
// Ambil data user terbaru dari database
$user = query("SELECT * FROM users WHERE user_id = $id")[0];

// 2. LOGIKA TOMBOL KEMBALI (Sesuai Role)
$back_link = 'dashboard.php'; 
if ($user['role'] == 'admin') {
    $back_link = 'admin/index.php';
} elseif ($user['role'] == 'developer') {
    $back_link = 'developer/index.php';
}

// 3. LOGIKA UPDATE
if (isset($_POST['update_profil'])) {
    
    // --- UPDATE BIODATA ---
    if (isset($_POST['name'])) {
        $name = htmlspecialchars($_POST['name']);
        mysqli_query($conn, "UPDATE users SET name = '$name' WHERE user_id = $id");
        $_SESSION['login']['name'] = $name; // Update session nama
    }

    // --- UPDATE FOTO ---
    if ($_FILES['foto']['error'] !== 4) {
        $foto_baru = uploadGambar($_FILES);
        if ($foto_baru) {
            // Hapus foto lama jika bukan default (Opsional, biar hemat storage)
            // if ($user['foto'] != 'default.jpg' && file_exists('assets/uploads/' . $user['foto'])) { unlink('assets/uploads/' . $user['foto']); }
            
            mysqli_query($conn, "UPDATE users SET foto = '$foto_baru' WHERE user_id = $id");
            $_SESSION['login']['foto'] = $foto_baru; // Update session foto
        }
    }

    // --- UPDATE PASSWORD (Jika Diisi) ---
    if (!empty($_POST['password'])) {
        $pass_baru = $_POST['password'];
        $password_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password = '$password_hash' WHERE user_id = $id");
        
        echo "<script>
            alert('Profil & Password berhasil diperbarui! Silakan login ulang.');
            window.location='auth/logout.php';
        </script>";
        exit;
    }

    echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Profil Saya | BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">

  <style>
    body {
        background-color: #f4f6f9;
    }
    .main-header { border-bottom: none; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    
    /* Profile Card Styling */
    .card-profile-cover {
        height: 120px;
        background: linear-gradient(135deg, #007bff, #00d2ff);
        border-radius: 0.25rem 0.25rem 0 0;
    }
    .profile-user-img {
        width: 130px;
        height: 130px;
        object-fit: cover;
        border: 5px solid #fff;
        margin-top: -65px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Tab Styling */
    .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
        background-color: #007bff;
        border-radius: 50px;
        box-shadow: 0 2px 5px rgba(0,123,255,0.4);
    }
    .nav-pills .nav-link {
        border-radius: 50px;
        color: #6c757d;
        font-weight: 600;
        padding: 8px 20px;
        transition: all 0.2s;
    }
    .nav-pills .nav-link:hover {
        background-color: #e9ecef;
        color: #007bff;
    }

    /* Input Styling */
    .form-control {
        border-radius: 8px;
        height: 45px;
        border: 1px solid #e2e8f0;
    }
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
    }
    .input-group-text {
        border-radius: 8px 0 0 8px;
        border: 1px solid #e2e8f0;
        background-color: #f8f9fa;
    }
  </style>
</head>

<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
      <a href="<?= $back_link; ?>" class="navbar-brand">
        <i class="fas fa-shield-alt text-primary mr-2"></i>
        <span class="brand-text font-weight-bold text-dark">BugTracker</span>
      </a>
      
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a href="<?= $back_link; ?>" class="btn btn-light text-primary font-weight-bold rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Dashboard
            </a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="content-wrapper">
    <div class="content mt-4">
      <div class="container">
        <div class="row">
            
          <div class="col-md-4">
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-profile-cover"></div>
                
                <div class="card-body box-profile text-center pt-0">
                    <?php 
                        $foto_path = !empty($user['foto']) ? "assets/uploads/" . $user['foto'] : "assets/dist/img/avatar5.png";
                    ?>
                    <img class="profile-user-img img-fluid img-circle"
                         src="<?= $foto_path; ?>"
                         alt="User profile picture"
                         id="previewFoto">

                    <h3 class="profile-username font-weight-bold mt-3 mb-0"><?= $user['name']; ?></h3>
                    
                    <p class="text-muted mb-3">
                        <?= $user['email']; ?> <i class="fas fa-check-circle text-success ml-1" title="Verified"></i>
                    </p>

                    <div class="d-flex justify-content-center mb-3">
                        <span class="badge badge-primary px-3 py-2 rounded-pill text-uppercase" style="letter-spacing: 1px;">
                            <?= $user['role']; ?>
                        </span>
                    </div>

                    <div class="row text-left border-top pt-3 mt-3">
                        <div class="col-6">
                            <small class="text-muted d-block">ID Pengguna</small>
                            <h6 class="font-weight-bold">#<?= $user['user_id']; ?></h6>
                        </div>
                        <div class="col-6 text-right">
                            <small class="text-muted d-block">Terdaftar</small>
                            <h6 class="font-weight-bold"><?= date('d M Y', strtotime($user['created_at'])); ?></h6>
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <div class="col-md-8">
            <div class="card shadow-lg border-0">
              <div class="card-header bg-white border-0 pt-4 pb-0">
                <ul class="nav nav-pills" id="custom-content-below-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="tab-biodata" data-toggle="pill" href="#biodata" role="tab">
                        <i class="fas fa-user-circle mr-2"></i> Biodata
                    </a>
                  </li>
                  <li class="nav-item ml-2">
                    <a class="nav-link" id="tab-security" data-toggle="pill" href="#security" role="tab">
                        <i class="fas fa-lock mr-2"></i> Keamanan
                    </a>
                  </li>
                </ul>
              </div>
              
              <div class="card-body pt-4">
                <form class="form-horizontal" method="POST" enctype="multipart/form-data">
                    <div class="tab-content">
                        
                        <div class="tab-pane fade show active" id="biodata" role="tabpanel">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="name" value="<?= $user['name']; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email (Login ID)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control bg-light" value="<?= $user['email']; ?>" readonly>
                                </div>
                                <small class="text-muted">*Email tidak dapat diubah.</small>
                            </div>

                            <div class="form-group">
                                <label>Upload Foto Baru</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="foto" id="inputFile" accept="image/*">
                                    <label class="custom-file-label" for="inputFile">Pilih file gambar...</label>
                                </div>
                                <small class="text-info mt-1 d-block"><i class="fas fa-info-circle"></i> Rekomendasi: Format JPG/PNG, Kotak (1:1), Maks 2MB.</small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="alert alert-warning rounded-lg">
                                <i class="fas fa-exclamation-triangle mr-2"></i> 
                                <strong>Perhatian:</strong> Mengganti password akan membuat Anda logout otomatis.
                            </div>
                            
                            <div class="form-group">
                                <label>Password Baru</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengganti password">
                                </div>
                            </div>
                        </div>

                    </div> <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="button" class="btn btn-default mr-2 rounded-pill" onclick="window.history.back()">Batal</button>
                            <button type="submit" name="update_profil" class="btn btn-primary font-weight-bold rounded-pill px-5 shadow-sm">
                                <i class="fas fa-save mr-2"></i> SIMPAN PERUBAHAN
                            </button>
                        </div>
                    </div>
                </form>
              </div></div>
          </div>
          
        </div>
      </div>
    </div>
  </div>
  
  <footer class="main-footer border-top-0 bg-white text-center mt-4">
    <small class="text-muted">Copyright &copy; 2026 <b>BugTracker System</b>. All rights reserved.</small>
  </footer>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>

<script>
    // Menampilkan nama file yang dipilih
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        
        // Preview Gambar secara langsung
        readURL(this);
    });

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#previewFoto').attr('src', e.target.result); // Ganti src gambar profil
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>
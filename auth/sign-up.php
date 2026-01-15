<?php
session_start();
require '../function.php';

if (isset($_POST['register'])) {
    if (registrasi($_POST) > 0) {
        echo "<script>
                alert('Registrasi Berhasil! Silakan Login.');
                document.location.href = 'login.php';
              </script>";
    } else {
        echo mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar | Trimhub BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">

  <style>
    /* 1. BACKGROUND GRADASI BIRU (SAMA KAYAK LOGIN) */
    body {
        background: linear-gradient(135deg, #0061f2 0%, #00c6ff 100%);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .register-box { width: 400px; }
    
    .card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        border: none;
        background-color: #ffffff;
    }
    .card-header {
        background: transparent;
        border-bottom: none;
        padding-top: 35px;
        padding-bottom: 10px;
    }

    /* 2. TOMBOL & AKSEN BIRU (GANTI DARI HIJAU) */
    .btn-primary {
        background: #0061f2;
        border: none;
        border-radius: 50px;
        font-weight: bold;
        transition: 0.3s;
        box-shadow: 0 4px 6px rgba(0,97,242,0.4);
    }
    .btn-primary:hover {
        background: #004bbd;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,97,242,0.6);
    }
    
    .form-control { border-radius: 50px; height: 45px; background: #f8f9fa; border: 1px solid #ddd; }
    .form-control:focus { background: #fff; border-color: #0061f2; }
    .input-group-text { border-radius: 0 50px 50px 0; background-color: #f8f9fa; border: 1px solid #ddd; border-left: none; }
    
    /* Warna Link & Ikon */
    .text-primary { color: #0061f2 !important; }
  </style>
</head>

<body>
<div class="register-box">
  
  <div class="card">
    <div class="card-header text-center">
      <a href="../index.php">
          <img src="../assets/dist/img/logotrimhub.png" alt="Trimhub Logo" style="max-width: 100px; height: auto;">
      </a>
    </div>
    <div class="card-body register-card-body pb-4 rounded-lg">
      <p class="login-box-msg text-muted font-weight-bold">Buat Akun Baru</p>

      <form action="" method="post">
        <div class="input-group mb-3">
          <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-user text-primary"></span></div>
          </div>
        </div>
        
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email Aktif" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-envelope text-primary"></span></div>
          </div>
        </div>
        
        <div class="input-group mb-4">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock text-primary"></span></div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            </div>
            
            <button type="submit" name="register" class="btn btn-primary btn-block py-2">
                DAFTAR SEKARANG
            </button>
          </div>
        </div>
      </form>

      <div class="text-center mt-4">
        <p class="mb-1 text-muted small font-weight-bold">Sudah punya akun?</p>
        <a href="login.php" class="text-primary font-weight-bold" style="text-decoration: underline;">Login di sini</a>
      </div>
    </div>
  </div>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
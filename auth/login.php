<?php
session_start();
require '../function.php';

// 1. CEK SESSION (JIKA SUDAH LOGIN)
// Jika user sudah login tapi masuk ke halaman login lagi, lempar balik ke dashboard
if (isset($_SESSION['login'])) {
    $role = $_SESSION['login']['role'];
    if ($role == 'admin') {
        header("Location: ../admin/index.php");
    } else if ($role == 'developer') {
        header("Location: ../developer/index.php");
    } else {
        header("Location: ../dashboard.php");
    }
    exit;
}

// 2. LOGIKA LOGIN SAAT TOMBOL DITEKAN
if (isset($_POST['login'])) {
    
    // SECURITY UPDATE: Gunakan real_escape_string untuk input Database!
    // htmlspecialchars hanya untuk output HTML, tidak aman untuk query database.
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Cek Email di Database
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    // Jika Email Ditemukan (Jumlah baris === 1)
    if (mysqli_num_rows($result) === 1) {
        
        // Ambil data user
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi Password (Hash vs Input)
        if (password_verify($password, $row['password'])) {
            
            // Set Session Login
            $_SESSION['login'] = $row; // Simpan data user ke session

            // Redirect Sesuai Role (Hak Akses)
            if ($row['role'] == 'admin') {
                header("Location: ../admin/index.php");
            } else if ($row['role'] == 'developer') {
                header("Location: ../developer/index.php");
            } else {
                header("Location: ../dashboard.php");
            }
            exit;
        }
    }

    // Jika Email tidak ketemu ATAU Password salah
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Trimhub BugTracker</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">

  <style>
    /* BACKGROUND GRADASI BIRU */
    body {
        background: linear-gradient(135deg, #0061f2 0%, #00c6ff 100%);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-box { width: 400px; }
    
    .card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        border: none;
        background-color: #ffffff;
    }
    /* Padding Header disesuaikan buat Logo Image */
    .card-header {
        background: transparent;
        border-bottom: none;
        padding-top: 35px;
        padding-bottom: 10px;
    }
    
    .btn-primary {
        background: #0061f2; border: none; border-radius: 50px; font-weight: bold; transition: 0.3s;
        box-shadow: 0 4px 6px rgba(0,97,242,0.4);
    }
    .btn-primary:hover {
        background: #004bbd; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,97,242,0.6);
    }
    .form-control { border-radius: 50px; height: 45px; background: #f8f9fa; border: 1px solid #ddd; }
    .form-control:focus { background: #fff; border-color: #0061f2; }
    .input-group-text { border-radius: 0 50px 50px 0; background-color: #f8f9fa; border: 1px solid #ddd; border-left: none; }
  </style>
</head>

<body>
<div class="login-box">
  
  <div class="card">
    <div class="card-header text-center">
      <a href="../index.php">
          <img src="../assets/img/logotrimhub.png" alt="Trimhub Logo" style="max-width: 100px; height: auto;">
      </a>
    </div>
    <div class="card-body login-card-body pb-4 rounded-lg">
      <p class="login-box-msg text-muted font-weight-bold">BugTracker</p>

      <?php if(isset($error)) : ?>
          <div class="alert alert-danger text-center py-2 rounded-pill mb-3" style="font-size: 14px;">
              <i class="fas fa-times-circle mr-1"></i> Email / Password Salah
          </div>
      <?php endif; ?>

      <form action="" method="post">
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
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
            <button type="submit" name="login" class="btn btn-primary btn-block py-2">
                MASUK SEKARANG
            </button>
          </div>
        </div>
      </form>

      <div class="text-center mt-4">
        <p class="mb-1 text-muted small font-weight-bold">Belum punya akun?</p>
        <a href="sign-up.php" class="text-primary font-weight-bold" style="text-decoration: underline;">Daftar Akun Baru</a>
      </div>
    </div>
  </div>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
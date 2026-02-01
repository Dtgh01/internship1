<?php
session_start();
require '../function.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: ../dashboard.php");
    exit;
}

// LOGIKA REGISTRASI
if (isset($_POST["register"])) {
    
    // Validasi Password Match
    if ($_POST["password"] !== $_POST["repassword"]) {
        $error = "Konfirmasi password tidak sesuai!";
    } else {
        // Panggil fungsi registrasi dari function.php
        if (registrasi($_POST) > 0) {
            echo "<script>
                    alert('Registrasi Berhasil! Silakan Login.');
                    document.location.href = 'login.php';
                  </script>";
        } else {
            $error = "Gagal mendaftar! Email mungkin sudah digunakan.";
            // echo mysqli_error($conn); // Uncomment untuk debugging
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">

    <style>
        /* --- CUSTOM REGISTER CSS (SAMA DENGAN LOGIN) --- */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; 
            background-image: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 75%);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-box { width: 400px; padding: 20px; }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .card-header {
            background: transparent;
            border-bottom: none;
            padding-top: 30px;
            padding-bottom: 10px;
        }

        /* Styling Logo */
        .register-logo img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.3));
            transition: transform 0.3s;
        }
        .register-logo img:hover { transform: scale(1.05); }

        .card-body { padding: 30px; }

        /* Input Styles */
        .form-control {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            height: 45px;
            border-radius: 8px;
        }
        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.9);
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            color: #fff;
        }
        .input-group-text {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: none;
            color: #94a3b8;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Tombol */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            height: 45px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
            transition: 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        }

        a { color: #60a5fa; }
        .icheck-primary label { color: #cbd5e1; }
        
        /* Alert Error */
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }
    </style>
</head>

<body class="hold-transition register-page">
    
    <div class="register-box">
        <div class="card">
            <div class="card-header text-center">
                <div class="register-logo">
                    <a href="../index.php">
                        <img src="../assets/img/logotrimhub.png" alt="Logo BugTracker">
                    </a>
                </div>
                <p class="text-white font-weight-bold mt-2 mb-1" style="font-size: 1.1rem;">Join the Squad!</p>
                <p class="login-box-msg p-0 text-muted">Daftar akun baru dalam 1 menit</p>
            </div>
            
            <div class="card-body">
                
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger text-center rounded-lg mb-4 p-2">
                        <small><i class="fas fa-exclamation-triangle mr-1"></i> <?= $error; ?></small>
                    </div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="input-group">
                            <input type="password" name="repassword" class="form-control" placeholder="Konfirmasi Password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="icheck-primary">
                                <input type="checkbox" id="agreeTerms" name="terms" value="agree" required>
                                <label for="agreeTerms">
                                   Saya setuju dengan <a href="#">Syarat & Ketentuan</a>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="register" class="btn btn-primary btn-block">
                                DAFTAR SEKARANG
                            </button>
                        </div>
                    </div>
                </form>

                <div class="social-auth-links text-center mt-4 mb-2">
                    <p class="text-sm text-muted mb-2">- Sudah punya akun? -</p>
                    <a href="login.php" class="btn btn-outline-light btn-block btn-sm" style="border-color: rgba(255,255,255,0.1); color: #cbd5e1;">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login Disini
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>
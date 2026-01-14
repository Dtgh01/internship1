<?php
session_start();
// Cek status login buat ngatur tombol di navbar
$isLoggedIn = isset($_SESSION['login']);
$role = $isLoggedIn ? $_SESSION['login']['role'] : '';

// Tentukan arah redirect dashboard berdasarkan role
$dashboardLink = 'dashboard.php';
if ($role == 'admin') $dashboardLink = 'admin/index.php';
if ($role == 'developer') $dashboardLink = 'developer/index.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BugTracker - Sistem Tracking Bug</title>
    
    <link rel="stylesheet" href="assets/plugins/bootstrap4/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .hero {
            background: linear-gradient(135deg, #007bff 0%, #00d2ff 100%);
            color: white;
            padding: 100px 0;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
        .hero h1 { font-weight: 800; font-size: 3.5rem; }
        .hero p { font-size: 1.2rem; opacity: 0.9; }
        
        .btn-light-custom {
            background: white; color: #007bff; font-weight: bold;
            padding: 12px 35px; border-radius: 50px; transition: 0.3s;
        }
        .btn-light-custom:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

        .feature-box {
            text-align: center; padding: 40px 20px; transition: 0.3s;
            border-radius: 15px; border: 1px solid #eee;
        }
        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        .feature-icon {
            font-size: 3rem; color: #007bff; margin-bottom: 20px;
        }

        .step-number {
            background: #007bff; color: white; width: 40px; height: 40px;
            border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
            font-weight: bold; margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand font-weight-bold text-primary" href="#">
                <img src="assets/img/logotrimhub.png" alt="BugTracker" style="width: auto; height: 40px; margin-right: 5px;">
                BugTracker
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item mx-2"><a class="nav-link" href="#fitur">Fitur</a></li>
                    <li class="nav-item mx-2"><a class="nav-link" href="#cara-kerja">Cara Kerja</a></li>
                    <li class="nav-item mx-2">
                        <?php if ($isLoggedIn) : ?>
                            <a href="<?= $dashboardLink; ?>" class="btn btn-primary btn-sm px-4 rounded-pill">
                                <i class="fas fa-tachometer-alt"></i> Dashboard Saya
                            </a>
                        <?php else : ?>
                            <a href="auth/login.php" class="btn btn-outline-primary btn-sm px-4 rounded-pill">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero mt-5">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <h1 class="mb-3">Tracking Bug<br>Tanpa Ribet.</h1>
                    <p class="mb-5">Laporkan error aplikasi, pantau perbaikan secara real-time, dan bantu kami menjadi lebih baik. Cepat, Transparan, dan Efisien.</p>
                    
                    <?php if ($isLoggedIn) : ?>
                        <a href="form-bug.php" class="btn btn-light-custom btn-lg">
                            <i class="fas fa-plus-circle"></i> Laporkan Bug Sekarang
                        </a>
                    <?php else : ?>
                        <a href="auth/login.php" class="btn btn-light-custom btn-lg">
                            <i class="fas fa-rocket"></i> Mulai Lapor Bug
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="font-weight-bold">BugTracker</h2>
                <p class="text-muted">Platform pelaporan bug.</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box bg-white">
                        <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                        <h4>Fast Reporting</h4>
                        <p class="text-muted">Formulir simpel. Lampirkan screenshot, pilih kategori, dan kirim dalam hitungan detik.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box bg-white">
                        <div class="feature-icon"><i class="fas fa-sync-alt"></i></div>
                        <h4>Real-time Tracking</h4>
                        <p class="text-muted">Pantau status laporanmu dari <i>Open</i>, <i>In Progress</i>, hingga <i>Resolved</i> secara langsung.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box bg-white">
                        <div class="feature-icon"><i class="fas fa-user-shield"></i></div>
                        <h4>Dedicated Team</h4>
                        <p class="text-muted">Developer kami langsung menerima notifikasi dan menangani masalah sesuai prioritas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="cara-kerja" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="font-weight-bold">Alur Pelaporan</h2>
            </div>
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="step-number">1</div>
                    <h5>Login</h5>
                    <p class="small text-muted">Masuk dengan akun user Anda.</p>
                </div>
                <div class="col-md-3">
                    <div class="step-number">2</div>
                    <h5>Buat Laporan</h5>
                    <p class="small text-muted">Isi detail error & upload bukti.</p>
                </div>
                <div class="col-md-3">
                    <div class="step-number">3</div>
                    <h5>Proses</h5>
                    <p class="small text-muted">Developer memperbaiki masalah.</p>
                </div>
                <div class="col-md-3">
                    <div class="step-number">4</div>
                    <h5>Selesai</h5>
                    <p class="small text-muted">Aplikasi kembali normal!</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y'); ?> <b>BugTracker</b>.</p>
        </div>
    </footer>

    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap4/js/bootstrap.bundle.min.js"></script>
</body>
</html>
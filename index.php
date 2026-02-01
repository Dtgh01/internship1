<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BugTracker | Manajemen Bug</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">

    <style>
        /* --- TEMA: MODERN ENGINEERING DARK --- */
        :root {
            --bg-dark: #0f172a;       
            --bg-card: rgba(30, 41, 59, 0.7); 
            --primary: #3b82f6;       
            --secondary: #64748b;     
            --text-light: #f1f5f9;    
            --border-glass: rgba(255, 255, 255, 0.1); 
        }

        html {
            scroll-behavior: smooth; /* Agar tombol scrollnya halus */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: #94a3b8;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Background Pattern */
        .engineering-grid {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: -1;
        }

        /* Navbar */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border-glass);
            padding: 15px 0;
            transition: all 0.3s;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--text-light) !important;
            letter-spacing: -0.5px;
            font-size: 1.25rem;
            display: flex; align-items: center;
        }
        .navbar-brand span {
            font-weight: 300; color: var(--secondary); font-size: 0.9rem; margin-left: 10px; padding-left: 10px; border-left: 1px solid var(--secondary);
        }
        .nav-link { font-weight: 500; color: #cbd5e1 !important; margin-left: 20px; font-size: 0.9rem; transition: color 0.2s; }
        .nav-link:hover { color: var(--primary) !important; }

        /* Tombol Login */
        .btn-login {
            background: rgba(59, 130, 246, 0.1); color: #60a5fa !important; border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 6px; padding: 8px 24px; font-weight: 600; transition: all 0.3s ease;
        }
        .btn-login:hover { background: var(--primary); color: white !important; box-shadow: 0 0 15px rgba(59, 130, 246, 0.4); }

        /* Hero Section */
        .hero-section { padding: 140px 0 100px; position: relative; }
        .hero-glow {
            position: absolute; width: 600px; height: 600px; background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%); top: -150px; left: -150px; z-index: -1;
        }
        .badge-tech {
            background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); color: #94a3b8; padding: 6px 14px; border-radius: 4px; font-size: 0.8rem; font-family: 'Courier New', monospace; margin-bottom: 20px; display: inline-block;
        }
        .hero-title { font-weight: 800; font-size: 3.2rem; line-height: 1.2; color: var(--text-light); margin-bottom: 20px; }
        .hero-lead { font-size: 1.05rem; margin-bottom: 35px; font-weight: 400; max-width: 540px; color: #94a3b8; }
        .btn-cta {
            background: var(--text-light); color: var(--bg-dark); font-weight: 600; padding: 12px 30px; border-radius: 6px; border: none; transition: transform 0.2s;
        }
        .btn-cta:hover { transform: translateY(-2px); background: white; box-shadow: 0 5px 15px rgba(255,255,255,0.1); }

        /* General Card Style */
        .glass-card {
            background: var(--bg-card); padding: 30px; border-radius: 10px; border: 1px solid var(--border-glass); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); transition: transform 0.3s, border-color 0.3s; height: 100%;
        }
        .glass-card:hover { transform: translateY(-5px); border-color: rgba(59, 130, 246, 0.5); }
        .icon-box {
            width: 56px; height: 56px; background: rgba(59, 130, 246, 0.1); color: #60a5fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 20px; border: 1px solid rgba(59, 130, 246, 0.2);
        }
        h5 { color: var(--text-light); font-weight: 600; letter-spacing: -0.5px; }

        /* Timeline / Alur Kerja Style */
        .step-number {
            font-size: 3rem; font-weight: 800; color: rgba(255,255,255,0.05); position: absolute; top: 10px; right: 20px; line-height: 1; pointer-events: none;
        }
        .timeline-connector {
            border-left: 2px dashed var(--secondary); height: 50px; margin-left: 28px; opacity: 0.3;
        }
        @media (min-width: 768px) {
            .timeline-connector {
                border-left: none; border-top: 2px dashed var(--secondary); height: auto; width: 100px; margin: auto; display: block;
            }
        }

        /* Footer */
        footer { background: #020617; padding: 50px 0; margin-top: 80px; border-top: 1px solid var(--border-glass); color: #64748b; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="engineering-grid"></div>

    <nav class="navbar navbar-expand-lg fixed-top navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
<a class="navbar-brand" href="#">
<img src="assets/img/logo1.png" alt="Logo" 
     style="height: 32px; margin-right: 36px; filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.5));">BugTracker<span></span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="fas fa-bars" style="color: var(--text-light);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#fitur">Fitur Utama</a></li>
                    <li class="nav-item"><a class="nav-link" href="#alur">Alur Kerja</a></li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-login ml-3" href="auth/login.php">
                            Login <i class="fas fa-arrow-right ml-2" style="font-size: 0.8em;"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="hero-glow"></div> 
        
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Sistem Pelaporan Bug<br>Terintegrasi & Aman.</h1>
                    <p class="hero-lead">
                        Platform manajemen bug untuk mendukung proses pengembangan perangkat lunak.
                    </p>
                    <div class="d-flex align-items-center">
                        <a href="auth/login.php" class="btn btn-cta">
                            Mulai dengan Login
                        </a>
                        <a href="#alur" class="ml-4 text-white" style="text-decoration: none; font-weight: 500;">
                            Pelajari Alur <i class="fas fa-arrow-down ml-1" style="font-size: 0.8em;"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center d-none d-lg-block">
                    <img src="assets/img/homepage.svg" alt="Technical Illustration" class="img-fluid" style="max-height: 420px; opacity: 0.9; filter: drop-shadow(0 0 40px rgba(59,130,246,0.15));">
                </div>
            </div>
        </div>
    </header>

    <section id="fitur" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 style="font-weight: 700; color: #fff;">Fitur BugTracker</h2>
                    <p class="text-muted">Implementasi fitur teknis untuk mendukung siklus hidup pengembangan (SDLC).</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="glass-card">
                        <div class="icon-box"><i class="fas fa-database"></i></div>
                        <h5>Data Terpusat</h5>
                        <p class="small text-muted mb-0">Penyimpanan laporan bug menggunakan basis data relasional untuk integritas data dan kemudahan audit trail.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="glass-card">
                        <div class="icon-box"><i class="fas fa-shield-alt"></i></div>
                        <h5>Manajemen Multi-User</h5>
                        <p class="small text-muted mb-0">Akses terpisah untuk setiap aktor berdasarkan role pada akun masing masing untuk menjaga alur kerja aplikasi tetap teratur.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="glass-card">
                        <div class="icon-box"><i class="fas fa-chart-pie"></i></div>
                        <h5>Visualisasi Statistik</h5>
                        <p class="small text-muted mb-0">Dashboard interaktif menyajikan metrik performa perbaikan bug secara realtime untuk pengambilan keputusan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="alur" class="py-5">
        <div class="container pt-4">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 style="font-weight: 700; color: #fff;">Mekanisme Pelaporan</h2>
                    <p class="text-muted">Alur kerja sistematis dari pelaporan hingga penyelesaian isu.</p>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-md-4 mb-4">
                    <div class="glass-card position-relative">
                        <div class="step-number">01</div>
                        <div class="icon-box" style="background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.2);">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h5>Pelaporan Bug</h5>
                        <p class="small text-muted mb-0">QA menemukan isu dan mengisi form laporan disertai bukti (Screenshot/Log). Status awal: <b>Open</b>.</p>
                    </div>
                </div>
                
                <div class="col-md-1 d-none d-md-block text-center">
                    <i class="fas fa-chevron-right text-muted" style="opacity: 0.3; font-size: 1.5rem;"></i>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="glass-card position-relative">
                        <div class="step-number">02</div>
                        <div class="icon-box" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border-color: rgba(245, 158, 11, 0.2);">
                            <i class="fas fa-code"></i>
                        </div>
                        <h5>Perbaikan</h5>
                        <p class="small text-muted mb-0">Developer menerima tugas, melakukan debugging, dan memperbaiki kode. Status: <b>In Progress</b>.</p>
                    </div>
                </div>

                <div class="col-md-1 d-none d-md-block text-center">
                    <i class="fas fa-chevron-right text-muted" style="opacity: 0.3; font-size: 1.5rem;"></i>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="glass-card position-relative">
                        <div class="step-number">03</div>
                        <div class="icon-box">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <h5>Validasi</h5>
                        <p class="small text-muted mb-0">Bug telah diperbaiki dan diverifikasi. Sistem mencatat waktu penyelesaian. Status: <b>Resolved</b>.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-center">
        <div class="container">
            <h6 class="font-weight-bold text-white mb-3">BugTracker</h6>
            <div class="d-flex justify-content-center mb-4 text-muted small">
            </div>
            <p class="mb-0" style="font-size: 12px; border-top: 1px solid var(--border-glass); padding-top: 20px; opacity: 0.5;">
                &copy; 2026 <b>BugTracker</b>
            </p>
        </div>
    </footer>

    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('.navbar-custom').css('padding', '10px 0');
                $('.navbar-custom').css('background', 'rgba(15, 23, 42, 0.95)');
            } else {
                $('.navbar-custom').css('padding', '15px 0');
                $('.navbar-custom').css('background', 'rgba(15, 23, 42, 0.85)');
            }
        });
    </script>
</body>
</html>/
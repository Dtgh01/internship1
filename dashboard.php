<?php
session_start();
require 'function.php';

// 1. Cek Login & Role
if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

// 2. Ambil Data User Login
$user_id = $_SESSION['login']['user_id'];
$nama_user = $_SESSION['login']['name'];

// 3. Query Laporan Saya
$query = "SELECT bugs.*, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          WHERE bugs.user_id = $user_id
          ORDER BY bugs.created_at DESC";

$my_bugs = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User | TrimHub ID</title>
    
    <link rel="stylesheet" href="assets/plugins/bootstrap4/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* HEADER GRADIENT */
        .header-dashboard {
            background: linear-gradient(135deg, #007bff 0%, #00d2ff 100%);
            color: white;
            padding-bottom: 5rem;
            margin-bottom: -3rem; /* Efek kartu naik */
            border-radius: 0 0 30px 30px;
        }

        .navbar-custom { background: transparent !important; }
        
        .card-summary {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .card-summary:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="header-dashboard shadow-sm">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom pt-4">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="#"><i class="fas fa-bug"></i> TrimHub ID</a>
            <div class="ml-auto text-white">
                Halo, <b><?= $nama_user; ?></b> 👋 | 
                <a href="auth/logout.php" class="text-white font-weight-bold ml-2" onclick="return confirm('Yakin mau logout?')">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 pb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 font-weight-bold">Dashboard Kamu 🚀</h1>
                <p class="lead" style="opacity: 0.9;">Pantau status laporan bug kamu secara real-time di sini.</p>
            </div>
            <div class="col-md-4 text-right">
                <a class="btn btn-light text-primary font-weight-bold shadow-sm py-2 px-4 rounded-pill" href="form-bug.php">
                    <i class="fas fa-plus-circle"></i> Buat Laporan Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-top: 50px;">
    
    <div class="card card-summary mb-5">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 text-primary font-weight-bold"><i class="fas fa-history"></i> Riwayat Laporan Saya</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Judul Masalah</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_bugs)) : ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-smile-beam fa-3x mb-3"></i><br>
                                    <i>Belum ada laporan yang kamu buat. Aplikasi aman terkendali! 🎉</i>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php $i = 1; foreach ($my_bugs as $bug) : ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td style="max-width: 300px;">
                                    <b><?= $bug['title']; ?></b><br>
                                    <small class="text-muted text-truncate d-block"><?= substr($bug['description'], 0, 50); ?>...</small>
                                </td>
                                <td><?= $bug['category_name']; ?></td>
                                <td>
                                    <?php if($bug['priority_name'] == 'Critical') : ?>
                                        <span class="badge badge-danger">Critical 🔥</span>
                                    <?php elseif($bug['priority_name'] == 'High') : ?>
                                        <span class="badge badge-warning">High ⚠️</span>
                                    <?php else : ?>
                                        <span class="badge badge-info"><?= $bug['priority_name']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $st = $bug['status'];
                                        $cls = 'secondary';
                                        if($st == 'Open') $cls = 'danger';
                                        if($st == 'In Progress') $cls = 'primary';
                                        if($st == 'Resolved') $cls = 'success';
                                        if($st == 'Closed') $cls = 'dark';
                                    ?>
                                    <span class="badge badge-<?= $cls; ?> p-2"><?= $st; ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($bug['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
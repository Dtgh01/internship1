<?php
session_start();
require '../function.php';

// 1. CEK LOGIN & ROLE
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['login']['role'] !== 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='../dashboard.php';</script>";
    exit;
}

// 2. QUERY STATISTIK (DARI KODE LAMA KAMU - TETAP DIPAKAI)
$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs");
$total_bugs = mysqli_fetch_assoc($q1)['total'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE status = 'Open'");
$open_bugs = mysqli_fetch_assoc($q2)['total'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE status = 'In Progress'");
$progress_bugs = mysqli_fetch_assoc($q3)['total'];

$q4 = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = mysqli_fetch_assoc($q4)['total'];


// 3. QUERY BARU: LIST VALIDASI (Resolved)
// Ini fitur baru: Menampilkan bug yang sudah selesai dikerjakan Developer dan butuh ACC Admin
$query_validasi = "SELECT bugs.*, users.name as pelapor, categories.category_name 
                   FROM bugs 
                   JOIN users ON bugs.user_id = users.user_id
                   JOIN categories ON bugs.category_id = categories.category_id
                   WHERE status = 'Resolved' 
                   ORDER BY updated_at ASC"; 
$list_validasi = query($query_validasi);


// 4. INCLUDE TEMPLATE (SESUAI STRUKTUR KAMU)
include 'templates/header.php';
include 'templates/sidebar-home.php'; 
?>

<style>
    .content-wrapper { background-color: #f4f6f9 !important; }    
    .small-box {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .small-box:hover { transform: translateY(-3px); }
    
    /* Style Khusus Tabel Validasi */
    .card-validasi { border-left: 5px solid #ffc107; }
    .bg-validasi { background-color: #fffcf5; }
    .blink_me { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }
</style>

<div class="content-wrapper">
    
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">Dashboard Admin</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $total_bugs; ?></h3>
                            <p>Total Laporan</p>
                        </div>
                        <div class="icon"><i class="fas fa-folder-open"></i></div>
                        <a href="data-bug.php" class="small-box-footer">Lihat Semua <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $open_bugs; ?></h3>
                            <p>Perlu Review (Open)</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <a href="data-bug.php" class="small-box-footer">Segera Cek <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner text-white">
                            <h3><?= $progress_bugs; ?></h3>
                            <p>Sedang Dikerjakan</p>
                        </div>
                        <div class="icon"><i class="fas fa-tools"></i></div>
                        <a href="data-bug.php" class="small-box-footer" style="color: white !important;">Pantau Progress <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $total_users; ?></h3>
                            <p>User Terdaftar</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <a href="active-acc.php" class="small-box-footer">Kelola User <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <?php if (!empty($list_validasi)) : ?>
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card card-warning card-outline card-validasi shadow-lg">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold text-warning">
                                <i class="fas fa-bell mr-2 blink_me"></i> MENUNGGU VALIDASI (RESOLVED)
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-warning"><?= count($list_validasi); ?> Laporan</span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Masalah</th>
                                        <th>Developer</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi Validasi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-validasi">
                                    <?php foreach ($list_validasi as $val) : ?>
                                    <tr>
                                        <td class="align-middle font-weight-bold text-center">#<?= $val['bug_id']; ?></td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold"><?= $val['title']; ?></span><br>
                                            <small class="text-muted"><i class="fas fa-user mr-1"></i> Pelapor: <?= $val['pelapor']; ?></small>
                                        </td>
                                        <td class="align-middle">
                                            <?php 
                                                // Ambil nama developer
                                                $dev_q = mysqli_query($conn, "SELECT name FROM users WHERE user_id = {$val['assigned_to']}");
                                                $dev = mysqli_fetch_assoc($dev_q);
                                                echo '<span class="text-primary font-weight-bold">'.($dev['name'] ?? 'Unknown').'</span>';
                                            ?>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-success px-3 py-1">Resolved (Selesai)</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="detail-bug.php?id=<?= $val['bug_id']; ?>" class="btn btn-warning btn-sm font-weight-bold shadow-sm rounded-pill px-4">
                                                <i class="fas fa-check-double mr-1"></i> CEK HASIL
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-muted">
                            <small><i class="fas fa-info-circle"></i> Segera validasi laporan di atas agar statusnya menjadi <b>Closed</b> (Selesai).</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($list_validasi)) : ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm" style="border-radius: 15px;">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold">👋 Selamat Datang, Admin!</h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="lead mb-1">Halo, <b><?= $_SESSION['login']['name']; ?></b>. Sistem berjalan normal.</p>
                                    <p class="text-muted">Tidak ada laporan yang menunggu validasi (Resolved). Silakan cek Data Bug untuk laporan baru.</p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="data-bug.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow">
                                        <i class="fas fa-rocket mr-2"></i> Kelola Semua Bug
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<?php include 'templates/footer.php'; ?>
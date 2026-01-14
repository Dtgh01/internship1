<?php
session_start();
require '../function.php';


if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['login']['role'] !== 'admin') {
    echo "<script>
            alert('Akses Ditolak!');
            window.location='../dashboard.php';
          </script>";
    exit;
}


// Hitung Total Laporan
$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs");
$total_bugs = mysqli_fetch_assoc($q1)['total'];

// Hitung Open (Perlu Review)
$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE status = 'Open'");
$open_bugs = mysqli_fetch_assoc($q2)['total'];

// Hitung Progress (Sedang Dikerjakan)
$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM bugs WHERE status = 'In Progress'");
$progress_bugs = mysqli_fetch_assoc($q3)['total'];

// Hitung Total User
$q4 = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = mysqli_fetch_assoc($q4)['total'];


include 'templates/header.php';
include 'templates/sidebar-home.php'; 
?>

<style>

    .content-wrapper {
        background-color: #f0f2f5 !important; 
    }    
    .small-box {
        transition: transform 0.3s;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }
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
                            <p>Total Bug Masuk</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <a href="data-bug.php" class="small-box-footer">
                            Lihat Semua <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $open_bugs; ?></h3>
                            <p>Perlu Review (Open)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="data-bug.php" class="small-box-footer">
                            Segera Cek <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner text-white">
                            <h3><?= $progress_bugs; ?></h3>
                            <p>Sedang Dikerjakan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <a href="data-bug.php" class="small-box-footer" style="color: white !important;">
                            Pantau Progress <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $total_users; ?></h3>
                            <p>User Terdaftar</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="active-acc.php" class="small-box-footer">
                            Kelola User <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm" style="border-radius: 15px;">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold"> Selamat Datang, Admin!</h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="lead mb-1">Anda login sebagai: <b><?= $_SESSION['login']['name']; ?></b></p>
                                    <p class="text-muted">Gunakan panel di atas untuk navigasi cepat ke fitur utama.</p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="data-bug.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow">
                                        <i class="fas fa-rocket"></i> Mulai Kelola Bug
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include 'templates/footer.php'; ?>
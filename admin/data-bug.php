<?php
session_start();
require '../function.php';

// 1. Cek Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. LOGIKA FILTER TANGGAL
$tgl_awal  = "";
$tgl_akhir = "";
$where_clause = "";
$url_cetak = "cetak-laporan.php"; // Default cetak semua

// Kalau tombol filter ditekan
if (isset($_GET['filter']) && !empty($_GET['tgl_awal']) && !empty($_GET['tgl_akhir'])) {
    $tgl_awal  = $_GET['tgl_awal'];
    $tgl_akhir = $_GET['tgl_akhir'];
    
    // Tambah WHERE ke Query
    $where_clause = " WHERE DATE(bugs.created_at) BETWEEN '$tgl_awal' AND '$tgl_akhir' ";
    
    // Update link cetak biar bawa tanggal
    $url_cetak = "cetak-laporan.php?tgl_awal=$tgl_awal&tgl_akhir=$tgl_akhir";
}

// 3. Query Data (Ditambah Variabel $where_clause)
$query = "SELECT bugs.*, users.name as pelapor, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN users ON bugs.user_id = users.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          $where_clause
          ORDER BY bugs.created_at DESC";

$bugs = query($query);

include 'templates/header.php';
include 'templates/sidebar-home.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Data Laporan Bug</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Data Bug</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        
                        <div class="card-header">
                            <form action="" method="GET">
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <h3 class="card-title font-weight-bold mb-2">
                                            <i class="fas fa-table mr-1"></i> Data Masuk
                                        </h3>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="mb-1 small">Dari Tanggal:</label>
                                        <input type="date" name="tgl_awal" class="form-control form-control-sm" value="<?= $tgl_awal; ?>">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="mb-1 small">Sampai Tanggal:</label>
                                        <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= $tgl_akhir; ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <button type="submit" name="filter" value="true" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        
                                        <a href="data-bug.php" class="btn btn-secondary btn-sm" title="Reset Filter">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>

                                        <a href="<?= $url_cetak; ?>" target="_blank" class="btn btn-danger btn-sm float-right">
                                            <i class="fas fa-print"></i> Cetak PDF
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="card-body">
                            <?php if(!empty($tgl_awal)) : ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fas fa-info-circle"></i> Menampilkan data dari tanggal <b><?= date('d-m-Y', strtotime($tgl_awal)); ?></b> s/d <b><?= date('d-m-Y', strtotime($tgl_akhir)); ?></b>.
                                </div>
                            <?php endif; ?>

                            <table id="example1" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Judul Masalah</th>
                                        <th>Kategori</th>
                                        <th>Pelapor</th>
                                        <th>Prioritas</th>
                                        <th>Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($bugs)) : ?>
                                        <tr>
                                            <td colspan="7" class="text-center font-italic text-muted py-4">
                                                Tidak ada data ditemukan pada periode ini.
                                            </td>
                                        </tr>
                                    <?php else : ?>
                                        <?php $i = 1; foreach ($bugs as $row) : ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td>
                                                <b><?= $row['title']; ?></b><br>
                                                <small class="text-muted"><?= date('d M Y H:i', strtotime($row['created_at'])); ?></small>
                                            </td>
                                            <td><?= $row['category_name']; ?></td>
                                            <td><?= $row['pelapor']; ?></td>
                                            <td>
                                                <?php if($row['priority_name'] == 'Critical'): ?>
                                                    <span class="badge badge-danger">Critical 🔥</span>
                                                <?php elseif($row['priority_name'] == 'High'): ?>
                                                    <span class="badge badge-warning">High ⚠️</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info"><?= $row['priority_name']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $st = $row['status'];
                                                    $badge = 'secondary';
                                                    if($st == 'Open') $badge = 'danger';
                                                    if($st == 'Assigned') $badge = 'info';
                                                    if($st == 'In Progress') $badge = 'primary';
                                                    if($st == 'Resolved') $badge = 'success';
                                                    if($st == 'Closed') $badge = 'dark';
                                                ?>
                                                <span class="badge badge-<?= $badge; ?>"><?= $st; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="detail-bug.php?id=<?= $row['bug_id']; ?>" class="btn btn-sm btn-info" title="Detail & Assign">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <a href="hapus.php?id=<?= $row['bug_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin mau menghapus laporan ini? Data yang dihapus tidak bisa kembali.');" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'templates/footer.php'; ?>
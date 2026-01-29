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

// Kalau tombol filter ditekan dan tanggal tidak kosong
if (isset($_GET['filter']) && !empty($_GET['tgl_awal']) && !empty($_GET['tgl_akhir'])) {
    // Security: Gunakan escape string untuk mencegah error query
    $tgl_awal  = mysqli_real_escape_string($conn, $_GET['tgl_awal']);
    $tgl_akhir = mysqli_real_escape_string($conn, $_GET['tgl_akhir']);
    
    // Tambah WHERE ke Query
    $where_clause = " WHERE DATE(bugs.created_at) BETWEEN '$tgl_awal' AND '$tgl_akhir' ";
    
    // Update link cetak biar bawa parameter tanggal
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
                    <h1 class="m-0 text-dark font-weight-bold">Data Laporan Bug</h1>
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
                    
                    <div class="card card-primary card-outline shadow-sm">
                        
                        <div class="card-header border-0 d-md-flex justify-content-between align-items-center py-3">
                            
                            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                                <i class="fas fa-list-alt mr-1"></i> Data Masuk
                            </h3>
                            
                            <div class="card-tools">
                                <form action="" method="GET" class="form-inline">
                                    
                                    <div class="input-group input-group-sm mr-2 mb-2 mb-md-0">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="far fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal; ?>" required>
                                    </div>

                                    <span class="mr-2 font-weight-bold text-muted d-none d-md-inline">-</span>

                                    <div class="input-group input-group-sm mr-2 mb-2 mb-md-0">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="far fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir; ?>" required>
                                    </div>

                                    <button type="submit" name="filter" value="true" class="btn btn-info btn-sm mr-2 mb-2 mb-md-0 shadow-sm" title="Terapkan Filter">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>

                                    <?php if(!empty($tgl_awal)): ?>
                                        <a href="data-bug.php" class="btn btn-default btn-sm border mr-2 mb-2 mb-md-0 shadow-sm" title="Reset Filter">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= $url_cetak; ?>" target="_blank" class="btn btn-danger btn-sm mb-2 mb-md-0 shadow-sm px-3">
                                        <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                                    </a>

                                </form>
                            </div>
                        </div>
                        
                        <div class="card-body p-0 table-responsive">
                            
                            <?php if(!empty($tgl_awal)) : ?>
                                <div class="alert alert-info rounded-0 mb-0 py-2 text-center" style="font-size: 14px;">
                                    <i class="fas fa-info-circle mr-1"></i> Menampilkan data periode: 
                                    <b><?= date('d-m-Y', strtotime($tgl_awal)); ?></b> s/d <b><?= date('d-m-Y', strtotime($tgl_akhir)); ?></b>
                                </div>
                            <?php endif; ?>

                            <table id="example1" class="table table-hover table-striped text-nowrap">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th>Judul Masalah</th>
                                        <th>Kategori</th>
                                        <th>Pelapor</th>
                                        <th>Prioritas</th>
                                        <th class="text-center">Status</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($bugs)) : ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="fas fa-folder-open fa-3x mb-3 text-gray-300"></i><br>
                                                <p>Belum ada laporan yang masuk pada periode ini.</p>
                                            </td>
                                        </tr>
                                    <?php else : ?>
                                        <?php $i = 1; foreach ($bugs as $row) : ?>
                                        <tr>
                                            <td class="align-middle" style="cursor: pointer;" onclick="window.location.href='detail-bug.php?id=<?= $row['bug_id']; ?>'">
    
    <span class="font-weight-bold text-primary">
        <?= $row['title']; ?>
    </span>
    
    <br>
    
    <small class="text-muted">
        <i class="far fa-clock mr-1"></i> <?= date('d M Y, H:i', strtotime($row['created_at'])); ?> WIB
    </small>
</td>
                                            <td class="align-middle"><?= $row['category_name']; ?></td>
                                            <td class="align-middle">
                                                <i class="fas fa-user-circle text-muted mr-1"></i> <?= $row['pelapor']; ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php if($row['priority_name'] == 'Critical'): ?>
                                                    <span class="badge badge-danger px-2 py-1"><i class="fas fa-fire mr-1"></i>Critical</span>
                                                <?php elseif($row['priority_name'] == 'High'): ?>
                                                    <span class="badge badge-warning px-2 py-1">High</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info px-2 py-1"><?= $row['priority_name']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php 
                                                    $st = $row['status'];
                                                    $badge = 'secondary';
                                                    if($st == 'Open') $badge = 'danger';
                                                    if($st == 'Assigned') $badge = 'info';
                                                    if($st == 'In Progress') $badge = 'primary';
                                                    if($st == 'Resolved') $badge = 'success';
                                                    if($st == 'Closed') $badge = 'dark';
                                                ?>
                                                <span class="badge badge-<?= $badge; ?> px-3 py-1 text-uppercase" style="letter-spacing: 0.5px;"><?= $st; ?></span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group">
                                                    <a href="detail-bug.php?id=<?= $row['bug_id']; ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                    
                                                    <a href="hapus.php?id=<?= $row['bug_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus permanen data ini?');" title="Hapus Data">
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
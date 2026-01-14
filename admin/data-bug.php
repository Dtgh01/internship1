<?php
session_start();
require '../function.php';

// 1. Cek Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Query Ambil Semua Data Bug
// Kita JOIN ke users, categories, dan priorities biar yang muncul Nama, bukan Angka ID
$query = "SELECT bugs.*, users.name as pelapor, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN users ON bugs.user_id = users.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
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
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-table mr-1"></i> Daftar Semua Laporan Masuk
                                </h3>
                                <a href="cetak-laporan.php" target="_blank" class="btn btn-primary btn-sm shadow-sm">
                                    <i class="fas fa-print"></i> Cetak PDF
                                </a>
                            </div>
                        </div>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
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
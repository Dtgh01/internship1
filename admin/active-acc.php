<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Query Ambil Semua User (Kecuali akun sendiri biar gak kehapus)
$my_id = $_SESSION['login']['user_id'];
$users = query("SELECT * FROM users WHERE user_id != $my_id ORDER BY role ASC, name ASC");

include 'templates/header.php';
include 'templates/sidebar-home.php'; 
?>

<style>
    .content-wrapper { background-color: #f0f2f5 !important; }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">Manajemen Pengguna</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-users"></i> Daftar Akun Aktif</h5>
                        <a href="user-tambah.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-user-plus"></i> Tambah User Baru
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <table id="example1" class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role / Peran</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($users as $u) : ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td>
                                    <b><?= $u['name']; ?></b>
                                </td>
                                <td><?= $u['email']; ?></td>
                                <td>
                                    <?php 
                                        $role = $u['role'];
                                        $badge = 'secondary';
                                        if($role == 'admin') $badge = 'danger';     // Admin = Merah
                                        if($role == 'developer') $badge = 'info';   // Dev = Biru
                                        if($role == 'user') $badge = 'success';     // User = Hijau
                                    ?>
                                    <span class="badge badge-<?= $badge; ?> p-2 px-3 rounded-pill">
                                        <?= strtoupper($role); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="user-hapus.php?id=<?= $u['user_id']; ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-pill" 
                                       onclick="return confirm('Yakin mau menghapus user ini? Data laporannya mungkin akan hilang/error.')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include 'templates/footer.php'; ?>
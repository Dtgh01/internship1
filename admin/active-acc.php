<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

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
            <h1 class="m-0 text-dark font-weight-bold ml-1">Manajemen Pengguna</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-users"></i> Daftar Akun Aktif</h5>
                        <a href="user-tambah.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-user-plus"></i> Tambah User
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
                                <th>Role</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($users as $u) : ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><b><?= $u['name']; ?></b></td>
                                <td><?= $u['email']; ?></td>
                                <td>
                                    <?php 
                                        $role = $u['role'];
                                        $badge = ($role == 'admin') ? 'danger' : (($role == 'developer') ? 'info' : 'success');
                                    ?>
                                    <span class="badge badge-<?= $badge; ?> px-3 py-2 rounded-pill"><?= strtoupper($role); ?></span>
                                </td>
                                <td>
                                    <a href="user-edit.php?id=<?= $u['user_id']; ?>" class="btn btn-sm btn-warning rounded-pill" title="Edit / Reset Password">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="user-hapus.php?id=<?= $u['user_id']; ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-pill ml-1" 
                                       onclick="return confirm('Yakin hapus user ini?')" title="Hapus User">
                                        <i class="fas fa-trash"></i>
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
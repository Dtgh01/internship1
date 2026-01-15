<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int)$_GET['id'];
$user = query("SELECT * FROM users WHERE user_id = $id")[0];

if (isset($_POST['update'])) {
    $name = htmlspecialchars($_POST['name']);
    $role = $_POST['role'];
    $password_baru = $_POST['password'];

    // 1. Update Nama & Role
    $query = "UPDATE users SET name = '$name', role = '$role' WHERE user_id = $id";
    mysqli_query($conn, $query);

    // 2. Update Password (Hanya jika diisi)
    if (!empty($password_baru)) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password = '$password_hash' WHERE user_id = $id");
    }

    echo "<script>alert('Data user berhasil diupdate!'); document.location.href = 'active-acc.php';</script>";
}

include 'templates/header.php';
include 'templates/sidebar-home.php'; 
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1>Edit Pengguna</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card card-warning shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-edit"></i> Edit Data User</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label>Email (Tidak bisa diubah)</label>
                                    <input type="text" class="form-control" value="<?= $user['email']; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control" value="<?= $user['name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" class="form-control">
                                        <option value="user" <?= ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                        <option value="developer" <?= ($user['role'] == 'developer') ? 'selected' : ''; ?>>Developer</option>
                                        <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label class="text-danger">Reset Password</label>
                                    <input type="text" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password">
                                    <small class="text-muted">Isi kolom ini hanya jika user meminta reset password.</small>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="update" class="btn btn-warning btn-block font-weight-bold">Simpan Perubahan</button>
                                    <a href="active-acc.php" class="btn btn-secondary btn-block">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'templates/footer.php'; ?>
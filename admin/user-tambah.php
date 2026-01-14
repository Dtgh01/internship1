<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// LOGIKA TAMBAH USER
if (isset($_POST['tambah'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $role = $_POST['role'];
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 1. Cek Email Kembar
    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_fetch_assoc($cek)) {
        echo "<script>alert('Email sudah terdaftar!');</script>";
    } else {
        // 2. Enkripsi Password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert ke DB
        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password_hash', '$role')";
        mysqli_query($conn, $query);

        if (mysqli_affected_rows($conn) > 0) {
            echo "<script>
                    alert('User berhasil ditambahkan!');
                    document.location.href = 'active-acc.php';
                  </script>";
        } else {
            echo "<script>alert('Gagal menambahkan user!');</script>";
        }
    }
}

include 'templates/header.php';
include 'templates/sidebar-home.php'; 
?>

<div class="content-wrapper" style="background-color: #f0f2f5;">
    <div class="content-header">
        <div class="container-fluid">
            <h1>Tambah Pengguna Baru</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm" style="border-radius: 15px;">
                        <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0"><i class="fas fa-user-plus"></i> Form Registrasi User</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Contoh: Budi Santoso">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" required placeholder="email@contoh.com">
                                </div>
                                <div class="form-group">
                                    <label>Password Default</label>
                                    <input type="text" name="password" class="form-control" required placeholder="Masukkan password...">
                                </div>
                                <div class="form-group">
                                    <label>Role / Peran</label>
                                    <select name="role" class="form-control" required>
                                        <option value="">-- Pilih Role --</option>
                                        <option value="developer">👨‍💻 Developer (Tim IT)</option>
                                        <option value="admin">👮‍♂️ Admin (Pengelola)</option>
                                        <option value="user">👤 User (Pelapor Biasa)</option>
                                    </select>
                                    <small class="text-muted">Pilih <b>Developer</b> jika ingin menambah teknisi bug.</small>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <a href="active-acc.php" class="btn btn-secondary btn-block rounded-pill">Batal</a>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" name="tambah" class="btn btn-primary btn-block rounded-pill">Simpan</button>
                                    </div>
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
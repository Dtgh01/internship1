<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// LOGIKA TAMBAH KATEGORI
if (isset($_POST['tambah'])) {
    $kategori = htmlspecialchars($_POST['category_name']);
    
    $cek = query("SELECT * FROM categories WHERE category_name = '$kategori'");
    if($cek) {
        echo "<script>alert('Kategori sudah ada!');</script>";
    } else {
        mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$kategori')");
        echo "<script>alert('Berhasil ditambah!'); window.location='master-kategori.php';</script>";
    }
}

// LOGIKA HAPUS KATEGORI
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM categories WHERE category_id = $id");
    echo "<script>alert('Dihapus!'); window.location='master-kategori.php';</script>";
}

$categories = query("SELECT * FROM categories");
include 'templates/header.php';
include 'templates/sidebar-home.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <h1 class="m-0 text-dark ml-2">Master Data Kategori</h1>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-5">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Tambah Kategori</h3></div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label>Nama Kategori</label>
                                    <input type="text" name="category_name" class="form-control" placeholder="Contoh: Jaringan, Server..." required>
                                </div>
                                <button type="submit" name="tambah" class="btn btn-primary btn-block">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card card-outline card-info">
                        <div class="card-header"><h3 class="card-title">List Kategori Tersedia</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead><tr><th>ID</th><th>Nama Kategori</th><th style="width: 40px">Aksi</th></tr></thead>
                                <tbody>
                                    <?php foreach ($categories as $c) : ?>
                                    <tr>
                                        <td><?= $c['category_id']; ?></td>
                                        <td><?= $c['category_name']; ?></td>
                                        <td>
                                            <a href="?hapus=<?= $c['category_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori ini?');"><i class="fas fa-trash"></i></a>
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
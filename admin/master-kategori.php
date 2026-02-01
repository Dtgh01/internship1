<?php
session_start();
require '../function.php';

if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}

// 1. TAMBAH KATEGORI
if (isset($_POST['add_cat'])) {
    $cat = htmlspecialchars($_POST['category_name']);
    mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$cat')");
    echo "<script>alert('Kategori ditambahkan!'); window.location='master-kategori.php';</script>";
}

// 2. HAPUS KATEGORI
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Cek apakah dipakai di bugs
    $cek = query("SELECT * FROM bugs WHERE category_id = $id");
    if(count($cek) > 0) {
        echo "<script>alert('Gagal! Kategori ini sedang digunakan oleh laporan bug.'); window.location='master-kategori.php';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE category_id = $id");
        echo "<script>alert('Kategori dihapus!'); window.location='master-kategori.php';</script>";
    }
}

$categories = query("SELECT * FROM categories ORDER BY category_id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Master Kategori | BugTracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/skin.css">
    
    <style>
        .modal-content { background-color: #1e293b; color: #fff; border: 1px solid rgba(255,255,255,0.1); }
        .close { color: #fff; opacity: 1; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
  </nav>

  <?php include 'templates/sidebar-home.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0 text-white font-weight-bold">Master Kategori</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-8">
            <div class="card card-primary card-outline shadow-lg">
              <div class="card-header border-0">
                <h3 class="card-title text-white mt-1">Daftar Kategori Bug</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary font-weight-bold" data-toggle="modal" data-target="#modalCat">
                        <i class="fas fa-plus mr-1"></i> Tambah Kategori
                    </button>
                </div>
              </div>
              <div class="card-body table-responsive">
                <table id="tableCat" class="table table-hover">
                  <thead>
                    <tr>
                      <th width="10%">No</th>
                      <th>Nama Kategori</th>
                      <th class="text-center" width="20%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; foreach ($categories as $row) : ?>
                    <tr>
                      <td><?= $i++; ?></td>
                      <td><span class="text-white font-weight-bold"><?= $row['category_name']; ?></span></td>
                      <td class="text-center">
                         <a href="master-kategori.php?hapus=<?= $row['category_id']; ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Hapus kategori ini?')">
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
          
          <div class="col-md-4">
              <div class="callout callout-info bg-dark">
                  <h5 class="text-info"><i class="fas fa-info-circle"></i> Info</h5>
                  <p>Kategori digunakan untuk mengelompokkan jenis bug. Contoh: UI/UX, Backend, Database, Security, dll.</p>
              </div>
          </div>

        </div>
      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong>Copyright &copy; 2026 BugTracker.</strong></footer>
</div>

<div class="modal fade" id="modalCat" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold">Tambah Kategori</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="" method="POST">
          <div class="modal-body">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="category_name" class="form-control" placeholder="Contoh: Database Error" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" name="add_cat" class="btn btn-primary">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
<script>
  $(function () {
    $("#tableCat").DataTable({ "responsive": true, "autoWidth": false, "searching": false, "lengthChange": false });
  });
</script>
</body>
</html>
<?php
session_start();
require '../function.php';

// Cek Akses Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}

// 1. LOGIKA TAMBAH USER
if (isset($_POST['add_user'])) {
    $name  = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $role  = $_POST['role'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek Email Kembar
    $cek = query("SELECT email FROM users WHERE email = '$email'");
    if (count($cek) > 0) {
        echo "<script>alert('Email sudah terdaftar!');</script>";
    } else {
        $query = "INSERT INTO users (name, email, password, role, created_at) VALUES ('$name', '$email', '$pass', '$role', NOW())";
        mysqli_query($conn, $query);
        if (mysqli_affected_rows($conn) > 0) {
            echo "<script>alert('User berhasil ditambahkan!'); window.location='active-acc.php';</script>";
        }
    }
}

// 2. LOGIKA EDIT USER (BARU)
if (isset($_POST['edit_user'])) {
    $id    = $_POST['user_id'];
    $name  = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $role  = $_POST['role'];
    $pass  = $_POST['password']; // Password baru (opsional)

    // Cek Password: Jika kosong, jangan diubah
    if (empty($pass)) {
        $query = "UPDATE users SET name = '$name', email = '$email', role = '$role' WHERE user_id = $id";
    } else {
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        $query = "UPDATE users SET name = '$name', email = '$email', role = '$role', password = '$pass_hash' WHERE user_id = $id";
    }

    mysqli_query($conn, $query);
    echo "<script>alert('Data user berhasil diperbarui!'); window.location='active-acc.php';</script>";
}

// 3. LOGIKA HAPUS USER
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $id");
    echo "<script>alert('User dihapus!'); window.location='active-acc.php';</script>";
}

// AMBIL DATA USER
$users = query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen User | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/skin.css">

    <style>
        /* Modal Dark Mode Fix */
        .modal-content { background-color: #1e293b; color: #fff; border: 1px solid rgba(255,255,255,0.1); }
        .modal-header, .modal-footer { border-color: rgba(255,255,255,0.1); }
        .close { color: #fff; text-shadow: none; opacity: 1; }
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
        <h1 class="m-0 text-white font-weight-bold">Manajemen User</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card card-primary card-outline shadow-lg">
              
              <div class="card-header border-0">
                <h3 class="card-title text-white mt-1">Daftar Pengguna Sistem</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary font-weight-bold" data-toggle="modal" data-target="#modalAdd">
                        <i class="fas fa-plus mr-1"></i> Tambah User Baru
                    </button>
                </div>
              </div>
              
              <div class="card-body table-responsive">
                <table id="tableUser" class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th width="5%">No</th>
                      <th>Nama Lengkap</th>
                      <th>Email</th>
                      <th>Role</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; foreach ($users as $row) : ?>
                    <tr>
                      <td><?= $i++; ?></td>
                      <td>
                          <div class="d-flex align-items-center">
                              <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['name']); ?>&background=random&color=fff&size=32" class="img-circle mr-2" style="width: 32px;">
                              <span class="font-weight-bold text-white"><?= $row['name']; ?></span>
                          </div>
                      </td>
                      <td class="text-muted"><?= $row['email']; ?></td>
                      <td>
                        <?php 
                            $role = $row['role'];
                            $badge = 'secondary';
                            if($role == 'admin') $badge = 'danger'; 
                            if($role == 'developer') $badge = 'warning'; 
                            if($role == 'user') $badge = 'info'; 
                        ?>
                        <span class="badge badge-<?= $badge; ?> px-3 py-1 text-uppercase"><?= $role; ?></span>
                      </td>
                      <td class="text-center">
                         <button type="button" class="btn btn-xs btn-outline-warning mr-1" data-toggle="modal" data-target="#modalEdit<?= $row['user_id']; ?>">
                             <i class="fas fa-edit"></i>
                         </button>

                         <?php if($row['user_id'] != $_SESSION['login']['user_id']): ?>
                             <a href="active-acc.php?hapus=<?= $row['user_id']; ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Yakin hapus user ini? Data bug mereka juga akan terhapus!')">
                                <i class="fas fa-trash"></i>
                             </a>
                         <?php endif; ?>
                      </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $row['user_id']; ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title font-weight-bold">Edit User: <?= $row['name']; ?></h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                          </div>
                          <form action="" method="POST">
                              <div class="modal-body">
                                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                                
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control" value="<?= $row['name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= $row['email']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Reset Password <small class="text-muted">(Kosongkan jika tidak ingin ubah)</small></label>
                                    <input type="password" name="password" class="form-control" placeholder="Isi untuk ganti password...">
                                </div>
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" class="form-control">
                                        <option value="user" <?= ($row['role']=='user')?'selected':''; ?>>User</option>
                                        <option value="developer" <?= ($row['role']=='developer')?'selected':''; ?>>Developer</option>
                                        <option value="admin" <?= ($row['role']=='admin')?'selected':''; ?>>Admin</option>
                                    </select>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" name="edit_user" class="btn btn-warning font-weight-bold">Simpan Perubahan</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>
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
  
  <footer class="main-footer"><strong>Copyright &copy; 2026 BugTracker.</strong></footer>
</div>

<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Tambah User Baru</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="" method="POST">
          <div class="modal-body">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password Default</label>
                <input type="text" name="password" class="form-control" value="12345" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="user">User</option>
                    <option value="developer">Developer</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" name="add_user" class="btn btn-primary">Simpan</button>
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
    $("#tableUser").DataTable({ "responsive": true, "autoWidth": false, "order": [] });
  });
</script>
</body>
</html>
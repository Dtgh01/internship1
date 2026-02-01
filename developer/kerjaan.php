<?php
session_start();
require '../function.php';

// Cek Akses
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    header("Location: ../auth/login.php"); exit;
}

$dev_id = $_SESSION['login']['user_id'];
$id_bug = isset($_GET['id']) ? $_GET['id'] : null;

// ==========================================
// MODE 1: DETAIL & EKSEKUSI (Jika ada ID)
// ==========================================
if ($id_bug) {
    
    // LOGIKA UPDATE STATUS
    if (isset($_POST['update_kerjaan'])) {
        $status_baru = $_POST['status'];
        $old_status  = $_POST['old_status'];
        
        mysqli_query($conn, "UPDATE bugs SET status = '$status_baru', updated_at = NOW() WHERE bug_id = $id_bug");
        
        // Catat History
        if ($status_baru != $old_status) {
            mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_at, changed_by) 
                                 VALUES ('$id_bug', '$old_status', '$status_baru', NOW(), '$dev_id')");
        }
        
        echo "<script>alert('Status berhasil diupdate!'); window.location='kerjaan.php?id=$id_bug';</script>";
    }

    // Ambil Data Detail
    $query = "SELECT bugs.*, u.name as pelapor, c.category_name, p.priority_name 
              FROM bugs 
              JOIN users u ON bugs.user_id = u.user_id
              JOIN categories c ON bugs.category_id = c.category_id
              JOIN priorities p ON bugs.priority_id = p.priority_id
              WHERE bugs.bug_id = $id_bug AND bugs.assigned_to = $dev_id";
    
    $bug = query($query);
    
    if (empty($bug)) {
        echo "<script>alert('Tugas tidak ditemukan atau bukan milik Anda!'); window.location='kerjaan.php';</script>";
        exit;
    }
    $bug = $bug[0];

    // --- LOGIKA CEK TIPE FILE (GAMBAR ATAU BUKAN) ---
    $ext = pathinfo($bug['attachment'], PATHINFO_EXTENSION);
    $is_image = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
    
    // Ambil History
    $histories = query("SELECT h.*, u.name as actor, u.role as actor_role 
                        FROM bug_status_history h
                        JOIN users u ON h.changed_by = u.user_id
                        WHERE bug_id = $id_bug ORDER BY changed_at DESC");

    $is_detail_mode = true;

} else {
    // ==========================================
    // MODE 2: LIST DAFTAR TUGAS (Jika tidak ada ID)
    // ==========================================
    $is_detail_mode = false;
    
    $my_tasks = query("SELECT bugs.*, u.name as pelapor, p.priority_name, c.category_name 
                       FROM bugs
                       JOIN users u ON bugs.user_id = u.user_id
                       JOIN priorities p ON bugs.priority_id = p.priority_id
                       JOIN categories c ON bugs.category_id = c.category_id
                       WHERE bugs.assigned_to = $dev_id
                       ORDER BY 
                       CASE WHEN bugs.status = 'In Progress' THEN 1 
                            WHEN bugs.status = 'Assigned' THEN 2 
                            ELSE 3 END, 
                       bugs.priority_id DESC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Tugas | BugTracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/skin.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto"><li class="nav-item"><span class="nav-link text-white">Developer Mode</span></li></ul>
  </nav>

  <?php include 'sidebar.php'; ?>

  <div class="content-wrapper">
    
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
              <h1 class="m-0 text-white font-weight-bold">
                  <?= ($is_detail_mode) ? 'Detail Perbaikan' : 'Daftar Kerjaan'; ?>
              </h1>
          </div>
          <div class="col-sm-6 text-right">
              <?php if($is_detail_mode): ?>
                  <a href="kerjaan.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali ke List</a>
              <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <?php if($is_detail_mode): ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-warning card-outline shadow-lg mb-4">
                        <div class="card-header"><h3 class="card-title text-white font-weight-bold">Update Progress</h3></div>
                        <form action="" method="POST">
                            <div class="card-body">
                                <input type="hidden" name="old_status" value="<?= $bug['status']; ?>">
                                <div class="form-group">
                                    <label class="text-white">Status Saat Ini:</label>
                                    <select name="status" class="form-control bg-dark text-white border-secondary">
                                        <option value="Assigned" <?= ($bug['status']=='Assigned')?'selected':''; ?>>Assigned (Diterima)</option>
                                        <option value="In Progress" <?= ($bug['status']=='In Progress')?'selected':''; ?>>In Progress (Sedang Dikerjakan)</option>
                                        <option value="Resolved" <?= ($bug['status']=='Resolved')?'selected':''; ?>>Resolved (Selesai Diperbaiki)</option>
                                    </select>
                                    <small class="text-muted mt-2 d-block">*Pilih <b>Resolved</b> jika bug sudah selesai diperbaiki.</small>
                                </div>
                                <button type="submit" name="update_kerjaan" class="btn btn-warning font-weight-bold btn-block text-dark">
                                    <i class="fas fa-save mr-2"></i> Simpan Progress
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card card-dark card-outline">
                        <div class="card-header"><h3 class="card-title text-white"><?= $bug['title']; ?></h3></div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6"><span class="text-muted">Pelapor:</span> <b class="text-white"><?= $bug['pelapor']; ?></b></div>
                                <div class="col-6"><span class="text-muted">Prioritas:</span> <span class="badge badge-<?= ($bug['priority_name']=='Critical'?'danger':'info'); ?>"><?= $bug['priority_name']; ?></span></div>
                            </div>
                            
                            <label class="text-info">Deskripsi Masalah:</label>
                            <div class="p-3 mb-4 rounded border border-secondary" style="background: rgba(0,0,0,0.2);">
                                <?= nl2br($bug['description']); ?>
                            </div>

                            <?php if(!empty($bug['attachment'])): ?>
                                <label class="text-info d-block">Bukti Lampiran:</label>
                                
                                <?php if($is_image): ?>
                                    <div class="text-center bg-dark p-2 rounded border border-secondary mt-2">
                                        <img src="../assets/uploads/<?= $bug['attachment']; ?>" class="img-fluid rounded shadow-sm" style="max-height: 500px; object-fit: contain;">
                                        <br>
                                        <a href="../assets/uploads/<?= $bug['attachment']; ?>" target="_blank" class="btn btn-xs btn-outline-secondary mt-2">
                                            <i class="fas fa-expand mr-1"></i> Lihat Ukuran Penuh
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="../assets/uploads/<?= $bug['attachment']; ?>" target="_blank" class="btn btn-outline-light mt-2">
                                        <i class="fas fa-file-download mr-1"></i> Buka Lampiran (<?= strtoupper($ext); ?>)
                                    </a>
                                <?php endif; ?>

                            <?php else: ?>
                                <span class="text-muted font-italic">Tidak ada lampiran.</span>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <h5 class="text-white mb-3">Riwayat Aktivitas</h5>
                    <div class="timeline">
                        <?php foreach($histories as $log): ?>
                        <div>
                            <i class="fas fa-circle bg-gray"></i>
                            <div class="timeline-item" style="background: rgba(30,41,59,0.5); border: 1px solid #444;">
                                <span class="time"><i class="fas fa-clock"></i> <?= date('d M H:i', strtotime($log['changed_at'])); ?></span>
                                <h3 class="timeline-header" style="border-bottom: 1px solid #444; color: #ddd;">
                                    <?= $log['actor']; ?>
                                </h3>
                                <div class="timeline-body text-white">
                                    Status: <b class="text-warning"><?= $log['new_status']; ?></b>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div><i class="fas fa-clock bg-gray"></i></div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline bg-dark border-secondary shadow-lg">
                        <div class="card-body table-responsive">
                            <table id="tableKerjaan" class="table table-hover text-white">
                                <thead>
                                    <tr>
                                        <th>Prioritas</th>
                                        <th>Judul Tugas</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($my_tasks as $row): ?>
                                    <tr onclick="window.location.href='kerjaan.php?id=<?= $row['bug_id']; ?>'" style="cursor: pointer;">
                                        <td>
                                            <span class="badge badge-<?= ($row['priority_name']=='Critical'?'danger':'info'); ?>">
                                                <?= $row['priority_name']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?= $row['title']; ?></span><br>
                                            <small class="text-muted">Pelapor: <?= $row['pelapor']; ?></small>
                                        </td>
                                        <td><?= $row['category_name']; ?></td>
                                        <td>
                                            <?php 
                                                $s = $row['status'];
                                                $cls = ($s=='In Progress') ? 'warning' : (($s=='Resolved') ? 'success' : 'danger');
                                            ?>
                                            <span class="badge badge-<?= $cls; ?>"><?= $s; ?></span>
                                        </td>
                                        <td>
                                            <a href="kerjaan.php?id=<?= $row['bug_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Kerjakan
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong>Copyright &copy; 2026 BugTracker.</strong></footer>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
<script>
  $(function () {
    $("#tableKerjaan").DataTable({ 
        "responsive": true, 
        "autoWidth": false,
        "order": [], 
        "language": { "emptyTable": "Belum ada tugas yang masuk. Santai dulu!" }
    });
  });
</script>
</body>
</html>
<?php
session_start();
require '../function.php';

if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}

// FILTER LOGIC (BULAN & TAHUN)
// Default: Bulan & Tahun saat ini
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query Filter
// Kita gunakan fungsi MONTH() dan YEAR() dari SQL
$where = "WHERE MONTH(bugs.created_at) = '$bulan_pilih' AND YEAR(bugs.created_at) = '$tahun_pilih'";

$query = "SELECT bugs.*, users.name as pelapor, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN users ON bugs.user_id = users.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          $where
          ORDER BY bugs.created_at DESC";
$bugs = query($query);

// Helper Array Bulan
$list_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Bug | BugTracker</title>

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
  </nav>

  <?php include 'templates/sidebar-home.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0 text-white font-weight-bold">Manajemen Laporan</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-12">
            <div class="card card-primary card-outline shadow-lg">
              
              <div class="card-header border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-white mt-1"><i class="fas fa-list mr-1"></i> Data Masuk</h3>
                    
                    <form action="" method="GET" class="form-inline">
                        <label class="text-white mr-2">Periode:</label>
                        
                        <select name="bulan" class="form-control form-control-sm bg-dark text-white border-secondary mr-2">
                            <?php foreach($list_bulan as $key => $val): ?>
                                <option value="<?= $key; ?>" <?= ($key == $bulan_pilih) ? 'selected' : ''; ?>>
                                    <?= $val; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="tahun" class="form-control form-control-sm bg-dark text-white border-secondary mr-2">
                            <?php for($t = date('Y'); $t >= 2024; $t--): ?>
                                <option value="<?= $t; ?>" <?= ($t == $tahun_pilih) ? 'selected' : ''; ?>>
                                    <?= $t; ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        
                        <a href="cetak-laporan.php?bulan=<?= $bulan_pilih; ?>&tahun=<?= $tahun_pilih; ?>" target="_blank" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </form>
                </div>
              </div>
              
              <div class="card-body table-responsive">
                <table id="example1" class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th width="5%">No</th>
                      <th>Judul Masalah</th>
                      <th>Pelapor</th>
                      <th>Prioritas</th>
                      <th>Status</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; foreach ($bugs as $row) : ?>
                    <tr onclick="window.location.href='detail-bug.php?id=<?= $row['bug_id']; ?>'" style="cursor: pointer;">
                      <td><?= $i++; ?></td>
                      <td>
                          <span class="font-weight-bold text-info"><?= $row['title']; ?></span><br>
                          <small class="text-muted"><?= date('d M Y', strtotime($row['created_at'])); ?></small>
                      </td>
                      <td><?= $row['pelapor']; ?></td>
                      <td><span class="badge badge-<?= ($row['priority_name']=='Critical'?'danger':'info'); ?>"><?= $row['priority_name']; ?></span></td>
                      <td>
                        <?php 
                            $st = $row['status'];
                            $badge = ($st == 'Open') ? 'danger' : (($st == 'Resolved') ? 'success' : 'secondary');
                        ?>
                        <span class="badge badge-<?= $badge; ?>"><?= $st; ?></span>
                      </td>
                      <td class="text-center">
                         <a href="hapus.php?id=<?= $row['bug_id']; ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Hapus permanen?')">
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
        </div>

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
    $("#example1").DataTable({ "responsive": true, "autoWidth": false, "order": [] });
  });
</script>
</body>
</html>
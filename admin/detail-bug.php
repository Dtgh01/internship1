<?php
session_start();
require '../function.php';

// 1. Cek Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil ID dari URL
$id_bug = (int)$_GET['id'];

// ==========================================
// 2. PROSES ASSIGN DEVELOPER (POST)
// ==========================================
if (isset($_POST['assign_dev'])) {
    $dev_id = (int)$_POST['developer_id'];
    $admin_id = $_SESSION['login']['user_id'];
    
    // Update Data: Set Developer & Ubah Status jadi 'Assigned'
    // Kita set status 'Assigned' biar developer tau dia dapet tugas baru
    $query = "UPDATE bugs SET assigned_to = $dev_id, status = 'Assigned' WHERE bug_id = $id_bug";
    mysqli_query($conn, $query);

    // Catat History (Penting buat jejak digital)
    $queryLog = "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by)
                 VALUES ($id_bug, 'Open', 'Assigned', $admin_id)";
    mysqli_query($conn, $queryLog);

    echo "<script>
            alert('Berhasil menunjuk Developer!'); 
            window.location.href='detail-bug.php?id=$id_bug';
          </script>";
}

// ==========================================
// 3. AMBIL DATA DETAIL BUG
// ==========================================
$queryBug = "SELECT bugs.*, 
             users.name as pelapor, 
             dev.name as developer_name,
             categories.category_name, 
             priorities.priority_name
             FROM bugs
             JOIN users ON bugs.user_id = users.user_id
             LEFT JOIN users as dev ON bugs.assigned_to = dev.user_id 
             JOIN categories ON bugs.category_id = categories.category_id
             JOIN priorities ON bugs.priority_id = priorities.priority_id
             WHERE bugs.bug_id = $id_bug";

$result = query($queryBug);

// Kalau ID salah/gak ketemu, balikin ke tabel
if (empty($result)) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='data-bug.php';</script>";
    exit;
}
$data = $result[0];

// 4. AMBIL LIST DEVELOPER (Buat Dropdown Pilihan)
$developers = query("SELECT * FROM users WHERE role = 'developer'");

include 'templates/header.php';
include 'templates/sidebar-home.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail Laporan #<?= $data['bug_id']; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="data-bug.php">Data Bug</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <?= $data['title']; ?>
                            </h3>
                            <div class="card-tools">
                                <?php 
                                    $st = $data['status'];
                                    $badge = 'secondary';
                                    if($st == 'Open') $badge = 'danger';
                                    if($st == 'Assigned') $badge = 'info';
                                    if($st == 'In Progress') $badge = 'primary';
                                    if($st == 'Resolved') $badge = 'success';
                                    if($st == 'Closed') $badge = 'dark';
                                ?>
                                <span class="badge badge-<?= $badge; ?> p-2"><?= $st; ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-5">
                                    <small class="text-muted">Pelapor:</small><br>
                                    <strong><i class="fas fa-user"></i> <?= $data['pelapor']; ?></strong>
                                    <br>
                                    <small><?= date('d M Y, H:i', strtotime($data['created_at'])); ?></small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Kategori:</small><br>
                                    <strong><i class="fas fa-tag"></i> <?= $data['category_name']; ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Prioritas:</small><br>
                                    <?php if($data['priority_name'] == 'Critical'): ?>
                                        <span class="text-danger font-weight-bold"><i class="fas fa-fire"></i> Critical</span>
                                    <?php else: ?>
                                        <span class="text-info font-weight-bold"><?= $data['priority_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <strong><i class="fas fa-align-left"></i> Deskripsi Masalah</strong>
                            <p class="mt-2 p-3 bg-light rounded border" style="white-space: pre-line;"><?= $data['description']; ?></p>

                            <?php if ($data['attachment']): ?>
                                <hr>
                                <strong><i class="fas fa-paperclip"></i> Bukti Lampiran</strong><br>
                                <div class="mt-2">
                                    <a href="../assets/uploads/<?= $data['attachment']; ?>" target="_blank">
                                        <img src="../assets/uploads/<?= $data['attachment']; ?>" class="img-fluid rounded border shadow-sm" style="max-height: 400px;" alt="Bukti Bug">
                                    </a>
                                    <br>
                                    <small class="text-muted">Klik gambar untuk memperbesar</small>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary mt-3">
                                    <i class="fas fa-info-circle"></i> Tidak ada lampiran gambar.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="data-bug.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users-cog"></i> Penanganan</h3>
                        </div>
                        <div class="card-body">
                            <strong>Developer Ditunjuk:</strong>
                            <?php if($data['assigned_to']): ?>
                                <div class="alert alert-info mt-2 mb-0">
                                    <i class="fas fa-user-check"></i> <b><?= $data['developer_name']; ?></b>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-exclamation-triangle"></i> Belum ada Developer.
                                </div>
                            <?php endif; ?>

                            <?php if($data['status'] == 'Open' || $data['status'] == 'Assigned'): ?>
                                <hr>
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Tunjuk / Ganti Developer:</label>
                                        <select name="developer_id" class="form-control" required>
                                            <option value="">-- Pilih Tim Dev --</option>
                                            <?php foreach($developers as $dev): ?>
                                                <option value="<?= $dev['user_id']; ?>" <?= ($data['assigned_to'] == $dev['user_id']) ? 'selected' : ''; ?>>
                                                    <?= $dev['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="assign_dev" class="btn btn-primary btn-block">
                                        <i class="fas fa-check-circle"></i> Assign Tugas
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Status</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Oleh</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Ambil History Log
                                    $histories = query("SELECT h.*, u.name 
                                                        FROM bug_status_history h 
                                                        JOIN users u ON h.changed_by = u.user_id 
                                                        WHERE bug_id = $id_bug 
                                                        ORDER BY changed_at DESC");
                                    
                                    if(empty($histories)) : ?>
                                        <tr><td colspan="3" class="text-center text-muted">Belum ada riwayat.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($histories as $h): ?>
                                        <tr>
                                            <td><small class="badge badge-light border"><?= $h['new_status']; ?></small></td>
                                            <td><small><?= $h['name']; ?></small></td>
                                            <td><small class="text-muted"><?= date('d/m H:i', strtotime($h['changed_at'])); ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
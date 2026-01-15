<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_bug = (int)$_GET['id'];
$admin_id = $_SESSION['login']['user_id'];

// ==========================================
// 1. LOGIKA: ASSIGN DEVELOPER (+ NOTIF EMAIL)
// ==========================================
if (isset($_POST['assign_developer'])) {
    $dev_id = (int)$_POST['developer_id'];
    
    // Update Bug
    $cek_status = query("SELECT status FROM bugs WHERE bug_id = $id_bug")[0]['status'];
    $status_baru = ($cek_status == 'Open') ? 'Assigned' : $cek_status;

    $query = "UPDATE bugs SET assigned_to = $dev_id, status = '$status_baru', updated_at = NOW() WHERE bug_id = $id_bug";
    mysqli_query($conn, $query);

    // Ambil Info Developer buat Email
    $dev_info = query("SELECT name, email FROM users WHERE user_id = $dev_id")[0];
    
    // Log History
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) 
                         VALUES ($id_bug, '$cek_status', '$status_baru', $admin_id)");
    
    // KIRIM NOTIF KE DEVELOPER
    $msg = "Halo {$dev_info['name']},\n\nAnda ditugaskan untuk menangani Bug #$id_bug.\nSilakan cek dashboard Developer Anda.";
    kirimNotifikasi($dev_info['email'], "[BugTracker] Tugas Baru #$id_bug", $msg);
    
    echo "<script>alert('Berhasil menugaskan Developer & Notifikasi dikirim!'); window.location='detail-bug.php?id=$id_bug';</script>";
}

// ==========================================
// 2. LOGIKA: CLOSE BUG (+ NOTIF KE PELAPOR)
// ==========================================
if (isset($_POST['close_bug'])) {
    $alasan = htmlspecialchars($_POST['alasan']);
    $status_lama = query("SELECT status, user_id, title FROM bugs WHERE bug_id = $id_bug")[0];

    // Update jadi Closed
    mysqli_query($conn, "UPDATE bugs SET status = 'Closed', updated_at = NOW() WHERE bug_id = $id_bug");

    // Log History
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) 
                         VALUES ($id_bug, '{$status_lama['status']}', 'Closed', $admin_id)");

    // KIRIM NOTIF KE PELAPOR
    $pelapor = query("SELECT email FROM users WHERE user_id = {$status_lama['user_id']}")[0];
    $msg = "Halo,\n\nLaporan Bug Anda '{$status_lama['title']}' telah DITUTUP oleh Admin.\nCatatan: $alasan";
    kirimNotifikasi($pelapor['email'], "[BugTracker] Bug #$id_bug Ditutup", $msg);

    echo "<script>alert('Bug berhasil ditutup!'); window.location='detail-bug.php?id=$id_bug';</script>";
}

// ==========================================
// 3. LOGIKA: REOPEN BUG (BUKA KEMBALI) - FITUR BARU!
// ==========================================
if (isset($_POST['reopen_bug'])) {
    $alasan = htmlspecialchars($_POST['alasan_reopen']);
    $data_bug = query("SELECT user_id, title FROM bugs WHERE bug_id = $id_bug")[0];

    // Update jadi Open lagi
    mysqli_query($conn, "UPDATE bugs SET status = 'Open', updated_at = NOW() WHERE bug_id = $id_bug");

    // Log History
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) 
                         VALUES ($id_bug, 'Closed', 'Open', $admin_id)");

    // KIRIM NOTIF KE PELAPOR & DEVELOPER (JAGA-JAGA)
    $pelapor = query("SELECT email FROM users WHERE user_id = {$data_bug['user_id']}")[0];
    $msg = "Halo,\n\nKabar Baik! Bug #$id_bug telah DIBUKA KEMBALI (Reopen) untuk pengecekan ulang.\nCatatan: $alasan";
    kirimNotifikasi($pelapor['email'], "[BugTracker] Bug #$id_bug Reopened", $msg);

    echo "<script>alert('Bug berhasil dibuka kembali (Reopen)!'); window.location='detail-bug.php?id=$id_bug';</script>";
}

// ==========================================
// 4. AMBIL DATA BUG
// ==========================================
$query = "SELECT bugs.*, 
                 u_pelapor.name as pelapor, 
                 u_dev.name as developer,
                 c.category_name, 
                 p.priority_name
          FROM bugs
          LEFT JOIN users u_pelapor ON bugs.user_id = u_pelapor.user_id
          LEFT JOIN users u_dev ON bugs.assigned_to = u_dev.user_id
          LEFT JOIN categories c ON bugs.category_id = c.category_id
          LEFT JOIN priorities p ON bugs.priority_id = p.priority_id
          WHERE bugs.bug_id = $id_bug";

$data = query($query);
if (empty($data)) exit;
$bug = $data[0];

$developers = query("SELECT * FROM users WHERE role = 'developer'");
$histories = query("SELECT h.*, u.name as pengubah 
                    FROM bug_status_history h
                    JOIN users u ON h.changed_by = u.user_id
                    WHERE h.bug_id = $id_bug 
                    ORDER BY h.changed_at DESC");

include 'templates/header.php';
include 'templates/sidebar-home.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail Laporan #<?= $bug['bug_id']; ?></h1>
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
                            <h3 class="card-title font-weight-bold"><?= $bug['title']; ?></h3>
                            <div class="card-tools">
                                <span class="badge badge-secondary px-3 py-2"><?= $bug['status']; ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><b>Deskripsi:</b><br><?= nl2br($bug['description']); ?></p>
                            <?php if($bug['attachment']) : ?>
                                <hr><img src="../assets/uploads/<?= $bug['attachment']; ?>" class="img-fluid" style="max-height: 300px;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card card-info card-outline">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-history"></i> Timeline</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead><tr><th>Waktu</th><th>Oleh</th><th>Perubahan</th></tr></thead>
                                <tbody>
                                    <?php foreach($histories as $log): ?>
                                    <tr>
                                        <td><small><?= date('d/m/Y H:i', strtotime($log['changed_at'])); ?></small></td>
                                        <td><?= $log['pengubah']; ?></td>
                                        <td><?= $log['old_status']; ?> <i class="fas fa-arrow-right mx-1"></i> <b><?= $log['new_status']; ?></b></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Assign Developer</h3></div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <select name="developer_id" class="form-control" required>
                                        <option value="">-- Pilih Developer --</option>
                                        <?php foreach($developers as $dev): ?>
                                            <option value="<?= $dev['user_id']; ?>" <?= ($bug['assigned_to'] == $dev['user_id']) ? 'selected' : ''; ?>>
                                                <?= $dev['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="assign_developer" class="btn btn-primary btn-block">Simpan & Kirim Email</button>
                            </form>
                        </div>
                    </div>

                    <?php if($bug['status'] != 'Closed'): ?>
                        <div class="card card-danger">
                            <div class="card-header"><h3 class="card-title">Tutup Laporan</h3></div>
                            <div class="card-body">
                                <form action="" method="POST" onsubmit="return confirm('Tutup laporan ini?');">
                                    <div class="form-group">
                                        <input type="text" name="alasan" class="form-control form-control-sm" placeholder="Alasan penutupan...">
                                    </div>
                                    <button type="submit" name="close_bug" class="btn btn-danger btn-block font-weight-bold">CLOSE BUG</button>
                                </form>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="card card-success">
                            <div class="card-header"><h3 class="card-title">Buka Kembali (Reopen)</h3></div>
                            <div class="card-body">
                                <div class="alert alert-dark text-center"><i class="fas fa-lock"></i> Laporan ini <b>CLOSED</b>.</div>
                                <p class="small text-muted">Jika masalah muncul lagi, Anda bisa membuka kembali tiket ini.</p>
                                <form action="" method="POST" onsubmit="return confirm('Buka kembali laporan ini? Status akan jadi OPEN.');">
                                    <div class="form-group">
                                        <input type="text" name="alasan_reopen" class="form-control form-control-sm" placeholder="Alasan Reopen (Wajib)..." required>
                                    </div>
                                    <button type="submit" name="reopen_bug" class="btn btn-success btn-block font-weight-bold">
                                        <i class="fas fa-unlock-alt mr-1"></i> REOPEN BUG
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>
</div>
<?php include 'templates/footer.php'; ?>
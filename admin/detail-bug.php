<?php
session_start();
require '../function.php';

// 1. CEK LOGIN ADMIN
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_bug = (int)$_GET['id'];
$admin_id = $_SESSION['login']['user_id'];

// ==========================================
// A. LOGIKA UTAMA: VALIDASI (RESOLVED -> CLOSED/OPEN)
// ==========================================
if (isset($_POST['validasi_bug'])) {
    $keputusan = $_POST['validasi_bug'];
    $catatan   = htmlspecialchars($_POST['catatan_validasi']);
    
    $old_data = query("SELECT title, user_id FROM bugs WHERE bug_id = $id_bug")[0];

    if ($keputusan == 'terima') {
        $new_status = 'Closed';
        $msg_alert  = "Validasi Diterima! Bug Ditutup.";
        $msg_email  = "Laporan Bug '{$old_data['title']}' telah DITUTUP (Valid).";
    } else {
        $new_status = 'Open';
        $msg_alert  = "Validasi Ditolak! Status kembali Open.";
        $msg_email  = "Laporan Bug '{$old_data['title']}' DITOLAK (Masih Error).";
    }

    mysqli_query($conn, "UPDATE bugs SET status = '$new_status', updated_at = NOW() WHERE bug_id = $id_bug");
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) VALUES ($id_bug, 'Resolved', '$new_status', $admin_id)");

    if (function_exists('kirimNotifikasi')) {
        $pelapor = query("SELECT email FROM users WHERE user_id = {$old_data['user_id']}")[0];
        kirimNotifikasi($pelapor['email'], "[BugTracker] Update Status #$id_bug", "Halo,\n\n$msg_email\nCatatan Admin: $catatan");
    }
    echo "<script>alert('$msg_alert'); window.location='detail-bug.php?id=$id_bug';</script>";
}

// ==========================================
// B. LOGIKA STANDARD: ASSIGN, CLOSE, REOPEN
// ==========================================
if (isset($_POST['assign_developer'])) {
    $dev_id = (int)$_POST['developer_id'];
    $cek_status = query("SELECT status FROM bugs WHERE bug_id = $id_bug")[0]['status'];
    $status_baru = ($cek_status == 'Open') ? 'Assigned' : $cek_status;

    mysqli_query($conn, "UPDATE bugs SET assigned_to = $dev_id, status = '$status_baru', updated_at = NOW() WHERE bug_id = $id_bug");
    
    $dev_info = query("SELECT name, email FROM users WHERE user_id = $dev_id")[0];
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) VALUES ($id_bug, '$cek_status', '$status_baru', $admin_id)");
    
    if (function_exists('kirimNotifikasi')) {
        kirimNotifikasi($dev_info['email'], "Tugas Baru #$id_bug", "Halo, Anda dapat tugas baru.");
    }
    echo "<script>alert('Berhasil Assign!'); window.location='detail-bug.php?id=$id_bug';</script>";
}

if (isset($_POST['close_bug'])) {
    $alasan = htmlspecialchars($_POST['alasan']);
    mysqli_query($conn, "UPDATE bugs SET status = 'Closed', updated_at = NOW() WHERE bug_id = $id_bug");
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) VALUES ($id_bug, 'Open/Assigned', 'Closed', $admin_id)");
    echo "<script>alert('Bug Ditutup!'); window.location='detail-bug.php?id=$id_bug';</script>";
}

if (isset($_POST['reopen_bug'])) {
    $alasan = htmlspecialchars($_POST['alasan_reopen']);
    mysqli_query($conn, "UPDATE bugs SET status = 'Open', updated_at = NOW() WHERE bug_id = $id_bug");
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) VALUES ($id_bug, 'Closed', 'Open', $admin_id)");
    echo "<script>alert('Bug Dibuka Kembali!'); window.location='detail-bug.php?id=$id_bug';</script>";
}

// ==========================================
// C. LOGIKA TAMBAHAN: UBAH STATUS MANUAL (FORCE)
// ==========================================
if (isset($_POST['force_status_change'])) {
    $status_manual = $_POST['status_manual'];
    $status_lama_cek = query("SELECT status FROM bugs WHERE bug_id = $id_bug")[0]['status'];

    if ($status_manual != $status_lama_cek) {
        mysqli_query($conn, "UPDATE bugs SET status = '$status_manual', updated_at = NOW() WHERE bug_id = $id_bug");
        mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) VALUES ($id_bug, '$status_lama_cek', '$status_manual', $admin_id)");
        echo "<script>alert('Status berhasil diubah manual ke $status_manual'); window.location='detail-bug.php?id=$id_bug';</script>";
    } else {
        echo "<script>alert('Status tidak berubah (sama dengan sebelumnya).');</script>";
    }
}

// AMBIL DATA
$query = "SELECT bugs.*, u_pelapor.name as pelapor, u_dev.name as developer, c.category_name, p.priority_name
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
$histories = query("SELECT h.*, u.name as pengubah FROM bug_status_history h JOIN users u ON h.changed_by = u.user_id WHERE h.bug_id = $id_bug ORDER BY h.changed_at DESC");

include 'templates/header.php';
include 'templates/sidebar-home.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid"><h1 class="m-0 text-dark">Detail Laporan #<?= $bug['bug_id']; ?></h1></div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold"><?= $bug['title']; ?></h3>
                            <div class="card-tools"><span class="badge badge-secondary"><?= $bug['status']; ?></span></div>
                        </div>
                        <div class="card-body">
                            <p><?= nl2br($bug['description']); ?></p>
                            <?php if($bug['attachment']) : ?><hr><img src="../assets/uploads/<?= $bug['attachment']; ?>" class="img-fluid" style="max-height: 300px;"><?php endif; ?>
                        </div>
                    </div>
                    <div class="card card-info card-outline">
                        <div class="card-header"><h3 class="card-title">Timeline</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <?php foreach($histories as $log): ?>
                                <tr>
                                    <td><?= date('d/m H:i', strtotime($log['changed_at'])); ?></td>
                                    <td><?= $log['pengubah']; ?></td>
                                    <td><?= $log['old_status']; ?> -> <b><?= $log['new_status']; ?></b></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    
                    <?php if ($bug['status'] == 'Resolved'): ?>
                        <div class="card card-warning" style="border: 2px solid #ffc107;">
                            <div class="card-header bg-warning"><h3 class="card-title font-weight-bold">VALIDASI</h3></div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="form-group"><textarea name="catatan_validasi" class="form-control" placeholder="Catatan..." required></textarea></div>
                                    <div class="row">
                                        <div class="col-6"><button type="submit" name="validasi_bug" value="terima" class="btn btn-success btn-block font-weight-bold">TERIMA</button></div>
                                        <div class="col-6"><button type="submit" name="validasi_bug" value="tolak" class="btn btn-danger btn-block font-weight-bold">TOLAK</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php elseif ($bug['status'] == 'Closed'): ?>
                        <div class="card card-success">
                            <div class="card-header"><h3 class="card-title">Status: CLOSED</h3></div>
                            <div class="card-body">
                                <form action="" method="POST" onsubmit="return confirm('Buka kembali?');">
                                    <div class="form-group"><input type="text" name="alasan_reopen" class="form-control" placeholder="Alasan..." required></div>
                                    <button type="submit" name="reopen_bug" class="btn btn-outline-success btn-block">BUKA KEMBALI</button>
                                </form>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="card card-primary">
                            <div class="card-header"><h3 class="card-title">Assign Dev</h3></div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="form-group">
                                        <select name="developer_id" class="form-control" required>
                                            <option value="">-- Pilih --</option>
                                            <?php foreach($developers as $dev): ?><option value="<?= $dev['user_id']; ?>"><?= $dev['name']; ?></option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="assign_developer" class="btn btn-primary btn-block">Simpan</button>
                                </form>
                            </div>
                        </div>
                        <div class="card card-danger mt-3">
                            <div class="card-body">
                                <form action="" method="POST" onsubmit="return confirm('Tutup?');">
                                    <input type="text" name="alasan" class="form-control mb-2" placeholder="Alasan tutup..." required>
                                    <button type="submit" name="close_bug" class="btn btn-danger btn-block">Close Bug</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card card-secondary collapsed-card mt-3 shadow-none border">
                        <div class="card-header">
                            <h3 class="card-title">Ubah Status Manual</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body" style="background-color: #f9f9f9;">
                            <form action="" method="POST" onsubmit="return confirm('Ubah status secara paksa?');">
                                <div class="form-group">
                                    <label>Paksa Ubah ke:</label>
                                    <select name="status_manual" class="form-control" required>
                                        <option value="" disabled selected>-- Pilih Status --</option>
                                        <option value="Open">Open</option>
                                        <option value="Assigned">Assigned</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Testing">Testing</option>
                                        <option value="Resolved">Resolved</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                </div>
                                <button type="submit" name="force_status_change" class="btn btn-sm btn-secondary btn-block font-weight-bold">
                                    <i class="fas fa-edit mr-1"></i> SIMPAN PERUBAHAN
                                </button>
                            </form>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </section>
</div>
<?php include 'templates/footer.php'; ?>
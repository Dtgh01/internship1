<?php
session_start();
require '../function.php';

// Cek Role
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int)$_GET['id'];
$dev_id = $_SESSION['login']['user_id'];

// Ambil Data Bug
$q = "SELECT * FROM bugs WHERE bug_id = $id AND assigned_to = $dev_id";
$bug = query($q);

// Kalau bug tidak ditemukan atau bukan jatah dia, tendang!
if (empty($bug)) {
    echo "<script>alert('Tugas tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}
$bug = $bug[0];

// PROSES UPDATE STATUS
if (isset($_POST['update_status'])) {
    $status_baru = $_POST['status'];
    $catatan     = htmlspecialchars($_POST['catatan']); // Bisa dipake buat log
    
    // Update Table Bugs
    // (Opsional: Kalau mau simpan catatan teknis, harus tambah kolom 'solution_note' di tabel bugs, 
    // tapi buat sekarang kita simpan di history aja biar simpel)
    mysqli_query($conn, "UPDATE bugs SET status = '$status_baru' WHERE bug_id = $id");

    // Catat History + Pesan Developer
    $pesan_history = "Mengubah status jadi $status_baru. Catatan: $catatan";
    mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) 
                         VALUES ($id, '{$bug['status']}', '$status_baru', $dev_id)");

    echo "<script>alert('Status berhasil diupdate!'); window.location='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kerjakan Bug | TrimHub</title>
    <link rel="stylesheet" href="../assets/plugins/bootstrap4/css/bootstrap.min.css">
</head>
<body class="bg-white">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Penanganan Masalah #<?= $bug['bug_id']; ?></h5>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-secondary">
                        <label class="font-weight-bold">Judul Masalah:</label>
                        <h5><?= $bug['title']; ?></h5>
                        <hr>
                        <label class="font-weight-bold">Deskripsi User:</label>
                        <p><?= $bug['description']; ?></p>
                        
                        <?php if($bug['attachment']) : ?>
                            <a href="../assets/uploads/<?= $bug['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                                📜 Lihat Lampiran User
                            </a>
                        <?php endif; ?>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label><b>Update Status Pengerjaan</b></label>
                            <select name="status" class="form-control" required>
                                <option value="In Progress" <?= ($bug['status'] == 'In Progress') ? 'selected' : ''; ?>>
                                    🔧 In Progress (Sedang Dikerjakan)
                                </option>
                                <option value="Testing" <?= ($bug['status'] == 'Testing') ? 'selected' : ''; ?>>
                                    🧪 Testing (Sudah Fix, Perlu Dicek)
                                </option>
                                <option value="Resolved" <?= ($bug['status'] == 'Resolved') ? 'selected' : ''; ?>>
                                    ✅ Resolved (Selesai Tuntas)
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><b>Catatan Teknis / Progres</b></label>
                            <textarea name="catatan" class="form-control" rows="4" placeholder="Tulis apa yang sudah diperbaiki, atau kendala yang dihadapi..." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <a href="index.php" class="btn btn-secondary btn-block">Kembali</a>
                            </div>
                            <div class="col-6">
                                <button type="submit" name="update_status" class="btn btn-success btn-block">
                                    Simpan Perubahan 💾
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
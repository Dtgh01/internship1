<?php
session_start();
require 'function.php';

// 1. Cek Login User
if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil Data Dropdown (Kategori & Prioritas)
$categories = query("SELECT * FROM categories");
$priorities = query("SELECT * FROM priorities");

// 2. PROSES SUBMIT FORM
if (isset($_POST['kirim'])) {
    $user_id = $_SESSION['login']['user_id'];
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $category_id = $_POST['category_id'];
    $priority_id = $_POST['priority_id'];
    
    // A. Handle Upload Gambar
    $attachment = null;
    $error_upload = false;

    // Cek apakah user upload gambar?
    if ($_FILES['gambar']['error'] !== 4) {
        $nama_file = $_FILES['gambar']['name'];
        $ukuran_file = $_FILES['gambar']['size'];
        $tmp_name = $_FILES['gambar']['tmp_name'];
        
        // Cek Ekstensi (Hanya boleh gambar)
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'gif'];
        $ekstensi_file = explode('.', $nama_file);
        $ekstensi_file = strtolower(end($ekstensi_file));

        if (!in_array($ekstensi_file, $ekstensi_valid)) {
            echo "<script>alert('Yang anda upload bukan gambar!');</script>";
            $error_upload = true;
        }

        // Cek Ukuran (Max 2MB)
        if ($ukuran_file > 2000000) {
            echo "<script>alert('Ukuran gambar terlalu besar! (Max 2MB)');</script>";
            $error_upload = true;
        }

        // Lolos Cek -> Upload
        if (!$error_upload) {
            // Generate nama baru biar gak duplikat
            $nama_file_baru = uniqid() . '.' . $ekstensi_file;
            move_uploaded_file($tmp_name, 'assets/uploads/' . $nama_file_baru);
            $attachment = $nama_file_baru;
        }
    }

    // B. Simpan ke Database (Kalau upload aman)
    if (!$error_upload) {
        // Query Insert
        // Kolom attachment boleh NULL kalau user gak upload
        $query = "INSERT INTO bugs (user_id, title, description, category_id, priority_id, attachment, status, created_at) 
                  VALUES ('$user_id', '$title', '$description', '$category_id', '$priority_id', '$attachment', 'Open', NOW())";
        
        mysqli_query($conn, $query);

        if (mysqli_affected_rows($conn) > 0) {
            // Catat History Awal
            $bug_id = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) VALUES ($bug_id, NULL, 'Open', $user_id)");

            echo "<script>
                    alert('Laporan berhasil dikirim! Terima kasih atas kontribusi Anda.');
                    document.location.href = 'dashboard.php';
                  </script>";
        } else {
            echo "<script>alert('Gagal mengirim laporan! Silakan coba lagi.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan | TrimHub ID</title>
    
    <link rel="stylesheet" href="assets/plugins/bootstrap4/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* HEADER GRADIENT (Senada Dashboard User) */
        .header-form {
            background: linear-gradient(135deg, #007bff 0%, #00d2ff 100%);
            color: white;
            padding: 2rem 0 5rem 0;
            margin-bottom: -3rem;
            border-radius: 0 0 20px 20px;
        }
        
        .card-form {
            border: none; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="header-form shadow-sm">
    <div class="container">
        <a href="dashboard.php" class="text-white font-weight-bold text-decoration-none">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h2 class="mt-3 font-weight-bold">Laporkan Masalah Baru</h2>
        <p class="opacity-90">Jelaskan detail bug yang kamu temukan agar tim developer bisa segera memperbaikinya.</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-form">
                <div class="card-body p-4">
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Judul Masalah <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg rounded-pill" placeholder="Contoh: Tombol Login Tidak Berfungsi..." required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Kategori</label>
                                    <select name="category_id" class="form-control" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php foreach ($categories as $cat) : ?>
                                            <option value="<?= $cat['category_id']; ?>"><?= $cat['category_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Prioritas / Urgensi</label>
                                    <select name="priority_id" class="form-control" required>
                                        <option value="">-- Seberapa Mendesak? --</option>
                                        <?php foreach ($priorities as $prio) : ?>
                                            <option value="<?= $prio['priority_id']; ?>"><?= $prio['priority_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Deskripsi Detail <span class="text-danger">*</span></label>
                            <textarea name="description" rows="5" class="form-control" placeholder="Jelaskan langkah-langkah terjadinya error secara rinci..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Bukti Screenshot (Opsional)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="gambar" name="gambar" accept="image/*">
                                <label class="custom-file-label" for="gambar">Pilih file gambar...</label>
                            </div>
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB.</small>
                        </div>

                        <hr class="my-4">

                        <button type="submit" name="kirim" class="btn btn-primary btn-block btn-lg rounded-pill shadow">
                            <i class="fas fa-paper-plane"></i> Kirim Laporan
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap4/js/bootstrap.bundle.min.js"></script>
<script>
    // Tampilkan nama file di input custom bootstrap
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>

</body>
</html>
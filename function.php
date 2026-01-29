<?php
// Tampilkan error biar transparan saat dev
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================
// KONEKSI DATABASE
// ==========================================
$db_host = "localhost"; // Atau 127.0.0.1 jika localhost error
$db_user = "root";
$db_pass = "";
$db_name = "bugtrack"; 

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("<b>Koneksi Database Gagal:</b> " . mysqli_connect_error());
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("<b>Query Error:</b> " . mysqli_error($conn) . "<br>SQL: $query");
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function kirimNotifikasi($email, $subjek, $pesan) {
    if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
        return true; // Skip di localhost
    }
    $headers = "From: admin@bugtracker.com";
    @mail($email, $subjek, $pesan, $headers);
}

// ==========================================
// 1. FUNGSI UPLOAD (SUDAH DIPERBAIKI)
// ==========================================
function uploadGambar($files) {
    // LOGIKA BARU: Cek otomatis kuncinya 'attachment' atau 'foto'
    $kunci = '';
    if (isset($files['attachment'])) {
        $kunci = 'attachment'; // Untuk Bug Report
    } elseif (isset($files['foto'])) {
        $kunci = 'foto';       // Untuk Profil User
    } else {
        return false; // Tidak ada file yang dikenal
    }

    $namaFile   = $files[$kunci]['name'];
    $ukuranFile = $files[$kunci]['size'];
    $error      = $files[$kunci]['error'];
    $tmpName    = $files[$kunci]['tmp_name'];

    // Cek apakah ada file yg diupload
    if ($error === 4) {
        return false;
    }

    // Cek Ekstensi
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'pdf'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));

    if (!in_array($ekstensiGambar, $ekstensiValid)) {
        echo "<script>alert('Yang diupload bukan gambar/PDF yang valid!');</script>";
        return false;
    }

    // Cek Ukuran (Max 5MB)
    if ($ukuranFile > 5000000) {
        echo "<script>alert('Ukuran file terlalu besar (Max 5MB)!');</script>";
        return false;
    }

    // Generate Nama Baru & Upload
    $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
    
    // Auto-detect path (admin/developer folder vs root)
    $target_dir = "assets/uploads/";
    if (!file_exists($target_dir) && file_exists("../assets/uploads/")) {
        $target_dir = "../assets/uploads/";
    }
    
    move_uploaded_file($tmpName, $target_dir . $namaFileBaru);

    return $namaFileBaru;
}

// ==========================================
// 2. INSERT BUG
// ==========================================
function insertPengaduan($data, $files) {
    global $conn;

    $user_id     = $_SESSION['login']['user_id']; 
    $title       = mysqli_real_escape_string($conn, htmlspecialchars($data['title']));
    $description = mysqli_real_escape_string($conn, htmlspecialchars($data['description']));
    $category_id = (int) $data['category_id'];
    $priority_id = (int) $data['priority_id'];
    $status      = 'Open'; 

    // Upload attachment (jika ada)
    $attachment = null;
    if ($files['attachment']['error'] !== 4) {
        $attachment = uploadGambar($files);
        if (!$attachment) return false; // Gagal upload
    }

    $query = "INSERT INTO bugs (title, description, attachment, priority_id, category_id, status, user_id, created_at) 
              VALUES ('$title', '$description', '$attachment', $priority_id, $category_id, '$status', $user_id, NOW())";

    mysqli_query($conn, $query);

    if (mysqli_affected_rows($conn) > 0) {
        $bug_id = mysqli_insert_id($conn);
        // Catat History Awal
        mysqli_query($conn, "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by) VALUES ($bug_id, NULL, 'Open', $user_id)");
        return 1;
    }
    return 0;
}

// ==========================================
// 3. LAIN-LAIN
// ==========================================
function deletePengaduan($id) {
    global $conn;
    $id = (int) $id;
    mysqli_query($conn, "DELETE FROM bug_status_history WHERE bug_id=$id");
    mysqli_query($conn, "DELETE FROM bugs WHERE bug_id=$id");
    return mysqli_affected_rows($conn);
}

function registrasi($data) {
    global $conn;
    $name  = mysqli_real_escape_string($conn, htmlspecialchars($data["name"]));
    $email = mysqli_real_escape_string($conn, htmlspecialchars($data["email"]));
    $pass  = mysqli_real_escape_string($conn, $data["password"]);
    $role  = 'user'; 

    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_fetch_assoc($cek)) {
        echo "<script>alert('Email sudah terdaftar!');</script>";
        return false;
    }
    $password = password_hash($pass, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}
?>
<?php
// ==========================================
// KONEKSI DATABASE
// ==========================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bugtrack"; // Sesuaikan nama DB

$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("<h1>Mohon Maaf</h1><p>Sistem sedang mengalami gangguan koneksi. Silakan coba beberapa saat lagi.</p>");
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result) {
        // Log error di server, jangan tampilkan ke user
        error_log(mysqli_error($conn)); 
        return [];
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// ==========================================
// 1. FUNGSI UPLOAD (SECURE - MIME CHECK)
// ==========================================
function uploadGambar($files) {
    $kunci = '';
    
    // Deteksi apakah inputnya 'attachment' atau 'foto'
    if (isset($files['attachment'])) {
        $kunci = 'attachment'; 
    } elseif (isset($files['foto'])) {
        $kunci = 'foto';       
    } else {
        return false; 
    }

    $namaFile   = $files[$kunci]['name'];
    $ukuranFile = $files[$kunci]['size'];
    $error      = $files[$kunci]['error'];
    $tmpName    = $files[$kunci]['tmp_name'];

    // Cek apakah tidak ada file yang diupload
    if ($error === 4) {
        return false;
    }

    // Cek Ekstensi
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'pdf'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));

    if (!in_array($ekstensiGambar, $ekstensiValid)) {
        echo "<script>alert('Ekstensi file tidak valid! Hanya JPG, PNG, PDF.');</script>";
        return false;
    }

    // Cek MIME Type (Keamanan Tinggi)
    // Pastikan extension=fileinfo aktif di php.ini
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeTypeAsli = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $mimeAman = ['image/jpeg', 'image/png', 'application/pdf'];

    if (!in_array($mimeTypeAsli, $mimeAman)) {
        echo "<script>alert('File corrupt atau palsu terdeteksi!');</script>";
        return false;
    }

    // Cek Ukuran (Max 5MB)
    if ($ukuranFile > 5000000) {
        echo "<script>alert('Ukuran file terlalu besar (Max 5MB)!');</script>";
        return false;
    }

    // Generate Nama Baru
    $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
    
    // Cek folder upload
    $target_dir = "assets/uploads/";
    
    // Buat folder jika belum ada (Penting!)
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    move_uploaded_file($tmpName, $target_dir . $namaFileBaru);

    return $namaFileBaru;
}

// ==========================================
// 2. TAMBAH BUG (UPDATED & SECURE)
// ==========================================
// Note: Parameter $files dihapus agar sinkron dengan panggilan di form-bug.php
function tambahBug($data) {
    global $conn;

    // Ambil variabel Global $_FILES secara langsung di sini
    $files = $_FILES;

    $user_id     = $_SESSION['login']['user_id']; 
    $title       = htmlspecialchars($data['title']);
    $description = htmlspecialchars($data['description']);
    $category_id = (int) $data['category_id'];
    $priority_id = (int) $data['priority_id'];
    $status      = 'Open'; 

    // Proses Upload
    $attachment = null;
    // Cek apakah ada file attachment yang dikirim dan tidak error
    if (isset($files['attachment']) && $files['attachment']['error'] !== 4) {
        $attachment = uploadGambar($files);
        // Jika upload gagal (format salah/kegedean), return 0 (Gagal)
        if (!$attachment) return 0; 
    }

    // INSERT BUG (Prepared Statement)
    $query = "INSERT INTO bugs (title, description, attachment, priority_id, category_id, status, user_id, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $query);
    // Bind Param: s=string, i=integer
    // title(s), desc(s), attach(s), prio(i), cat(i), stat(s), user(i)
    mysqli_stmt_bind_param($stmt, "sssissi", $title, $description, $attachment, $priority_id, $category_id, $status, $user_id);
    mysqli_stmt_execute($stmt);

    // Jika Insert Berhasil
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $bug_id = mysqli_insert_id($conn);
        
        // INSERT HISTORY (Otomatis mencatat history pertama)
        $hist_query = "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by, changed_at) 
                       VALUES (?, NULL, 'Open', ?, NOW())";
        $stmt_hist = mysqli_prepare($conn, $hist_query);
        mysqli_stmt_bind_param($stmt_hist, "ii", $bug_id, $user_id);
        mysqli_stmt_execute($stmt_hist);
        
        return 1; // Sukses
    } else {
        // Debugging (Opsional: Matikan saat live)
        // echo mysqli_error($conn);
        return 0; // Gagal
    }
}

// ==========================================
// 3. FUNGSI REGISTRASI (SECURE)
// ==========================================
function registrasi($data) {
    global $conn;
    
    $name  = htmlspecialchars($data["name"]);
    $email = htmlspecialchars($data["email"]);
    $pass  = $data["password"];
    $role  = 'user'; 

    // Cek Email Kembar
    $stmt_cek = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt_cek, "s", $email);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);

    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        echo "<script>alert('Email sudah terdaftar!');</script>";
        return false;
    }

    // Enkripsi Password
    $password = password_hash($pass, PASSWORD_DEFAULT);

    // Insert User
    $query_ins = "INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt_ins = mysqli_prepare($conn, $query_ins);
    mysqli_stmt_bind_param($stmt_ins, "ssss", $name, $email, $password, $role);
    
    mysqli_stmt_execute($stmt_ins);

    return mysqli_stmt_affected_rows($stmt_ins);
}

// ==========================================
// 4. FUNGSI LAIN
// ==========================================
function deletePengaduan($id) {
    global $conn;
    $id = (int) $id; 
    // Hapus history dulu karena Foreign Key
    mysqli_query($conn, "DELETE FROM bug_status_history WHERE bug_id=$id");
    mysqli_query($conn, "DELETE FROM bugs WHERE bug_id=$id");
    return mysqli_affected_rows($conn);
}
?>
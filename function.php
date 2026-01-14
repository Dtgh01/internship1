<?php
// ==========================================
// KONEKSI DATABASE
// ==========================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bugtrack"; // Pastikan nama DB bener

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// ==========================================
// 1. INSERT BUG (Sesuai Struktur 6 Tabel)
// ==========================================
function insertPengaduan($data) 
{
    global $conn;

    // Ambil User ID dari Session (Wajib Login)
    // Pastikan di file login.php session 'user_id' diset saat berhasil login
    $user_id = $_SESSION['login']['user_id']; 

    // Sanitasi Input
    $title       = htmlspecialchars($data['title']);
    $description = htmlspecialchars($data['description']);
    $category_id = (int) $data['category_id']; // Pastikan Angka
    $priority_id = (int) $data['priority_id']; // Pastikan Angka
    
    // Default Status saat baru dibuat
    $status = 'Open';

    // Query Insert ke Tabel Utama (BUGS)
    // Kita simpan ID Kategori & Prioritas, bukan teks-nya
    $query = "INSERT INTO bugs (title, description, priority_id, category_id, status, user_id) 
              VALUES ('$title', '$description', $priority_id, $category_id, '$status', $user_id)";

    mysqli_query($conn, $query);

    // Cek jika berhasil insert bug, langsung catat ke History
    if (mysqli_affected_rows($conn) > 0) {
        $bug_id = mysqli_insert_id($conn); // Ambil ID bug yang baru jadi
        
        // Insert Log Sejarah (Audit Trail)
        $queryHistory = "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by)
                         VALUES ($bug_id, NULL, 'Open', $user_id)";
                         
        mysqli_query($conn, $queryHistory);
        return 1;
    }

    return 0;
// ==========================================
// 1. INSERT BUG (Support Upload Gambar)
// ==========================================
function insertPengaduan($data, $files) 
{
    global $conn;

    $user_id     = $_SESSION['login']['user_id']; 
    $title       = htmlspecialchars($data['title']);
    $description = htmlspecialchars($data['description']);
    $category_id = (int) $data['category_id'];
    $priority_id = (int) $data['priority_id'];
    $status      = 'Open'; // Status awal selalu Open

    // --- PROSES UPLOAD GAMBAR (Opsional) ---
    $attachment = null;
    if ($files['attachment']['error'] === 4) {
        $attachment = null; // User gak upload gambar
    } else {
        $attachment = uploadGambar($files); // Panggil fungsi upload
        if (!$attachment) {
            return false; // Gagal upload (ukuran/ekstensi salah)
        }
    }

    // Query Insert Baru
    $query = "INSERT INTO bugs (title, description, attachment, priority_id, category_id, status, user_id) 
              VALUES ('$title', '$description', '$attachment', $priority_id, $category_id, '$status', $user_id)";

    mysqli_query($conn, $query);

    if (mysqli_affected_rows($conn) > 0) {
        $bug_id = mysqli_insert_id($conn); 
        
        // Log History
        $queryHistory = "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by)
                         VALUES ($bug_id, NULL, 'Open', $user_id)";
        mysqli_query($conn, $queryHistory);
        return 1;
    }
    return 0;
}

// --- FUNGSI BANTUAN UPLOAD ---
function uploadGambar($files) {
    $namaFile   = $files['attachment']['name'];
    $ukuranFile = $files['attachment']['size'];
    $error      = $files['attachment']['error'];
    $tmpName    = $files['attachment']['tmp_name'];

    // Cek ekstensi
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'pdf'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));

    if (!in_array($ekstensiGambar, $ekstensiValid)) {
        echo "<script>alert('Yang diupload bukan gambar/PDF!');</script>";
        return false;
    }

    // Cek ukuran (Max 2MB)
    if ($ukuranFile > 2000000) {
        echo "<script>alert('Ukuran file terlalu besar (Max 2MB)!');</script>";
        return false;
    }

    // Generate nama baru biar gak kembar
    $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
    
    // Pastikan folder 'assets/uploads' sudah dibuat ya!
    move_uploaded_file($tmpName, 'assets/uploads/' . $namaFileBaru);

    return $namaFileBaru;
}
}

// ==========================================
// 2. UPDATE STATUS (Pake Fitur History) 
// ==========================================
function updateBugStatus($data) {
    global $conn;

    $bug_id     = (int) $data['bug_id'];
    $new_status = htmlspecialchars($data['status']); 
    $user_id    = $_SESSION['login']['user_id']; // Siapa yang ubah? (Admin/Dev)

    // A. Ambil status lama dulu buat dicatat
    $result = mysqli_query($conn, "SELECT status FROM bugs WHERE bug_id = $bug_id");
    $row    = mysqli_fetch_assoc($result);
    $old_status = $row['status'];

    // B. Update Status di tabel Utama
    mysqli_query($conn, "UPDATE bugs SET status='$new_status' WHERE bug_id=$bug_id");

    // C. Catat perubahan di tabel History
    // Ini yang bikin nilainya mahal di mata dosen
    $queryLog = "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by)
                 VALUES ($bug_id, '$old_status', '$new_status', $user_id)";
                 
    mysqli_query($conn, $queryLog);

    return mysqli_affected_rows($conn);
}

// ==========================================
// 3. DELETE BUG
// ==========================================
function deletePengaduan($id) {
    global $conn;
    $id = (int) $id;

    // Hapus history-nya dulu biar gak error foreign key (kalau gak pake CASCADE di DB)
    mysqli_query($conn, "DELETE FROM bug_status_history WHERE bug_id=$id");

    // Baru hapus bug-nya
    mysqli_query($conn, "DELETE FROM bugs WHERE bug_id=$id");
    
    return mysqli_affected_rows($conn);
}

// ==========================================
// 4. SEARCH (Dengan JOIN)
// ==========================================
function searchPengaduan($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, $keyword);

    // Kita cari data tapi tetap sambungin ke kategori & user biar outputnya lengkap
    $query = "SELECT bugs.*, users.name as pelapor, categories.category_name, priorities.priority_name
              FROM bugs
              JOIN users ON bugs.user_id = users.user_id
              JOIN categories ON bugs.category_id = categories.category_id
              JOIN priorities ON bugs.priority_id = priorities.priority_id
              WHERE 
                bugs.title LIKE '%$keyword%' OR 
                bugs.description LIKE '%$keyword%' OR
                users.name LIKE '%$keyword%'
              ORDER BY bugs.created_at DESC";

    return query($query);
}

// ==========================================
// 5. REGISTRASI (Disesuaikan Schema Baru)
// ==========================================
function registrasi($data) {
    global $conn;

    $name  = htmlspecialchars($data["name"]);
    $email = htmlspecialchars($data["email"]); // Pake Email, bukan NIP lagi
    $pass  = mysqli_real_escape_string($conn, $data["password"]);
    
    // Default Role
    $role = 'user'; 

    // Cek Email Kembar
    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_fetch_assoc($cek)) {
        echo "<script>alert('Email sudah terdaftar!');</script>";
        return false;
    }

    // Enkripsi Password
    $password = password_hash($pass, PASSWORD_DEFAULT);

    // Insert ke tabel users baru
    $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
    
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// ==========================================
// 6. FUNGSI LAINNYA
// ==========================================
// Update Password User
function updatePass($data) {
    global $conn;
    $id = $_SESSION['login']['user_id'];
    $password_baru = password_hash($data["password_baru"], PASSWORD_DEFAULT);

    mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE user_id=$id");
    return mysqli_affected_rows($conn);
}

?>
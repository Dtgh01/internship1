<?php
// ==========================================
// KONEKSI DATABASE (PORT 3307)
// ==========================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bugtrack"; 

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, 3307);

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

// --- FUNGSI KIRIM NOTIFIKASI EMAIL ---
function kirimNotifikasi($email_penerima, $subjek, $pesan) {
    // Setting Header Email
    $headers = "From: no-reply@bugtracker.com" . "\r\n" .
               "Reply-To: admin@bugtracker.com" . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Coba kirim email (Hanya jalan jika SMTP XAMPP sudah disetting)
    // Kalau di hosting beneran, ini langsung jalan.
    @mail($email_penerima, $subjek, $pesan, $headers);
}

// ==========================================
// 1. INSERT BUG (UPDATE: ADA NOTIFIKASI KE ADMIN)
// ==========================================
function insertPengaduan($data, $files) 
{
    global $conn;

    $user_id     = $_SESSION['login']['user_id']; 
    $user_name   = $_SESSION['login']['name']; // Ambil nama pelapor
    $title       = htmlspecialchars($data['title']);
    $description = htmlspecialchars($data['description']);
    $category_id = (int) $data['category_id'];
    $priority_id = (int) $data['priority_id'];
    $status      = 'Open'; 

    $attachment = null;
    if ($files['attachment']['error'] === 4) {
        $attachment = null; 
    } else {
        $attachment = uploadGambar($files);
        if (!$attachment) {
            return false; 
        }
    }

    $query = "INSERT INTO bugs (title, description, attachment, priority_id, category_id, status, user_id, created_at) 
              VALUES ('$title', '$description', '$attachment', $priority_id, $category_id, '$status', $user_id, NOW())";

    mysqli_query($conn, $query);

    if (mysqli_affected_rows($conn) > 0) {
        $bug_id = mysqli_insert_id($conn); 
        
        // Log History
        $queryHistory = "INSERT INTO bug_status_history (bug_id, old_status, new_status, changed_by)
                         VALUES ($bug_id, NULL, 'Open', $user_id)";
        mysqli_query($conn, $queryHistory);

        // --- KIRIM NOTIF KE SEMUA ADMIN ---
        $admins = query("SELECT email FROM users WHERE role = 'admin'");
        foreach ($admins as $adm) {
            $subjek = "[BugTracker] Laporan Baru: $title";
            $pesan  = "Halo Admin,\n\nAda laporan bug baru dari $user_name.\n\nJudul: $title\nPrioritas: Cek Dashboard\n\nSegera proses ya!";
            kirimNotifikasi($adm['email'], $subjek, $pesan);
        }

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

    $ekstensiValid = ['jpg', 'jpeg', 'png', 'pdf'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));

    if (!in_array($ekstensiGambar, $ekstensiValid)) {
        echo "<script>alert('Yang diupload bukan gambar/PDF!');</script>";
        return false;
    }

    if ($ukuranFile > 2000000) {
        echo "<script>alert('Ukuran file terlalu besar (Max 2MB)!');</script>";
        return false;
    }

    $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
    move_uploaded_file($tmpName, 'assets/uploads/' . $namaFileBaru);

    return $namaFileBaru;
}

// ==========================================
// 2. UPDATE STATUS
// ==========================================
function updateBugStatus($data) {
    // Fungsi ini jarang dipake langsung karena logicnya udah dipisah di tiap file
    // Tapi biarin aja buat cadangan
    return 0;
}

// ==========================================
// 3. DELETE BUG
// ==========================================
function deletePengaduan($id) {
    global $conn;
    $id = (int) $id;
    mysqli_query($conn, "DELETE FROM bug_status_history WHERE bug_id=$id");
    mysqli_query($conn, "DELETE FROM bugs WHERE bug_id=$id");
    return mysqli_affected_rows($conn);
}

// ==========================================
// 4. SEARCH
// ==========================================
function searchPengaduan($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, $keyword);
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
// 5. REGISTRASI
// ==========================================
function registrasi($data) {
    global $conn;
    $name  = htmlspecialchars($data["name"]);
    $email = htmlspecialchars($data["email"]);
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
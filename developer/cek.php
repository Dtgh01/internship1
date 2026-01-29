<?php
// 1. Paksa Error Muncul di Layar
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Mulai Diagnosa...</h1>";

// 2. Cek File Function
echo "<b>1. Cek File function.php...</b> ";
if (file_exists('../function.php')) {
    echo "<span style='color:green'>ADA ✅</span><br>";
    
    // Coba include (kalau ada syntax error di function.php, dia bakal mati disini)
    require '../function.php';
    echo "Load function.php berhasil.<br>";
} else {
    die("<span style='color:red'>TIDAK KETEMU! ❌</span><br>Pastikan file function.php ada di folder luar.");
}

// 3. Cek Variabel Koneksi
echo "<b>2. Cek Variabel \$conn...</b> ";
if (isset($conn) && $conn instanceof mysqli) {
    echo "<span style='color:green'>TERDEFINISI ✅</span><br>";
} else {
    die("<span style='color:red'>GAGAL! ❌</span><br>Variabel \$conn tidak terbaca atau bukan objek MySQLi.");
}

// 4. Cek Koneksi Database
echo "<b>3. Tes Koneksi Database...</b> ";
if ($conn->connect_error) {
    die("<span style='color:red'>GAGAL KONEK! ❌</span><br>Pesan: " . $conn->connect_error);
}
echo "<span style='color:green'>SUKSES ✅</span><br>";

// 5. Cek Data di Tabel
echo "<b>4. Tes Query Data...</b> ";
$sql = "SELECT * FROM bugs LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<span style='color:green'>BERHASIL QUERY ✅</span><br>";
    $jumlah = mysqli_num_rows($result);
    echo "Data ditemukan: $jumlah";
} else {
    echo "<span style='color:red'>ERROR QUERY ❌</span><br>Pesan: " . mysqli_error($conn);
}

echo "<h3>✅ Kesimpulan: Aman bro. Masalahnya bukan di koneksi.</h3>";
?>
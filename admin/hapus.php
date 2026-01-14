<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];

// Panggil fungsi deletePengaduan (Pastikan fungsi ini ada di function.php)
// Kalau lo pake nama fungsi lain (misal deleteBug), sesuaikan ya!
if (deletePengaduan($id) > 0) {
    echo "<script>
            alert('Data berhasil dihapus!');
            document.location.href = 'data-bug.php';
          </script>";
} else {
    echo "<script>
            alert('Data gagal dihapus!');
            document.location.href = 'data-bug.php';
          </script>";
}
?>
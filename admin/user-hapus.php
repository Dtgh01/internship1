<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];

// Cegah Hapus Diri Sendiri (Safety)
if ($id == $_SESSION['login']['user_id']) {
    echo "<script>
            alert('Anda tidak bisa menghapus akun yang sedang login!');
            document.location.href = 'active-acc.php';
          </script>";
    exit;
}

// Proses Hapus
mysqli_query($conn, "DELETE FROM users WHERE user_id = $id");

if (mysqli_affected_rows($conn) > 0) {
    echo "<script>
            alert('User berhasil dihapus!');
            document.location.href = 'active-acc.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal menghapus user!');
            document.location.href = 'active-acc.php';
          </script>";
}
?>
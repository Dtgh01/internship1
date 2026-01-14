<?php 
session_start();
$_SESSION = [];
session_unset();
session_destroy();

// Hapus Cookie juga biar bersih total
setcookie('id', '', time() - 3600);
setcookie('key', '', time() - 3600);

// Arahkan balik ke Landing Page utama (bukan login page)
header("Location: ../index.php");
exit;
?>
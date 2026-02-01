<?php 
session_start();
$_SESSION = [];
session_unset();
session_destroy();

// Hapus Cookie
setcookie('id', '', time() - 3600);
setcookie('key', '', time() - 3600);

// Arahkan balik ke Landing Page
header("Location: ../index.php");
exit;
?>
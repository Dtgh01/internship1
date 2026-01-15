<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- LOGIKA FILTER TANGGAL ---
$where_clause = "";
$label_periode = "Semua Data";

if (isset($_GET['tgl_awal']) && isset($_GET['tgl_akhir'])) {
    $tgl_awal = $_GET['tgl_awal'];
    $tgl_akhir = $_GET['tgl_akhir'];
    
    // Filter Query
    $where_clause = " WHERE DATE(bugs.created_at) BETWEEN '$tgl_awal' AND '$tgl_akhir' ";
    $label_periode = date('d/m/Y', strtotime($tgl_awal)) . " - " . date('d/m/Y', strtotime($tgl_akhir));
}

// Query Data
$query = "SELECT bugs.*, users.name as pelapor, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN users ON bugs.user_id = users.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          $where_clause
          ORDER BY bugs.created_at DESC";

$bugs = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Cetak Laporan - BugTracker</title>
    <link rel="stylesheet" href="../assets/plugins/bootstrap4/css/bootstrap.min.css">
    <style>
        /* Desain Polos & Bersih */
        body { font-family: sans-serif; font-size: 12px; }
        h3 { font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        table, th, td { border: 1px solid #333; padding: 8px; }
        th { background-color: #eee; text-align: center; font-weight: bold; }
        td { vertical-align: top; }
        
        /* Hilangkan elemen header/footer browser saat print */
        @media print {
            @page { margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center mb-4">
        <h3>LAPORAN DATA BUGTRACKER</h3>
        <p class="mb-0">Periode: <strong><?= $label_periode; ?></strong></p>
    </div>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>Pelapor</th>
                <th>Judul Bug</th>
                <th>Kategori</th>
                <th>Prioritas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($bugs)) : ?>
                <tr><td colspan="7" class="text-center font-italic">Tidak ada data pada periode ini.</td></tr>
            <?php else : ?>
                <?php $i = 1; foreach ($bugs as $row) : ?>
                <tr>
                    <td class="text-center"><?= $i++; ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    <td><?= $row['pelapor']; ?></td>
                    <td><?= $row['title']; ?></td>
                    <td><?= $row['category_name']; ?></td>
                    <td class="text-center"><?= $row['priority_name']; ?></td>
                    <td class="text-center font-weight-bold">
                        <?= strtoupper($row['status']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="mt-3 text-right text-muted small">
        <p>Dicetak pada: <?= date('d F Y H:i'); ?></p>
    </div>

</body>
</html>
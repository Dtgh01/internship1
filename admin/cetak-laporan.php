<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil Semua Data Laporan (Urut Tanggal Terbaru)
$query = "SELECT bugs.*, users.name as pelapor, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN users ON bugs.user_id = users.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          ORDER BY bugs.created_at DESC";

$bugs = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Bug - TrimHub ID</title>
    <style>
        /* CSS KHUSUS CETAK */
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; color: #000; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18pt; text-transform: uppercase; }
        .header p { margin: 0; font-size: 10pt; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; text-align: center; }
        
        .status-badge { font-weight: bold; font-size: 0.9em; }

        /* Area Tanda Tangan */
        .ttd { float: right; margin-top: 50px; text-align: center; width: 250px; }
        
        /* Sembunyikan tombol cetak pas diprint */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div style="text-align: center; margin-bottom: 20px;">
        <h3>LAPORAN REKAPITULASI BUG & ERROR</h3>
        <p>Per Tanggal: <?= date('d F Y'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th width="15%">Pelapor</th>
                <th>Judul Masalah</th>
                <th>Kategori</th>
                <th>Prioritas</th>
                <th>Status Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($bugs)) : ?>
                <tr><td colspan="7" style="text-align: center;">Tidak ada data laporan.</td></tr>
            <?php else : ?>
                <?php $i = 1; foreach ($bugs as $row) : ?>
                <tr>
                    <td style="text-align: center;"><?= $i++; ?></td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    <td><?= $row['pelapor']; ?></td>
                    <td><?= $row['title']; ?></td>
                    <td><?= $row['category_name']; ?></td>
                    <td style="text-align: center;"><?= $row['priority_name']; ?></td>
                    <td style="text-align: center;">
                        <span class="status-badge"><?= strtoupper($row['status']); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="ttd">
        <p>__________, <?= date('d F Y'); ?></p>
        <p>Mengetahui,</p>
        <p><b>_______________________</b></p>
        <br><br><br> <p>_______________________</p>
        <p>_______________________</p>
    </div>

</body>
</html>
<?php
session_start();
require '../function.php';

// Cek Admin
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}

// AMBIL FILTER DARI URL
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Helper Nama Bulan
$list_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$label_periode = $list_bulan[$bulan] . " " . $tahun;

// Query Filter Bulanan
$query = "SELECT bugs.*, users.name as pelapor, categories.category_name, priorities.priority_name
          FROM bugs
          JOIN users ON bugs.user_id = users.user_id
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          WHERE MONTH(bugs.created_at) = '$bulan' AND YEAR(bugs.created_at) = '$tahun'
          ORDER BY bugs.created_at ASC";
$data = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan - <?= $label_periode; ?></title>
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    
    <style>
        body { background: #fff; color: #000; font-family: 'Times New Roman', serif; font-size: 12pt; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #f0f0f0 !important; text-align: center; font-weight: bold; }
        .badge { border: 1px solid #000; background: #fff; color: #000; padding: 2px 5px; font-weight: normal; }
        
        @media print {
            .no-print { display: none !important; } /* Hilangkan tombol saat print */
            @page { size: A4; margin: 2cm; }
        }
    </style>
</head>
<body>

    <div class="no-print position-fixed" style="top: 20px; right: 20px; z-index: 1000;">
        <button onclick="window.print()" class="btn btn-primary font-weight-bold shadow-lg">
            <i class="fas fa-print mr-2"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="container-fluid pt-4">
        
        <div class="header position-relative">
            <h3 class="font-weight-bold m-0">BUG TRACKER INDONESIA</h3>
            <p class="m-0"></p>
            <p class="m-0 small"></p>
        </div>

        <div class="text-center mb-4">
            <h4 class="font-weight-bold text-uppercase">LAPORAN REKAPITULASI BUG</h4>
            <p>Periode : <b><?= $label_periode; ?></b></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th>Judul Masalah</th>
                    <th width="15%">Pelapor</th>
                    <th width="10%">Prioritas</th>
                    <th width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 font-italic">Tidak ada data laporan pada periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php $i=1; foreach($data as $row): ?>
                    <tr>
                        <td class="text-center"><?= $i++; ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <b><?= $row['title']; ?></b><br>
                            <small>Kategori: <?= $row['category_name']; ?></small>
                        </td>
                        <td><?= $row['pelapor']; ?></td>
                        <td class="text-center"><?= $row['priority_name']; ?></td>
                        <td class="text-center"><?= $row['status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="row mt-5">
            <div class="col-4 offset-8 text-center">
                <p>Bandung, <?= date('d F Y'); ?></p>
                <p>Mengetahui,<br>IT Manager</p>
                <br><br><br>
                <p class="font-weight-bold border-bottom d-inline-block pb-1" style="border-color: #000 !important; min-width: 150px;">
                    <?= $_SESSION['login']['name']; ?>
                </p>
            </div>
        </div>

    </div>

</body>
</html>
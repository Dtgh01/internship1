<?php
session_start();
require '../function.php';

// Cek Login & Role Developer
if (!isset($_SESSION['login']) || $_SESSION['login']['role'] !== 'developer') {
    header("Location: ../auth/login.php");
    exit;
}

$dev_id = $_SESSION['login']['user_id'];
$dev_name = $_SESSION['login']['name'];

// Ambil Bug yang DI-ASSIGN ke Developer ini
$query = "SELECT bugs.*, categories.category_name, priorities.priority_name, users.name as pelapor
          FROM bugs
          JOIN categories ON bugs.category_id = categories.category_id
          JOIN priorities ON bugs.priority_id = priorities.priority_id
          JOIN users ON bugs.user_id = users.user_id
          WHERE bugs.assigned_to = $dev_id
          ORDER BY FIELD(bugs.status, 'Assigned', 'In Progress', 'Testing', 'Resolved', 'Closed'), bugs.updated_at DESC";

$jobs = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Workspace Developer | BugTracker</title>
    <link rel="stylesheet" href="../assets/plugins/bootstrap4/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* HEADER GRADIENT AGAK GELAP (DEV STYLE) */
        .header-dev {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding-bottom: 4rem;
            margin-bottom: -2rem;
            border-radius: 0 0 20px 20px;
        }
        
        .card-job {
            border: none; border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="header-dev shadow">
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="#">👨‍💻 DevSpace <b>BugTracker</b></a>
            <div class="ml-auto text-white">
                Halo, <b><?= $dev_name; ?></b> | 
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light ml-2 font-weight-bold">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-3">
        <h3>Daftar Tugas Hari Ini</h3>
        <p class="mb-0 opacity-75">Semangat ngoding! Jangan lupa update status kalau udah fix.</p>
    </div>
</div>

<div class="container" style="margin-top: 40px;">
    
    <div class="card card-job">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list-ul"></i> Antrian Pekerjaan</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Masalah & Prioritas</th>
                            <th>Kategori</th>
                            <th>Pelapor</th>
                            <th>Status Saat Ini</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)) : ?>
                            <tr><td colspan="6" class="text-center py-5"><i>Belum ada tugas masuk. Aman aja!</i></td></tr>
                        <?php else : ?>
                            <?php foreach ($jobs as $job) : ?>
                            <tr>
                                <td>#<?= $job['bug_id']; ?></td>
                                <td>
                                    <b><?= $job['title']; ?></b><br>
                                    <?php if($job['priority_name'] == 'Critical') : ?>
                                        <span class="badge badge-danger">Critical 🔥</span>
                                    <?php elseif($job['priority_name'] == 'High') : ?>
                                        <span class="badge badge-warning">High ⚠️</span>
                                    <?php else : ?>
                                        <span class="badge badge-info"><?= $job['priority_name']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $job['category_name']; ?></td>
                                <td><?= $job['pelapor']; ?></td>
                                <td>
                                    <span class="badge p-2 badge-<?php 
                                        echo ($job['status'] == 'Assigned') ? 'info' : 
                                             (($job['status'] == 'In Progress') ? 'primary' : 
                                             (($job['status'] == 'Resolved') ? 'success' : 'secondary')); 
                                    ?>">
                                        <?= $job['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="kerjaan.php?id=<?= $job['bug_id']; ?>" class="btn btn-sm btn-success shadow-sm">
                                        <i class="fas fa-tools"></i> Proses
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  
  <a href="index.php" class="brand-link text-center">
    <img src="../assets/img/logotrimhub.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8; max-height: 33px; float: none;">
    
    <span class="brand-text font-weight-bold d-block mt-1">BugTracker</span>
  </a>

  <div class="sidebar">
    
    <div class="user-panel mt-3 pb-3 mb-3 d-flex justify-content-center">
      <div class="info text-center w-100">
        <a href="#" class="d-block font-weight-bold" style="font-size: 1.1em;">
            <i class="fas fa-user-shield mr-1"></i> <?= $_SESSION['login']['name']; ?>
        </a>
        <small class="text-muted">Administrator</small>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <li class="nav-item">
          <a href="index.php" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-header font-weight-bold text-muted">DATA UTAMA</li>

        <li class="nav-item">
          <a href="data-bug.php" class="nav-link">
            <i class="nav-icon fas fa-bug"></i>
            <p>Data Bug Masuk</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="master-kategori.php" class="nav-link">
            <i class="nav-icon fas fa-tags"></i>
            <p>Master Kategori</p>
          </a>
        </li>

        <li class="nav-header font-weight-bold text-muted">PENGGUNA</li>

        <li class="nav-item">
          <a href="active-acc.php" class="nav-link">
            <i class="nav-icon fas fa-users"></i>
            <p>Manajemen User</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../profil.php" class="nav-link">
            <i class="nav-icon fas fa-user-cog"></i>
            <p>Edit Profil Saya</p>
          </a>
        </li>

        <li class="nav-header"></li>
        
        <li class="nav-item">
          <a href="../auth/logout.php" class="nav-link bg-danger" onclick="return confirm('Yakin ingin keluar?');">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>
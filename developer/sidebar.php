<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <img src="../assets/img/logo1.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">BugTracker</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex border-bottom-0">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['login']['name']); ?>&background=eab308&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block text-white font-weight-bold">Developer</a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          
          <li class="nav-item">
            <a href="index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-header">PEKERJAAN</li>
          
          <li class="nav-item">
            <a href="kerjaan.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'kerjaan.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-tasks"></i>
              <p>Daftar Kerjaan</p>
            </a>
          </li>

          <li class="nav-header">AKUN</li>
          
          <li class="nav-item">
            <a href="../profil.php" class="nav-link">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>Edit Profil</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link bg-danger">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>

        </ul>
      </nav>
    </div>
</aside>
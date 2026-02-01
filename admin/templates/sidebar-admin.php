<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <img src="../assets/img/logotrimhub.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">BugTracker</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex border-bottom-0">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name=Admin&background=3b82f6&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block text-white font-weight-bold">Administrator</a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          
          <li class="nav-item">
            <a href="index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-header">MASTER DATA</li>
          
          <li class="nav-item">
            <a href="data-bug.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'data-bug.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-bug"></i>
              <p>Data Bug / Laporan</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="users.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>Manajemen User</p>
            </a>
          </li>

          <li class="nav-header">AKSES</li>
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
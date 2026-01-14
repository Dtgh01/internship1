<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <span class="brand-text font-weight-light px-3"><b>BugTracker </b> TrimHubID</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="../assets/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image" onerror="this.src='https://via.placeholder.com/150'">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?= $_SESSION['login']['name']; ?></a>
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

          <li class="nav-header">MAIN DATA</li>

          <li class="nav-item">
            <a href="data-bug.php" class="nav-link">
              <i class="nav-icon fas fa-bug"></i>
              <p>
                Data Bug Masuk
                <span class="right badge badge-danger">New</span>
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="active-acc.php" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Manajemen User</p>
            </a>
          </li>

          <li class="nav-header">SYSTEM</li>
          
          <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link">
              <i class="nav-icon fas fa-power-off"></i>
              <p>Logout</p>
            </a>
          </li>

        </ul>
      </nav>
      </div>
    </aside>
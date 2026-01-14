<footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
      Bug Tracking System
    </div>
    <strong>Copyright &copy; <?= date('Y'); ?> <a href="#">BugTracker</a>.</strong>
  </footer>
</div>
<script src="../assets/plugins/jquery/jquery.min.js"></script>

<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="../assets/dist/js/adminlte.min.js"></script>

<script src="../assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
  $(function () {
    
    // A. Inisialisasi DataTables
    // Cari tabel yang punya ID "example1" lalu aktifkan fiturnya
    $("#example1").DataTable({
      "responsive": true, 
      "lengthChange": false, 
      "autoWidth": false,
      "pageLength": 10,
      "language": {
        "search": "Cari Data:",
        "zeroRecords": "Data tidak ditemukan",
        "paginate": {
          "next": "Lanjut",
          "previous": "Mundur"
        }
      }
    });

    // B. Logika Menu Sidebar Aktif Otomatis
    // Biar gak usah manual nambahin class 'active' di sidebar
    var url = window.location;

    // Untuk menu utama
    $('ul.nav-sidebar a').filter(function() {
        return this.href == url;
    }).addClass('active');

    // Untuk menu yang ada di dalam treeview (Sub-menu)
    $('ul.nav-treeview a').filter(function() {
        return this.href == url;
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

  });
</script>

</body>
</html>
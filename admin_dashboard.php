<?php
session_start();
include 'connection.php';
$name = $_SESSION['name'];
$id = $_SESSION['id'];
if (empty($id)) {
    header("Location: index.php");
    exit();
}

// ================= Visitor Stats =================
$total_visitors = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM tbl_visitors"));

// ================= Department List =================
$departments = mysqli_query($conn, "SELECT * FROM tbl_department WHERE status=1 ORDER BY department ASC");

// ================= Filters =================
$where = "WHERE 1=1";
if (!empty($_GET['department'])) $where .= " AND department='" . $_GET['department'] . "'";
if (!empty($_GET['year'])) $where .= " AND year_of_graduation='" . $_GET['year'] . "'";
if (!empty($_GET['gender'])) $where .= " AND gender='" . $_GET['gender'] . "'";

// Visitor list with filters
$visitors = mysqli_query($conn, "SELECT * FROM tbl_visitors $where ORDER BY in_time DESC");
?>

<?php include('include/header.php'); ?>

<div id="wrapper">
  <?php include('include/side-bar.php'); ?>

  <div id="content-wrapper">
    <div class="container-fluid">

      <!-- Breadcrumb -->
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>

      <!-- Visitor Stats Cards -->
      <div class="row justify-content-center">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner text-center">
              <h3><?php echo $total_visitors[0]; ?></h3>
              <p>Total Visitors</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-3">
        <div class="card-header"><i class="fas fa-filter"></i> Search Visitors</div>
        <div class="card-body">
          <form method="GET" class="form-inline row">
            <!-- Department Dropdown -->
            <div class="col-md-3 mb-2">
              <select id="department" name="department" class="form-control w-100">
                <option value="">All Departments</option>
                <?php while($dept = mysqli_fetch_assoc($departments)) { ?>
                  <option value="<?php echo $dept['department']; ?>" 
                    <?= (isset($_GET['department']) && $_GET['department']==$dept['department'])?'selected':''; ?>>
                    <?php echo $dept['department']; ?>
                  </option>
                <?php } ?>
                <option value="new">➕ Add New Department</option>
              </select>
            </div>

            <!-- Year Dropdown -->
            <div class="col-md-3 mb-2">
              <select name="year" class="form-control w-100">
                <option value="">All Years</option>
                <?php for ($y=2007; $y<=date("Y"); $y++) { ?>
                  <option value="<?php echo $y; ?>" <?= (isset($_GET['year']) && $_GET['year']==$y)?'selected':''; ?>>
                    <?php echo $y; ?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <!-- Gender Dropdown -->
            <div class="col-md-3 mb-2">
              <select name="gender" class="form-control w-100">
                <option value="">All Genders</option>
                <option value="Male" <?= (isset($_GET['gender']) && $_GET['gender']=='Male')?'selected':''; ?>>Male</option>
                <option value="Female" <?= (isset($_GET['gender']) && $_GET['gender']=='Female')?'selected':''; ?>>Female</option>
              </select>
            </div>

            <div class="col-md-3 mb-2">
              <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Visitors Table -->
      <div class="card-header d-flex justify-content-between">
        <span><i class="fas fa-table"></i> Visitors List</span>
        <div>
          <a href="export_visitors.php?<?php echo http_build_query($_GET); ?>" 
            class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
          </a>
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" width="100%" cellspacing="0">
            <thead class="thead-dark">
              <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Year</th>
                <th>Gender</th>
                <th>In Time</th>
                <th>Out Time</th>
              </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($visitors)) { ?>
              <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['department']; ?></td>
                <td><?php echo $row['year_of_graduation']; ?></td>
                <td><?php echo $row['gender']; ?></td>
                <td><?php echo $row['in_time']; ?></td>
                <td><?php echo $row['out_time']; ?></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /.container-fluid -->
  </div><!-- /#content-wrapper -->
</div><!-- /#wrapper -->

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addDeptForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addDeptModalLabel">Add New Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="dept_name" class="form-control" placeholder="Department Name" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Add Department</button>
        </div>
      </form>
    </div>
  </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
  <i class="fas fa-angle-up"></i>
</a>

<?php include('include/footer.php'); ?>

<script>
document.getElementById('department').addEventListener('change', function() {
    if(this.value === 'new') {
        var addDeptModal = new bootstrap.Modal(document.getElementById('addDeptModal'));
        addDeptModal.show();
        this.value = ''; // Reset selection
    }
});

// AJAX for adding new department
document.getElementById('addDeptForm').addEventListener('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);
    fetch('add_department_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        data = data.trim();
        if(data === 'success'){
            alert('Department added successfully! Refreshing page...');
            location.reload();
        } else if(data === 'exists'){
            alert('Department already exists!');
        } else {
            alert('Error adding department!');
        }
    });
});
</script>

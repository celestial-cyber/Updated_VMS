<?php
session_start();
include ('connection.php');
include 'include/guard_member.php';
$name = $_SESSION['name'];
$id = $_SESSION['id'];
if(empty($id))
{
    header("Location: index.php");
    exit();
}
?>
<?php include('include/header.php'); ?>

<!-- Content -->
<div class="container-fluid">
  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <div class="title-row">
      <span class="chip"><i class="fa-solid fa-users-gear text-primary"></i> Visitors</span>
      <h2>👥 Manage Visitors</h2>
      <span class="badge">Live Data</span>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" onclick="location.href='member_new-visitor.php'"><i class="fa-solid fa-plus me-2"></i>Add Visitor</button>
      <button class="btn btn-outline-primary" onclick="location.reload()"><i class="fa-solid fa-arrow-rotate-right me-2"></i>Refresh</button>
    </div>
  </div>

 <!-- Search Form -->
<div class="card-lite mb-3">
  <div class="card-head">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-filter text-primary"></i>
      <span class="fw-semibold">Filter Visitors</span>
    </div>
  </div>
  <div class="card-body">
    <form method="post" class="row g-3 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label">Visitor Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Department</label>
        <select class="form-control" id="department" name="department">
          <option value="">All Departments</option>
          <?php
          $fetch_department = mysqli_query($conn, "SELECT * FROM tbl_department");
          while($row = mysqli_fetch_array($fetch_department)){
          ?>
          <option value="<?php echo $row['department']; ?>"><?php echo $row['department']; ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label">Roll Number</label>
        <input type="text" class="form-control" id="roll_number" name="roll_number" placeholder="Enter roll number">
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label">Year of Graduation</label>
        <input type="number" class="form-control" id="year_of_graduation" name="year_of_graduation" placeholder="e.g. 2020" min="1950" max="2099">
      </div>

      <div class="col-12 col-md-2 d-grid">
        <button type="submit" name="srh-btn" class="btn btn-primary w-100">
          <i class="fa-solid fa-search me-2"></i>Search
        </button>
      </div>
    </form>
  </div>
</div>



  <!-- Visitors Table -->
  <div class="card-lite">
    <div class="card-head">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-users text-primary"></i>
        <span class="fw-semibold">Visitors List</span>
      </div>
      <span class="text-muted"><?php echo isset($search_query) ? 'Filtered Results' : 'All Visitors'; ?></span>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>S.No.</th>
              <th>Name</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Department</th>
              <th>Roll No</th>
              <th>Year</th>
              <th>Status</th>
              <th>Actions</th>
              <th>In/Out</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if(isset($_REQUEST['srh-btn']))
            {
                $visitor_name = trim($_POST['name']);
                $dept = $_POST['department'];
                $roll_number = trim($_POST['roll_number']);
                $year_of_graduation = trim($_POST['year_of_graduation']);

                $conditions = [];

                if(!empty($visitor_name)) {
                    $conditions[] = "name LIKE '%" . mysqli_real_escape_string($conn, $visitor_name) . "%'";
                }

                if(!empty($dept)) {
                    $conditions[] = "department='" . mysqli_real_escape_string($conn, $dept) . "'";
                }

                if(!empty($roll_number)) {
                $conditions[] = "roll_number LIKE '%" . mysqli_real_escape_string($conn, $roll_number) . "%'";
                }

              if(!empty($year_of_graduation)) {
              $conditions[] = "year_of_graduation='" . mysqli_real_escape_string($conn, $year_of_graduation) . "'";
              }


                $where = '';
                if(count($conditions) > 0){
                    $where = 'WHERE ' . implode(' OR ', $conditions); // OR condition
                }

                $search_query = mysqli_query($conn, "SELECT * FROM tbl_visitors $where ORDER BY created_at DESC");
                $sn = 1;
                while($row = mysqli_fetch_array($search_query))
                { ?>
                    <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><?php echo htmlspecialchars($row['roll_number'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['year_of_graduation'] ?? '-'); ?></td>
                        <td>
                            <span class="badge <?php echo $row['status']==1 ? 'text-bg-success-subtle text-success border border-success' : 'text-bg-danger-subtle text-danger border border-danger'; ?>">
                                <?php echo $row['status']==1 ? 'In' : 'Out'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="member_edit_visitors.php?id=<?php echo $row['id'];?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-pencil me-1"></i><?php echo $row['status']==1 ? 'Edit' : 'View'; ?>
                                </a>
                                <a href="member_manage_visitors.php?ids=<?php echo $row['id'];?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">
                                    <i class="fa-solid fa-trash me-1"></i>Delete
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (empty($row['in_time'])) { ?>
                                    <a class="btn btn-outline-primary" href="visitor_status.php?action=checkin&id=<?php echo (int)$row['id']; ?>&from=member_manage_visitors.php"><i class="fa-solid fa-door-open"></i></a>
                                <?php } elseif (empty($row['out_time'])) { ?>
                                    <a class="btn btn-outline-success" href="visitor_status.php?action=checkout&id=<?php echo (int)$row['id']; ?>&from=member_manage_visitors.php"><i class="fa-solid fa-door-closed"></i></a>
                                <?php } else { ?>
                                    <button class="btn btn-outline-secondary" disabled><i class="fa-regular fa-circle-check"></i></button>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php $sn++; }
            }
            else
            {
                if(isset($_GET['ids'])){
                    $id = $_GET['ids'];
                    $delete_query = mysqli_query($conn, "DELETE FROM tbl_visitors WHERE id='$id'");
                }
                $select_query = mysqli_query($conn, "SELECT * FROM tbl_visitors ORDER BY created_at DESC");
                $sn = 1;
                while($row = mysqli_fetch_array($select_query))
                { ?>
                    <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><?php echo htmlspecialchars($row['roll_number'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['year_of_graduation'] ?? '-'); ?></td>
                        <td>
                            <span class="badge <?php echo $row['status']==1 ? 'text-bg-success-subtle text-success border border-success' : 'text-bg-danger-subtle text-danger border border-danger'; ?>">
                                <?php echo $row['status']==1 ? 'In' : 'Out'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="member_edit_visitors.php?id=<?php echo $row['id'];?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-pencil me-1"></i><?php echo $row['status']==1 ? 'Edit' : 'View'; ?>
                                </a>
                                <a href="member_manage_visitors.php?ids=<?php echo $row['id'];?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">
                                    <i class="fa-solid fa-trash me-1"></i>Delete
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (empty($row['in_time'])) { ?>
                                    <a class="btn btn-outline-primary" href="visitor_status.php?action=checkin&id=<?php echo (int)$row['id']; ?>&from=member_manage_visitors.php"><i class="fa-solid fa-door-open"></i></a>
                                <?php } elseif (empty($row['out_time'])) { ?>
                                    <a class="btn btn-outline-success" href="visitor_status.php?action=checkout&id=<?php echo (int)$row['id']; ?>&from=member_manage_visitors.php"><i class="fa-solid fa-door-closed"></i></a>
                                <?php } else { ?>
                                    <button class="btn btn-outline-secondary" disabled><i class="fa-regular fa-circle-check"></i></button>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php $sn++; }
            }
            ?>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <span class="muted">Showing <?php echo isset($search_query) ? 'filtered' : 'all'; ?> visitors</span>
        <div class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-success" href="export_visitors_excel.php?department=<?php echo isset($dept)?urlencode($dept):''; ?>">
            <i class="fa-regular fa-file-excel me-1"></i>Excel
          </a>
          <a class="btn btn-sm btn-outline-secondary" href="export_visitors.php">
            <i class="fa-solid fa-file-csv me-1"></i>CSV
          </a>
          <a href="export_visitors_pdf.php?event=Nostalgia" target="_blank" class="btn btn-sm btn-outline-danger">
            <i class="fa-regular fa-file-pdf me-1"></i>Export PDF
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include('include/footer.php'); ?>
<script>
function confirmDelete(){
    return confirm('Are you sure want to delete this Visitor?');
}
</script>

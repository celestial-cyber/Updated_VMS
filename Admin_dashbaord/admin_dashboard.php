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

// Fetch events for sidebar
$events_res = mysqli_query($conn, "SELECT event_id, event_name FROM tbl_events ORDER BY event_date DESC");
$has_events = ($events_res && mysqli_num_rows($events_res) > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.0.js"></script>

  <!-- Bootstrap (optional, for modal) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    * {margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body {background:#f2f2f2;}
    
    /* ===== Sidebar ===== */
    nav{
      background:#343a40;
      width:250px;
      min-height:100vh;
      position:fixed;
      top:0; left:0;
      overflow-y:auto;
    }
    nav .logo{
      color:white;
      font-size:27px;
      font-weight:600;
      line-height:70px;
      text-align:center;
    }
    nav ul {list-style:none;padding:0;}
    nav ul li {position:relative;}
    nav ul li a{
      display:block;
      color:white;
      text-decoration:none;
      padding:15px 20px;
      font-size:16px;
    }
    nav ul li a:hover{background-color:#495057;border-radius:5px;}
    nav ul ul{
      display:none;
      background-color:#fff;
    }
    nav ul ul li a{color:#000;padding-left:40px;}
    nav ul ul li a:hover{background-color:#f1f1f1;}
    .show,input[type=checkbox]{display:none;}
    input[type=checkbox]:checked + ul{display:block;}

    /* Toggle icon for mobile */
    .icon{display:none;position:absolute;top:10px;right:20px;font-size:25px;color:white;cursor:pointer;}

    /* ===== Main Content ===== */
    .dashboard-wrapper {display:flex; min-height:100vh;}
    .main-content {flex:1; margin-left:250px; padding:20px;}

    /* Table styling */
    table { width:100%; border-collapse: collapse; margin-top:20px; }
    table, th, td { border:1px solid #ccc; }
    th, td { padding:8px; text-align:left; }

    @media(max-width:768px){
      nav{width:100%; position:relative;}
      .icon{display:block;}
      .main-content{margin-left:0;}
    }
  </style>
</head>
<body>

<div class="dashboard-wrapper">
  <!-- Sidebar -->
  <nav>
    <div class="logo">VMS</div>
    <label for="sidebar-toggle" class="icon"><span class="fa fa-bars"></span></label>
    <input type="checkbox" id="sidebar-toggle">
    <ul>
      <li><a href="admin_dashboard.php">🏠 Admin Dashboard</a></li>
      <li><a href="new-visitor.php">📝 New Visitor</a></li>
      <li><a href="manage-visitors.php">📋 Manage Visitors</a></li>

      <li>
        <label for="events-toggle" class="show">📅 Manage Events +</label>
        <a href="javascript:void(0);">📅 Manage Events</a>
        <input type="checkbox" id="events-toggle">
        <ul>
          <li><a href="add-event.php">➕ Add New Event</a></li>
          <?php if ($has_events) {
            while ($ev = mysqli_fetch_assoc($events_res)) {
              $id = (int)$ev['event_id'];
              $name = htmlspecialchars($ev['event_name']);
              echo "<li><a href='view_event.php?event_id={$id}'>📌 {$name}</a></li>";
            }
          } ?>
        </ul>
      </li>
    </ul>
  </nav>

  <!-- Main Content -->
  <div class="main-content">
    <h2>Welcome, <?php echo $name; ?></h2>

    <!-- Visitor Stats -->
    <div class="stats">
      <div class="card" style="padding:15px;margin:10px 0;">
        <h3>Total Visitors</h3>
        <p><?php echo $total_visitors[0]; ?></p>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters">
      <form method="GET" style="margin:15px 0;">
        <select name="department">
          <option value="">All Departments</option>
          <?php while($dept = mysqli_fetch_assoc($departments)) { ?>
            <option value="<?php echo $dept['department']; ?>" <?= (isset($_GET['department']) && $_GET['department']==$dept['department'])?'selected':''; ?>>
              <?php echo $dept['department']; ?>
            </option>
          <?php } ?>
        </select>
        <select name="year">
          <option value="">All Years</option>
          <?php for ($y=2007; $y<=date("Y"); $y++) { ?>
            <option value="<?php echo $y; ?>" <?= (isset($_GET['year']) && $_GET['year']==$y)?'selected':''; ?>>
              <?php echo $y; ?>
            </option>
          <?php } ?>
        </select>
        <select name="gender">
          <option value="">All Genders</option>
          <option value="Male" <?= (isset($_GET['gender']) && $_GET['gender']=='Male')?'selected':''; ?>>Male</option>
          <option value="Female" <?= (isset($_GET['gender']) && $_GET['gender']=='Female')?'selected':''; ?>>Female</option>
        </select>
        <button type="submit">Search</button>
      </form>
    </div>

    <!-- Visitor Table -->
    <table>
      <thead>
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

  </div> <!-- End Main Content -->
</div> <!-- End dashboard-wrapper -->

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addDeptForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addDeptModalLabel">Add New Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

<script>
document.getElementById('department').addEventListener('change', function() {
    if(this.value === 'new') {
        var addDeptModal = new bootstrap.Modal(document.getElementById('addDeptModal'));
        addDeptModal.show();
        this.value = '';
    }
});

// AJAX for adding new department
document.getElementById('addDeptForm').addEventListener('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);
    fetch('add_department_ajax.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        data = data.trim();
        if(data==='success'){ alert('Department added! Refreshing...'); location.reload(); }
        else if(data==='exists'){ alert('Department already exists!'); }
        else{ alert('Error adding department!'); }
    });
});
</script>

</body>
</html>

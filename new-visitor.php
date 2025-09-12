<?php
session_start();
include('connection.php');
$name = $_SESSION['name'];
$id = $_SESSION['id'];
if(empty($id)) {
    header("Location: index.php"); 
    exit();
}

// Initialize popup message
$popup_message = '';
$popup_type = '';

// Handle Add Visitor
if(isset($_POST['sbt-vstr'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $year = mysqli_real_escape_string($conn, $_POST['year_of_graduation']);
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);

    $insert_visitor = mysqli_query($conn, "
        INSERT INTO tbl_visitors
        SET event_id='$event_id', name='$fullname', email='$email', mobile='$mobile',
            address='$address', department='$department',
            gender='$gender', year_of_graduation='$year', in_time=NOW()
    ");

    if($insert_visitor) {
        $popup_message = "Visitor added successfully!";
        $popup_type = "success";
    } else {
        $popup_message = "Error adding visitor!";
        $popup_type = "danger";
    }
}

// Handle Edit Visitor
if(isset($_POST['update-vstr'])){
    $visitor_id = $_POST['visitor_id'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $year = mysqli_real_escape_string($conn, $_POST['year_of_graduation']);

    $update_visitor = mysqli_query($conn, "
        UPDATE tbl_visitors 
        SET name='$fullname', email='$email', mobile='$mobile',
            address='$address', department='$department', 
            gender='$gender', year_of_graduation='$year'
        WHERE id='$visitor_id'
    ");

    if($update_visitor){
        $popup_message = "Visitor updated successfully!";
        $popup_type = "success";
    } else {
        $popup_message = "Error updating visitor!";
        $popup_type = "danger";
    }
}
?>

<!-- Content -->
<div class="container-fluid">
  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <div class="title-row">
      <span class="chip"><i class="fa-solid fa-user-plus text-primary"></i> Visitor</span>
      <h2>👤 Add / Edit Visitor</h2>
      <span class="badge">Live Form</span>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-primary" onclick="location.href='manage-visitors.php'"><i class="fa-solid fa-users me-2"></i>View All</button>
      <button class="btn btn-outline-secondary" onclick="location.reload()"><i class="fa-solid fa-arrow-rotate-right me-2"></i>Reset</button>
    </div>
  </div>

  <!-- Visitor Form -->
  <div class="card-lite">
    <div class="card-head">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-user-circle text-primary"></i>
        <span class="fw-semibold">Visitor Details</span>
      </div>
    </div>
    <div class="card-body">
      <form method="post" class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Name <span class="text-danger">*</span></label>
          <input type="text" name="fullname" class="form-control" placeholder="Enter Full Name" required>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="Enter Email Address" required>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Mobile <span class="text-danger">*</span></label>
          <input type="text" name="mobile" class="form-control" placeholder="Enter Mobile Number" required>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Address <span class="text-danger">*</span></label>
          <textarea name="address" class="form-control" placeholder="Enter Complete Address" rows="2" required></textarea>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Department <span class="text-danger">*</span></label>
          <select name="department" class="form-control" required>
            <option value="">Select Department</option>
            <?php
            $select_department = mysqli_query($conn,"SELECT * FROM tbl_department WHERE status=1 ORDER BY department ASC");
            while($dept = mysqli_fetch_assoc($select_department)){
                echo "<option value='".$dept['department']."'>".$dept['department']."</option>";
            }
            ?>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Event <span class="text-danger">*</span></label>
          <select name="event_id" class="form-control" required>
            <option value="">Select Event</option>
            <?php
            $select_events = mysqli_query($conn,"SELECT * FROM tbl_events ORDER BY event_name ASC");
            while($event = mysqli_fetch_assoc($select_events)){
                echo "<option value='".$event['event_id']."'>".$event['event_name']."</option>";
            }
            ?>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Gender <span class="text-danger">*</span></label>
          <select name="gender" class="form-control" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Year of Graduation <span class="text-danger">*</span></label>
          <select name="year_of_graduation" class="form-control" required>
            <option value="">Select Year</option>
            <?php for($y = 2007; $y <= date("Y"); $y++){
                echo "<option value='$y'>$y</option>";
            } ?>
          </select>
        </div>

        <div class="col-12">
          <div class="d-flex gap-2">
            <button type="submit" name="sbt-vstr" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add Visitor</button>
            <button type="submit" name="update-vstr" class="btn btn-success"><i class="fa-solid fa-pen-to-square me-2"></i>Update Visitor</button>
            <button type="reset" class="btn btn-outline-secondary"><i class="fa-solid fa-eraser me-2"></i>Clear Form</button>
          </div>
        </div>

        <input type="hidden" name="visitor_id" value="">
      </form>
    </div>
  </div>
</div>

<!-- Popup Modal -->
<div class="modal fade" id="visitorPopup" tabindex="-1" aria-labelledby="visitorPopupLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-<?php echo $popup_type ?: 'primary'; ?> text-white">
        <h5 class="modal-title" id="visitorPopupLabel"><?php echo ucfirst($popup_type ?: 'Info'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php echo $popup_message; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-<?php echo $popup_type ?: 'primary'; ?>" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<?php include('include/footer.php'); ?>

<?php if($popup_message): ?>
<script>
  var popupModal = new bootstrap.Modal(document.getElementById('visitorPopup'));
  popupModal.show();
</script>
<?php endif; ?>

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

// Handle Add Participation
if(isset($_POST['sbt-part'])) {
    $activity_name = mysqli_real_escape_string($conn, $_POST['activity_name']);
    $participant_count = intval($_POST['participant_count']);
    
    if (empty($activity_name) || $participant_count < 0) {
        $popup_message = "Invalid input data";
        $popup_type = "danger";
    } else {
        // Check if activity already exists
        $check_sql = "SELECT * FROM tbl_event_participation WHERE activity_name = '$activity_name'";
        $result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($result) > 0) {
            // Update existing activity
            $update_sql = "UPDATE tbl_event_participation SET participant_count = participant_count + $participant_count WHERE activity_name = '$activity_name'";
            if (mysqli_query($conn, $update_sql)) {
                $popup_message = "Participation updated successfully!";
                $popup_type = "success";
            } else {
                $popup_message = "Error updating participation: " . mysqli_error($conn);
                $popup_type = "danger";
            }
        } else {
            // Insert new activity
            $insert_sql = "INSERT INTO tbl_event_participation (activity_name, participant_count)
                          VALUES ('$activity_name', $participant_count)";
            if (mysqli_query($conn, $insert_sql)) {
                $popup_message = "Participation recorded successfully!";
                $popup_type = "success";
            } else {
                $popup_message = "Error recording participation: " . mysqli_error($conn);
                $popup_type = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>VMS - Add Participation</title>
  <?php include('include/header.php'); ?>
</head>
<body id="page-top">
  <?php
  $breadcrumbs = [
      ['url' => 'admin_dashboard.php', 'text' => 'Dashboard'],
      ['url' => 'manage-participation.php', 'text' => 'Participation'],
      ['text' => 'Add Participation']
  ];
  include('include/top-bar.php');
  ?>
  <div id="wrapper">
    <?php include('include/side-bar.php'); ?>
    
    <!-- Content -->
    <div class="container-fluid">
      <!-- Header -->
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div class="title-row">
          <span class="chip"><i class="fa-solid fa-calendar-plus text-primary"></i> Participation</span>
          <h2>📊 Add Event Participation</h2>
          <span class="badge">Live Form</span>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-primary" onclick="location.href='manage-participation.php'"><i class="fa-solid fa-chart-line me-2"></i>View All</button>
          <button class="btn btn-outline-secondary" onclick="location.reload()"><i class="fa-solid fa-arrow-rotate-right me-2"></i>Reset</button>
        </div>
      </div>

      <!-- Participation Form -->
      <div class="card-lite">
        <div class="card-head">
          <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-users text-primary"></i>
            <span class="fw-semibold">Participation Details</span>
          </div>
        </div>
        <div class="card-body">
          <form method="post" class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Activity Name <span class="text-danger">*</span></label>
              <input type="text" name="activity_name" class="form-control" placeholder="Enter Activity Name" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Participant Count <span class="text-danger">*</span></label>
              <input type="number" name="participant_count" class="form-control" placeholder="Enter Participant Count" min="0" required>
            </div>

            <div class="col-12">
              <div class="d-flex gap-2">
                <button type="submit" name="sbt-part" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add Participation</button>
                <button type="reset" class="btn btn-outline-secondary"><i class="fa-solid fa-eraser me-2"></i>Clear Form</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Popup Modal -->
  <div class="modal fade" id="participationPopup" tabindex="-1" aria-labelledby="participationPopupLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-<?php echo $popup_type ?: 'primary'; ?> text-white">
          <h5 class="modal-title" id="participationPopupLabel"><?php echo ucfirst($popup_type ?: 'Info'); ?></h5>
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
    var popupModal = new bootstrap.Modal(document.getElementById('participationPopup'));
    popupModal.show();
  </script>
  <?php endif; ?>
</body>
</html>
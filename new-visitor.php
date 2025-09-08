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
    $emailid = mysqli_real_escape_string($conn, $_POST['emailid']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $year = mysqli_real_escape_string($conn, $_POST['year_of_graduation']);

    $insert_visitor = mysqli_query($conn, "
        INSERT INTO tbl_visitors 
        SET name='$fullname', emailid='$emailid', mobile='$mobile', 
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
    $emailid = mysqli_real_escape_string($conn, $_POST['emailid']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $year = mysqli_real_escape_string($conn, $_POST['year_of_graduation']);

    $update_visitor = mysqli_query($conn, "
        UPDATE tbl_visitors 
        SET name='$fullname', emailid='$emailid', mobile='$mobile', 
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

<?php include('include/header.php'); ?>
<div id="wrapper">
<?php include('include/side-bar.php'); ?>

<div id="content-wrapper">
    <div class="container-fluid">

        <!-- Breadcrumbs -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Add / Edit Visitor</a>
            </li>
        </ol>

        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-info-circle"></i> Submit Details</div>
            <form method="post" class="form-valide">
                <div class="card-body">

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Name <span class="text-danger">*</span></label>
                        <div class="col-lg-6">
                            <input type="text" name="fullname" class="form-control" placeholder="Enter Name" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Email <span class="text-danger">*</span></label>
                        <div class="col-lg-6">
                            <input type="email" name="emailid" class="form-control" placeholder="Enter Email" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Mobile <span class="text-danger">*</span></label>
                        <div class="col-lg-6">
                            <input type="text" name="mobile" class="form-control" placeholder="Enter Mobile" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Address <span class="text-danger">*</span></label>
                        <div class="col-lg-6">
                            <textarea name="address" class="form-control" placeholder="Enter Address" required></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Department <span class="text-danger">*</span></label>
                        <div class="col-lg-6">
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
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Gender <span class="text-danger">*</span></label>
                        <div class="col-lg-6">
                            <select name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Year of Graduation <span class="text-danger">*</span></label>
                        <div class="col-lg-6">
                            <select name="year_of_graduation" class="form-control" required>
                                <option value="">Select Year</option>
                                <?php for($y = 2007; $y <= date("Y"); $y++){
                                    echo "<option value='$y'>$y</option>";
                                } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-8 ml-auto">
                            <button type="submit" name="sbt-vstr" class="btn btn-primary">Add Visitor</button>
                            <button type="submit" name="update-vstr" class="btn btn-success">Update Visitor</button>
                        </div>
                    </div>

                    <input type="hidden" name="visitor_id" value="">
                </div>
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

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<?php include('include/footer.php'); ?>

<?php if($popup_message): ?>
<script>
  var popupModal = new bootstrap.Modal(document.getElementById('visitorPopup'));
  popupModal.show();
</script>
<?php endif; ?>

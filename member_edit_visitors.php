<?php
session_start();
include('connection.php');

// -----------------------------
// Session & role checks
// -----------------------------
$name = $_SESSION['name'];
$id = $_SESSION['id'];  // logged-in member ID
$role = $_SESSION['role'] ?? 'member';

if(empty($id)) {
    header("Location: index.php"); 
    exit();
}

// Only members can access this page
if($role != 'member') {
    header("Location: admin_dashboard.php");
    exit();
}

// -----------------------------
// Get visitor ID
// -----------------------------
$visitor_id = $_GET['id'] ?? 0;
$fetch_query = mysqli_query($conn, "SELECT * FROM tbl_visitors WHERE id='$visitor_id'");
$row = mysqli_fetch_assoc($fetch_query);

// -----------------------------
// Popup variables
// -----------------------------
$popup_message = '';
$popup_type = '';

// -----------------------------
// Handle Save / Update Visitor
// -----------------------------
if(isset($_POST['sv-vstr'])) {

    $fullname   = isset($_POST['fullname']) ? mysqli_real_escape_string($conn, $_POST['fullname']) : '';
    $email      = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $mobile     = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
    $address    = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    $department = isset($_POST['department']) ? mysqli_real_escape_string($conn, $_POST['department']) : '';
    $status     = isset($_POST['status']) ? $_POST['status'] : 1;

    $update_visitor = mysqli_query($conn, "
        UPDATE tbl_visitors 
        SET name='$fullname', email='$email', phone='$mobile',
            address='$address', department='$department', status='$status' 
        WHERE id='$visitor_id'
    ");

    if($update_visitor) {
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
<?php include('include/member_side-bar.php'); ?>

<div id="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumbs -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Edit Visitor</a></li>
        </ol>

        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-info-circle"></i> Edit Details</div>
            <form method="post" class="form-valide">
                <div class="card-body">
                    <!-- Name -->
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Name</label>
                        <div class="col-lg-6">
                            <input type="text" name="fullname" class="form-control" value="<?php echo $row['name']; ?>" readonly>
                        </div>
                    </div>
                    <!-- Email -->
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Email</label>
                        <div class="col-lg-6">
                            <input type="email" name="email" class="form-control" value="<?php echo $row['email']; ?>" readonly>
                        </div>
                    </div>
                    <!-- Mobile -->
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Mobile</label>
                        <div class="col-lg-6">
                            <input type="text" name="phone" class="form-control" value="<?php echo $row['phone']; ?>" readonly>
                        </div>
                    </div>
                    <!-- Address -->
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Address</label>
                        <div class="col-lg-6">
                            <textarea name="address" class="form-control" readonly><?php echo $row['address']; ?></textarea>
                        </div>
                    </div>
                    <!-- Department -->
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Department</label>
                        <div class="col-lg-6">
                            <input type="text" name="department" class="form-control" value="<?php echo $row['department']; ?>" readonly>
                        </div>
                    </div>
                    <!-- In Time -->
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">In Time</label>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" value="<?php echo $row['in_time']; ?>" readonly>
                        </div>
                    </div>
                    <!-- Status -->
                    <div class="form-group row">
                        <?php if($row['status']==1){ ?>
                            <label class="col-lg-4 col-form-label">Status</label>
                            <div class="col-lg-6">
                                <select name="status" class="form-control" required>
                                    <option value="1" selected>In</option>
                                    <option value="0">Out</option>
                                </select>
                            </div>
                        <?php } else { ?>
                            <label class="col-lg-4 col-form-label">Out Time</label>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" value="<?php echo $row['out_time']; ?>" readonly>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if($row['status']==1){ ?>
                    <div class="form-group row">
                        <div class="col-lg-8 ml-auto">
                            <button type="submit" name="sv-vstr" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Popup -->
<?php if($popup_message != '') { ?>
<div class="modal fade" id="visitorPopup" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-<?php echo $popup_type; ?>">
      <div class="modal-body text-<?php echo $popup_type; ?>">
        <?php echo $popup_message; ?>
      </div>
      <div class="modal-footer">
        <a href="member_manage_visitors.php" class="btn btn-<?php echo $popup_type; ?>">OK</a>
      </div>
    </div>
  </div>
</div>
<script>
    var popup = new bootstrap.Modal(document.getElementById('visitorPopup'));
    popup.show();
</script>
<?php } ?>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>
<?php include('include/footer.php'); ?>

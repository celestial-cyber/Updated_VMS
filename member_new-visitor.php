<?php
session_start();
include('connection.php');
$name = $_SESSION['name'];
$id = $_SESSION['id'];
if (empty($id)) {
    header("Location: index.php");
    exit();
}

// Initialize popup message
$popup_message = '';
$popup_type = '';
$redirect_page = 'admin_dashboard.php'; // default

if (isset($_POST['sbt-vstr'])) {
    // Safely get POST variables
    $fullname    = isset($_POST['full_name']) ? mysqli_real_escape_string($conn, $_POST['full_name']) : '';
    $email       = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $phone       = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
    $gender      = isset($_POST['gender']) ? mysqli_real_escape_string($conn, $_POST['gender']) : '';
    $department  = isset($_POST['department']) ? mysqli_real_escape_string($conn, $_POST['department']) : '';
    $event_id    = isset($_POST['event_id']) ? mysqli_real_escape_string($conn, $_POST['event_id']) : '';
    $year        = isset($_POST['year_of_graduation']) ? mysqli_real_escape_string($conn, $_POST['year_of_graduation']) : '';
    $address     = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    
    // Redirect logic based on dashboard
    $redirect_page = 'member_dashboard.php'; // default
   

    // Insert query
    $insert_visitor = mysqli_query($conn, "
        INSERT INTO tbl_visitors
        SET event_id='$event_id',
            full_name='$fullname',
            email='$email',
            phone='$phone',
            address='$address',
            department='$department',
            gender='$gender',
            year_of_graduation='$year',
            in_time=NOW()
    ");

    if ($insert_visitor) {
        $popup_message = "Visitor registered successfully!";
        $popup_type = "success";
    } else {
        $popup_message = "Error registering visitor!";
        $popup_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Visitor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f6f7fb;font-family:'Poppins',sans-serif;}
    .container-reg{max-width:800px;width:100%;border-radius:6px;padding:30px;margin:20px auto;background-color:#fff;box-shadow:0 5px 10px rgba(0,0,0,0.1);}
    .container-reg header{font-size:20px;font-weight:600;color:#333;margin-bottom:15px;}
    .fields{display:flex;flex-wrap:wrap;gap:15px;}
    .input-field{flex:1 1 calc(33% - 15px);display:flex;flex-direction:column;}
    .input-field label{font-size:12px;font-weight:500;color:#2e2e2e;}
    .input-field input, .input-field select, textarea{border:1px solid #aaa;border-radius:5px;padding:8px;font-size:14px;}
    .container-reg button{background:#4070f4;color:#fff;border:none;border-radius:5px;padding:10px 20px;cursor:pointer;margin-top:20px;}
    .container-reg button:hover{background:#265df2;}
    @media(max-width:750px){.input-field{flex:1 1 calc(50% - 15px);} }
    @media(max-width:550px){.input-field{flex:1 1 100%;} }
  </style>
</head>
<body>

<div class="container-reg">
    <header>Visitor Registration</header>
    <form action="" method="POST">
        <!-- Hidden input to detect source dashboard -->
        <input type="hidden" name="form_source" value="<?php echo isset($form_source) ? $form_source : 'admin'; ?>">
        <div class="fields">
            <div class="input-field">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="input-field">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div class="input-field">
                <label>Mobile Number</label>
                <input type="text" name="phone" required>
            </div>
            <div class="input-field">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="">Select</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="input-field">
                <label>Event</label>
                <select name="event_id" required>
                    <option value="">Select Event</option>
                    <?php
                    $select_events = mysqli_query($conn,"SELECT * FROM tbl_events ORDER BY event_name ASC");
                    while($event = mysqli_fetch_assoc($select_events)){
                        echo "<option value='".$event['event_id']."'>".$event['event_name']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="input-field">
                <label>Department</label>
                <select name="department" required>
                    <option value="">Select Department</option>
                    <?php
                    $select_department = mysqli_query($conn,"SELECT * FROM tbl_department WHERE status=1 ORDER BY department ASC");
                    while($dept = mysqli_fetch_assoc($select_department)){
                        echo "<option value='".$dept['department']."'>".$dept['department']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="input-field">
                <label>Year of Graduation</label>
                <select name="year_of_graduation" required>
                    <option value="">Select Year</option>
                    <?php for($y = 2007; $y <= date("Y"); $y++){ echo "<option value='$y'>$y</option>"; } ?>
                </select>
            </div>
            <div class="input-field" style="flex:1 1 100%;">
                <label>Address</label>
                <textarea name="address" rows="2" required></textarea>
            </div>
        </div>
        <button type="submit" name="sbt-vstr">Submit</button>
    </form>
</div>

<!-- Popup Modal -->
<div class="modal fade" id="visitorPopup" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-<?php echo $popup_type ?: 'primary'; ?> text-white">
        <h5 class="modal-title"><?php echo ucfirst($popup_type ?: 'Info'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><?php echo $popup_message; ?></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-<?php echo $popup_type ?: 'primary'; ?>" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if($popup_message): ?>
<script>
var popupModal = new bootstrap.Modal(document.getElementById('visitorPopup'));
popupModal.show();
// Redirect to appropriate dashboard after 2.5 seconds
setTimeout(function(){
    window.location.href = "<?php echo $redirect_page; ?>";
}, 2500);
</script>
<?php endif; ?>
</body>
</html>

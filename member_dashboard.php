<?php
session_start();
include 'connection.php';

// If not logged in → redirect to login
if (empty($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Fetch session variables
$name = $_SESSION['name'];
$id = $_SESSION['id'];
$user_role = $_SESSION['role'] ?? 'member';

// Fetch member data from database
$member_query = mysqli_query($conn, "SELECT * FROM tbl_members WHERE id='$id'");
$member_data = mysqli_fetch_assoc($member_query);


// Dashboard Stats for Member
$total_visitors = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM tbl_visitors"));
$checked_in = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM tbl_visitors WHERE status=1 AND in_time IS NOT NULL"));
$goodies_given = mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(SUM(quantity),0) FROM tbl_goodies_distribution"));
$event_participants = mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(SUM(participant_count),0) FROM tbl_event_participation"));
// ================= Visitor Data =================
$visitors = mysqli_query($conn, "SELECT * FROM tbl_visitors ORDER BY created_at DESC LIMIT 10");

// ================= Inventory Data =================
$inventory = mysqli_query($conn, "SELECT * FROM tbl_inventory ORDER BY status, item_name");

// ================= Goodies Distribution =================
$goodies = mysqli_query($conn, "SELECT g.*, v.name as visitor_name FROM tbl_goodies_distribution g LEFT JOIN tbl_visitors v ON g.visitor_id = v.id ORDER BY g.distribution_time DESC LIMIT 10");

// ================= Event Participation =================
$participation = mysqli_query($conn, "SELECT * FROM tbl_event_participation ORDER BY participant_count DESC");

// Fetch announcements (could be from a new table or use coordinator notes)
//$announcements = mysqli_query($conn, "SELECT * FROM tbl_coordinator_notes WHERE note_type='LOG' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>VMS Console — Member Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Fonts & Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"/>

  <style>
    :root{
      --bg:#f6f7fb;
      --card:#ffffff;
      --ink:#1f2937;
      --ink-soft:#6b7280;
      --edge:#e5e7eb;
      --brand:#2563eb;
      --accent:#10b981;
      --warn:#f59e0b;
      --danger:#ef4444;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      background:var(--bg);
      color:var(--ink);
      font-family:'Poppins',sans-serif;
      font-size:15px;
    }

    /* Sidebar */
    .sidebar{
      position:fixed; inset:0 auto 0 0;
      width:260px; background:#0f172a; color:#fff;
      border-right:1px solid rgba(255,255,255,0.06);
      display:flex; flex-direction:column;
    }
    .brand{
      padding:18px 20px; border-bottom:1px solid rgba(255,255,255,0.08);
      display:flex; align-items:center; gap:10px; font-weight:600;
    }
    .brand .logo{display:inline-flex; width:34px; height:34px; border-radius:8px; background:#1d4ed8; align-items:center; justify-content:center}
    .nav{
      list-style:none; padding:10px 10px 24px; margin:0; overflow:auto;
    }
    .nav a{
      display:flex; gap:12px; align-items:center;
      padding:10px 12px; margin:4px 0; color:#cbd5e1; text-decoration:none;
      border-radius:8px; transition:all .15s ease;
    }
    .nav a:hover{background:rgba(255,255,255,0.06); color:#fff}
    .nav .section-label{
      font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8;
      padding:12px 12px 6px; margin-top:6px;
    }
    .nav .is-active{background:#1e293b; color:#fff}

    /* Main */
    .main{
      margin-left:260px; padding:22px;
      min-height:100vh;
    }

    /* Cards */
    .card-lite{
      background:var(--card); border:1px solid var(--edge);
      border-radius:14px; box-shadow:0 2px 10px rgba(16,24,40,.04);
    }
    .card-head{
      padding:16px 18px; border-bottom:1px solid var(--edge);
      display:flex; align-items:center; justify-content:space-between; gap:12px;
    }
    .card-body{ padding:16px 18px; }
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      padding:6px 10px; border-radius:20px; font-size:12px; border:1px solid var(--edge); background:#fff;
    }
    .chip i{opacity:.85}

    /* Tables */
    .table thead th{color:#6b7280; font-weight:600; background:#fafafa}
    .table td, .table th{vertical-align:middle}
    .stat{
      display:flex; align-items:center; gap:10px;
      padding:14px; border-radius:14px; background:var(--card);
      border:1px solid var(--edge);
    }
    .stat i{font-size:18px}
    .muted{color:var(--ink-soft)}
    .btn-soft{
      border:1px solid var(--edge); background:#fff; color:var(--ink);
    }
    .btn-soft:hover{border-color:#cbd5e1; background:#f9fafb}

    /* Topbar */
    .topbar{
      display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:18px;
    }
    .crumbs{
      display:flex; align-items:center; gap:8px; color:var(--ink-soft); font-size:13px;
    }
    .crumbs a{ color:var(--ink-soft); text-decoration:none }
    .crumbs a:hover{ text-decoration:underline }
    .title-row{ display:flex; align-items:center; gap:12px; }
    .title-row h2{ margin:0; font-weight:600; font-size:22px; }
    .title-row .badge{
      background:#eef2ff; color:#4338ca; border:1px solid #e0e7ff;
      padding:6px 10px; border-radius:10px; font-weight:500;
    }

    /*Manage Visitors Dropdown*/
    /* Dropdown styles */
./* Dropdown styles for dark sidebar */
.nav ul.dropdown-menu {
  list-style: none;
  padding-left: 0;
  margin: 0;
  display: none; /* hidden initially */
  background-color: #0f172a !important; /* DARK sidebar color */
  border: none !important;
  border-radius: 8px;
}

.nav ul.dropdown-menu li a {
  display: flex;
  gap: 10px;
  padding: 8px 20px 8px 40px;
  color: #cbd5e1 !important; /* light text */
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.15s ease;
}

.nav ul.dropdown-menu li a:hover {
  background: rgba(255, 255, 255, 0.06) !important; /* hover effect */
  color: #fff !important;
}




    /* Responsive */
    @media (max-width: 992px){
      .sidebar{transform:translateX(-100%); transition:transform .2s ease}
      .sidebar.show{transform:translateX(0)}
      .main{margin-left:0}
      .topbar .toggle{display:inline-flex}
    }
    @media (min-width: 993px){
      .topbar .toggle{display:none}
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <img src="Images/SALogo.png" alt="Specanciens Logo" 
         style="width:50px; height:50px; border-radius:8px; object-fit:cover; margin-left:-15px;">
    <span class="brand-text">SPECANCIENS VMS</span>
  </div>

  <ul class="nav" style="list-style:none; padding:0; margin:0;">
    <!-- Member Dashboard -->
    <li style="margin-bottom:5px;">
      <a href="member_dashboard.php" class="is-active" 
         style="background-color:#0f172a; color:white; border-radius:8px; padding:10px 15px; display:flex; align-items:center; text-decoration:none;">
        <i class="fa-solid fa-house" style="margin-right:8px;"></i>
        <span>Member Dashboard</span>
      </a>
    </li>

    <!-- Manage Events -->
    <li style="margin-bottom:5px;">
      <a href="#view-events" 
         style="background-color:#0f172a; color:white; border-radius:8px; padding:10px 15px; display:flex; align-items:center; text-decoration:none;">
        <i class="fa-solid fa-calendar-days" style="margin-right:8px;"></i>
        <span>Manage Events</span>
      </a>
    </li>

    <!-- View Visitors -->
   <!--
<li style="margin-bottom:5px;">
  <a href="member_view_visitors.php" 
     style="background-color:#0f172a; color:white; border-radius:8px; padding:10px 15px; display:flex; align-items:center; text-decoration:none;">
    <i class="fa-solid fa-eye" style="margin-right:8px;"></i>
    <span>View Visitors</span>
  </a>
</li>
-->


    <!-- Manage Visitors Dropdown -->
    <li class="has-dropdown" style="position:relative; margin-bottom:5px;">
      <a href="#" onclick="toggleDropdown(event)" 
         style="background-color:#0f172a; color:white; border-radius:8px; padding:10px 15px; display:flex; align-items:center; justify-content:space-between; text-decoration:none;">
        <span><i class="fa-solid fa-edit" style="margin-right:8px;"></i> Manage Visitors</span>
        <i class="fa-solid fa-chevron-right toggle-icon"></i>
      </a>
      <ul class="dropdown-menu" 
          style="list-style:none; padding:0; margin:0; display:none; 
                 background-color:#1f2a40; border-radius:8px; position:relative; width:100%;">
        <li>
          <a href="member_new-visitor.php" 
             style="color:white; display:block; padding:10px 15px; text-decoration:none; border-bottom:1px solid #0f172a;">
            <i class="fa-solid fa-user-plus" style="margin-right:8px;"></i> Add Visitor
          </a>
        </li>
        <li>
          <a href="member_manage_visitors.php" 
             style="color:white; display:block; padding:10px 15px; text-decoration:none;">
            <i class="fa-solid fa-pen" style="margin-right:8px;"></i> Edit Visitor
          </a>
        </li>
      </ul>
    </li>
  </ul>
</aside>

<script>
function toggleDropdown(event) {
  event.preventDefault();
  const dropdown = event.currentTarget.nextElementSibling;
  const icon = event.currentTarget.querySelector('.toggle-icon');

  if (dropdown.style.display === 'block') {
    dropdown.style.display = 'none';
    icon.style.transform = 'rotate(0deg)';
  } else {
    dropdown.style.display = 'block';
    icon.style.transform = 'rotate(90deg)';
  }
}
</script>






      <li><a href="#view-visitors"><i class="fa-solid fa-edit"></i><span>View Visitors</span></a></li>
      <li><a href="#manage-notes"><i class="fa-solid fa-user-pen"></i><span>Announcements</span></a></li>
    </ul>
  </aside>

  <!-- Main -->
  <main class="main">
    <!-- Topbar -->
    <div class="topbar">
      <button class="btn btn-soft toggle" id="toggleSidebar"><i class="fa-solid fa-bars"></i></button>
      <div class="crumbs">
        <a href="member_dashboard.php">Dashboard</a>
        <span>›</span>
        <span>Member Dashboard</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="text-muted">Welcome, <?php echo htmlspecialchars($name); ?></span>
        <button class="btn btn-soft"><i class="fa-regular fa-bell"></i></button>
        <button class="btn btn-soft"><i class="fa-regular fa-circle-question"></i></button>
        <a href="logout.php" class="btn btn-soft"><i class="fa-solid fa-right-from-bracket"></i></a>
      </div>
    </div>

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
      <div class="title-row">
        <span class="chip"><i class="fa-solid fa-user text-primary"></i> Member Dashboard</span>
        <h2>👋 Welcome, <?php echo htmlspecialchars($name); ?></h2>
        <span class="badge">Member Access - Live Data </span>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary" onclick="exportData()"><i class="fa-solid fa-download me-2"></i>Export CSV</button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filtersModal"><i class="fa-solid fa-sliders me-2"></i>Filters</button>
        
      </div>
    </div>

    <!-- Quick stats -->
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3">
        <div class="stat">
          <i class="fa-solid fa-users text-primary"></i>
          <div>
            <div class="fw-semibold">Total Visitors</div>
            <div class="muted"><?php echo $total_visitors[0]; ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat">
          <i class="fa-solid fa-door-open text-success"></i>
          <div>
            <div class="fw-semibold">Checked In</div>
            <div class="muted"><?php echo $checked_in[0]; ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat">
          <i class="fa-solid fa-gift text-warning"></i>
          <div>
            <div class="fw-semibold">Goodies Given</div>
            <div class="muted"><?php echo $goodies_given[0] ?: '0'; ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat">
          <i class="fa-solid fa-clipboard-check text-danger"></i>
          <div>
            <div class="fw-semibold">Participants</div>
            <div class="muted"><?php echo $event_participants[0]; ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content grid -->
    <div class="row g-2">
      <!-- Visitor In/Out -->
      <div class="col-12 col-lg-7">
        <div class="card-lite">
          <div class="card-head">
            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-right-left text-primary"></i>
              <span class="fw-semibold">Visitor In/Out</span>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-soft" onclick="location.href='new-visitor.php'"><i class="fa-solid fa-plus me-1"></i>Add</button>
              <button class="btn btn-sm btn-soft" onclick="location.reload()"><i class="fa-solid fa-arrow-rotate-right me-1"></i>Refresh</button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($row = mysqli_fetch_assoc($visitors)) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                    <td><?php echo htmlspecialchars($row['in_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['out_time'] ?: '—'); ?></td>
                    <td>
                      <span class="badge <?php echo $row['out_time'] ? 'text-bg-success-subtle text-success border border-success' : 'text-bg-primary-subtle text-primary border border-primary'; ?>">
                        <?php echo $row['out_time'] ? 'Checked Out' : 'Checked In'; ?>
                      </span>
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <span class="muted">Showing recent visitors</span>
              <a href="member_manage_visitors.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Inventory -->
      <div class="col-12 col-lg-5">
        <div class="card-lite h-100">
          <div class="card-head">
            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-boxes-stacked text-success"></i>
              <span class="fw-semibold">Inventory</span>
            </div>
            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#addInventoryModal"><i class="fa-solid fa-plus me-1"></i>Add Item</button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>In Stock</th>
                    <th>Used</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($item = mysqli_fetch_assoc($inventory)) { 
                    $remaining = $item['total_stock'] - $item['used_count'];
                    $status_class = '';
                    if ($remaining <= 0) {
                      $status_class = 'text-bg-danger-subtle text-danger border border-danger';
                    } elseif ($remaining <= 10) {
                      $status_class = 'text-bg-warning-subtle text-warning border border-warning';
                    } else {
                      $status_class = 'text-bg-success-subtle text-success border border-success';
                    }
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo $item['total_stock']; ?></td>
                    <td><?php echo $item['used_count']; ?></td>
                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $item['status']; ?></span></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#restockModal"><i class="fa-solid fa-arrow-up-short-wide me-1"></i>Restock</button>
              <button class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-file-lines me-1"></i>View Log</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Goodies -->
      <div class="col-12 col-lg-7">
        <div class="card-lite">
          <div class="card-head">
            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-gifts text-warning"></i>
              <span class="fw-semibold">Goodies Distribution</span>
            </div>
            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#addGoodieModal"><i class="fa-solid fa-plus me-1"></i>Record</button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Goodie</th>
                    <th>Qty</th>
                    <th>Time</th>
                    <th>Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($goodie = mysqli_fetch_assoc($goodies)) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($goodie['visitor_name'] ?: 'Unknown'); ?></td>
                    <td><?php echo htmlspecialchars($goodie['goodie_name']); ?></td>
                    <td><?php echo $goodie['quantity']; ?></td>
                    <td><?php echo date('H:i', strtotime($goodie['distribution_time'])); ?></td>
                    <td><?php echo htmlspecialchars($goodie['remarks'] ?: '—'); ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-sm btn-outline-primary"><i class="fa-regular fa-clipboard me-1"></i>Summary</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Participation -->
      <div class="col-12 col-lg-5">
        <div class="card-lite">
          <div class="card-head">
            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-clipboard-list text-danger"></i>
              <span class="fw-semibold">Event Participation</span>
            </div>
            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#addParticipationModal"><i class="fa-solid fa-user-plus me-1"></i>Add</button>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <?php while($activity = mysqli_fetch_assoc($participation)) { ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo htmlspecialchars($activity['activity_name']); ?>
                <span class="badge rounded-pill text-bg-primary"><?php echo $activity['participant_count']; ?></span>
              </li>
              <?php } ?>
            </ul>
            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-filter me-1"></i>Filter</button>
              <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-share-nodes me-1"></i>Share</button>
            </div>
          </div>
        </div>
      </div>    


  <!-- Event Registration Modal -->
  <div class="modal fade" id="registrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Event Registration</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="registrationForm">
            <input type="hidden" name="event_id" id="event_id">
            <input type="hidden" name="event_name" id="event_name">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($member_data['member_name'] ?? ''); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($member_data['emailid'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Phone Number (India) <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="phone" required pattern="[+91]{3} [0-9]{10}" placeholder="+91 9876543210" value="<?php echo htmlspecialchars($member_data['whatsapp'] ?? ''); ?>">
                <div class="form-text">Format: +91 followed by 10-digit number</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Department</label>
                <select class="form-select" name="department">
                  <option value="">Select Department</option>
                  <?php
                  $depts = mysqli_query($conn, "SELECT * FROM tbl_department WHERE status=1 ORDER BY department");
                  while($dept = mysqli_fetch_assoc($depts)) {
                    $selected = ($member_data['department'] ?? '') == $dept['department'] ? 'selected' : '';
                    echo "<option value='{$dept['department']}' $selected>{$dept['department']}</option>";
                  }
                  ?>
                </select>
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Food Preferences</label>
                <select class="form-select" name="food_preference">
                  <option value="">Select Preference</option>
                  <option value="Vegetarian">Vegetarian</option>
                  <option value="Non-Vegetarian">Non-Vegetarian</option>
                  <option value="Vegan">Vegan</option>
                  <option value="Jain">Jain</option>
                  <option value="No Preference">No Preference</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Dietary Restrictions</label>
                <input type="text" class="form-control" name="dietary_restrictions" placeholder="Any allergies or restrictions">
              </div>
              
              <div class="col-md-4">
                <label class="form-label">LinkedIn Profile</label>
                <input type="url" class="form-control" name="linkedin" placeholder="https://linkedin.com/in/username" value="<?php echo htmlspecialchars($member_data['linkedin'] ?? ''); ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Instagram ID</label>
                <input type="text" class="form-control" name="instagram" placeholder="@username" value="<?php echo htmlspecialchars($member_data['instagram'] ?? ''); ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Twitter/X ID</label>
                <input type="text" class="form-control" name="twitter" placeholder="@username">
              </div>
              
              <div class="col-12">
                <label class="form-label">How did you hear about this event?</label>
                <select class="form-select" name="referral_source">
                  <option value="">Select Source</option>
                  <option value="Email">Email</option>
                  <option value="Social Media">Social Media</option>
                  <option value="Word of Mouth">Word of Mouth</option>
                  <option value="College Notice">College Notice</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              
              <div class="col-12">
                <label class="form-label">Additional Comments or Questions</label>
                <textarea class="form-control" name="comments" rows="3" placeholder="Any special requests or questions"></textarea>
              </div>
              
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="newsletter" id="newsletter" checked>
                  <label class="form-check-label" for="newsletter">
                    Subscribe to our newsletter for future events
                  </label>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="submitRegistration()">Complete Registration</button>
        </div>
     <div class="row g-3 mb-3">
  <div class="col-6 col-md-4">
    <div class="stat">
      <i class="fa-solid fa-users text-primary"></i>
      <div>
        <div class="fw-semibold">Total Visitors</div>
        <div class="muted"><?php echo $total_visitors[0]; ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat">
      <i class="fa-solid fa-door-open text-success"></i>
      <div>
        <div class="fw-semibold">Checked In</div>
        <div class="muted"><?php echo $checked_in[0]; ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat">
      <i class="fa-solid fa-gift text-warning"></i>
      <div>
        <div class="fw-semibold">Goodies Given</div>
        <div class="muted"><?php echo $goodies_given[0] ?: '0'; ?></div>
      </div>
    </div>
  </div>
</div>

  <!-- Scripts -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
   const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('toggleSidebar');

if (toggle) {
  toggle.addEventListener('click', (e) => {
    e.stopPropagation(); // prevent this click from immediately closing the sidebar
    sidebar.classList.toggle('show');
  });
}

// Close sidebar when clicking outside
document.addEventListener('click', (e) => {
  if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
    sidebar.classList.remove('show');
  }
});
    
    document.getElementById('profileForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch('update_profile.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Profile updated successfully!');
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error updating profile');
      });
    });
    //add filter functionality 
    function applyFilters() {
  const form = document.getElementById('filterForm');
  const formData = new FormData(form);
  const params = new URLSearchParams(formData).toString();
  window.location.href = 'member_dashboard.php?' + params;
}

   // Simple CSV export for visitor data  
function exportData() {
     
      fetch('export_visitors.php')
        .then(response => response.text())
        .then(csv => {
          const blob = new Blob([csv], { type: 'text/csv' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'visitors_export.csv';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          window.URL.revokeObjectURL(url);
        })
        .catch(error => {
          alert('Error exporting data: ' + error);
        });
    }
function toggleDropdown(event) {
  event.preventDefault();
  const dropdownMenu = event.currentTarget.nextElementSibling;
  if(dropdownMenu.style.display === "none" || dropdownMenu.style.display === "") {
    dropdownMenu.style.display = "block";
  } else {
    dropdownMenu.style.display = "none";
  }
}

  </script>
  
</body>
</html>

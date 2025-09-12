<?php
// side-bar.php
// ensure this file is included where $conn is available, or include connection.php here:
if (!isset($conn)) {
    include 'connection.php';
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch counts for sidebar badges
$visitor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tbl_visitors WHERE status=1"))['count'];
$inventory_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tbl_inventory"))['count'];
$goodies_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tbl_goodies_distribution"))['count'];
$participation_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tbl_event_participation"))['count'];
$notes_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tbl_coordinator_notes"))['count'];
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <span class="logo"><i class="fa-solid fa-bullseye text-white"></i></span>
    <span>VMS Console</span>
  </div>
  <ul class="nav">
    <li><a href="admin_dashboard.php" class="<?php echo $current_page == 'admin_dashboard.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-house"></i><span>Admin Dashboard</span></a></li>
    
    <li class="section-label">Visitor Management</li>
    <li><a href="new-visitor.php" class="<?php echo $current_page == 'new-visitor.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-user-plus"></i><span>New Visitor</span></a></li>
    <li><a href="manage-visitors.php" class="<?php echo $current_page == 'manage-visitors.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-users-gear"></i><span>Manage Visitors</span><span class="badge"><?php echo $visitor_count; ?></span></a></li>
    
    <li class="section-label">Inventory Management</li>
    <li><a href="add_inventory.php" class="<?php echo $current_page == 'add_inventory.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-boxes"></i><span>Add Inventory</span></a></li>
    <li><a href="manage-inventory.php" class="<?php echo $current_page == 'manage-inventory.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-warehouse"></i><span>Manage Inventory</span><span class="badge"><?php echo $inventory_count; ?></span></a></li>
    
    <li class="section-label">Goodies Distribution</li>
    <li><a href="add_goodie.php" class="<?php echo $current_page == 'add_goodie.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-gift"></i><span>Add Goodie</span></a></li>
    <li><a href="manage-goodies.php" class="<?php echo $current_page == 'manage-goodies.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-truck"></i><span>Manage Goodies</span><span class="badge"><?php echo $goodies_count; ?></span></a></li>
    
    <li class="section-label">Event Participation</li>
    <li><a href="add_participation.php" class="<?php echo $current_page == 'add_participation.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-calendar-plus"></i><span>Add Participation</span></a></li>
    <li><a href="manage-participation.php" class="<?php echo $current_page == 'manage-participation.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-chart-line"></i><span>Manage Participation</span><span class="badge"><?php echo $participation_count; ?></span></a></li>
    
    <li class="section-label">Coordinator Notes</li>
    <li><a href="add_note.php" class="<?php echo $current_page == 'add_note.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-sticky-note"></i><span>Add Note</span></a></li>
    <li><a href="manage-notes.php" class="<?php echo $current_page == 'manage-notes.php' ? 'is-active' : ''; ?>"><i class="fa-solid fa-clipboard-list"></i><span>Manage Notes</span><span class="badge"><?php echo $notes_count; ?></span></a></li>
    
    <li class="section-label">Event Dashboards</li>
    <li><a href="#"><i class="fa-solid fa-scroll"></i><span>Nostalgia</span></a></li>
    <li><a href="#"><i class="fa-solid fa-microphone-lines"></i><span>Alumni Talks</span></a></li>
    <li><a href="#"><i class="fa-solid fa-graduation-cap"></i><span>Induction Program</span></a></li>
    <li><a href="#"><i class="fa-solid fa-briefcase"></i><span>Mock Interviews</span></a></li>
  </ul>
</aside>

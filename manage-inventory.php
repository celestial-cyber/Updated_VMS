<?php
session_start();
include ('connection.php');
$name = $_SESSION['name'];
$id = $_SESSION['id'];
if(empty($id))
{
    header("Location: index.php");
    exit();
}

$breadcrumbs = [
    ['url' => 'admin_dashboard.php', 'text' => 'Dashboard'],
    ['text' => 'Manage Inventory']
];
?>
<?php include('include/header.php'); ?>
<?php include('include/top-bar.php'); ?>

<!-- Content -->
<div class="container-fluid">
  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <div class="title-row">
      <span class="chip"><i class="fa-solid fa-warehouse text-primary"></i> Inventory</span>
      <h2>📦 Manage Inventory</h2>
      <span class="badge">Live Data</span>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" onclick="location.href='add_inventory.php'"><i class="fa-solid fa-plus me-2"></i>Add Item</button>
      <button class="btn btn-outline-primary" onclick="location.reload()"><i class="fa-solid fa-arrow-rotate-right me-2"></i>Refresh</button>
    </div>
  </div>

  <!-- Inventory Table -->
  <div class="card-lite">
    <div class="card-head">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-boxes text-primary"></i>
        <span class="fw-semibold">Inventory List</span>
      </div>
      <span class="text-muted">All Inventory Items</span>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>S.No.</th>
              <th>Item Name</th>
              <th>Category</th>
              <th>Quantity</th>
              <th>Status</th>
              <th>Last Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if(isset($_GET['ids'])){
              $id = $_GET['ids'];
              $delete_query = mysqli_query($conn, "DELETE FROM tbl_inventory WHERE id='$id'");
              if($delete_query) {
                echo "<script>alert('Inventory item deleted successfully');</script>";
              }
            }
            
            $select_query = mysqli_query($conn, "SELECT * FROM tbl_inventory ORDER BY created_at DESC");
            $sn = 1;
            while($row = mysqli_fetch_array($select_query))
            {
            ?>
            <tr>
              <td><?php echo $sn; ?></td>
              <td><?php echo htmlspecialchars($row['item_name']); ?></td>
              <td><?php echo htmlspecialchars($row['category']); ?></td>
              <td><?php echo htmlspecialchars($row['quantity']); ?></td>
              <td>
                <span class="badge <?php echo $row['status'] == 'Available' ? 'text-bg-success-subtle text-success border border-success' : 'text-bg-warning-subtle text-warning border border-warning'; ?>">
                  <?php echo htmlspecialchars($row['status']); ?>
                </span>
              </td>
              <td><?php echo date('M d, Y', strtotime($row['updated_at'])); ?></td>
              <td>
                <div class="d-flex gap-2">
                  <a href="edit-inventory.php?id=<?php echo $row['id'];?>" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-pencil me-1"></i>Edit
                  </a>
                  <a href="manage-inventory.php?ids=<?php echo $row['id'];?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">
                    <i class="fa-solid fa-trash me-1"></i>Delete
                  </a>
                </div>
              </td>
            </tr>
            <?php $sn++; } ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <span class="muted">Showing all inventory items</span>
        <button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-download me-1"></i>Export</button>
      </div>
    </div>
  </div>
</div>

<?php include('include/footer.php'); ?>
<script>
function confirmDelete(){
    return confirm('Are you sure want to delete this inventory item?');
}
</script>
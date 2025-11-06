<?php
session_start();
include ('connection.php');
include 'include/guard_admin.php';

$name = $_SESSION['name'];
$id = $_SESSION['id'];
if(empty($id)) {
    header("Location: index.php");
    exit();
}

/* ----------------------
   HANDLERS: Add / Restock / Mark Used / Delete
   ---------------------- */

// Add new item
if (isset($_POST['add_item'])) {
    $item_name = mysqli_real_escape_string($conn, trim($_POST['item_name']));
    $total_stock = max(0, intval($_POST['total_stock']));
    $status = mysqli_real_escape_string($conn, trim($_POST['status'] ?: 'Available'));

    if ($item_name === '') {
        echo "<script>alert('Enter item name');</script>";
    } else {
        $stmt = "INSERT INTO tbl_inventory (item_name, total_stock, used_count, status, created_at, updated_at)
                 VALUES ('$item_name', $total_stock, 0, '$status', NOW(), NOW())";
        if (mysqli_query($conn, $stmt)) {
            echo "<script>alert('Item added successfully'); window.location.href='manage_inventory.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error adding item');</script>";
        }
    }
}

// Restock item
if (isset($_POST['restock_item'])) {
    $item_id = intval($_POST['restock_item_id']);
    $add_qty = max(0, intval($_POST['restock_quantity']));

    if ($add_qty <= 0) {
        echo "<script>alert('Enter a valid restock quantity');</script>";
    } else {
        $q = "UPDATE tbl_inventory SET total_stock = total_stock + $add_qty, updated_at = NOW(), status = 'Available' WHERE id = $item_id";
        if (mysqli_query($conn, $q)) {
            echo "<script>alert('Restocked successfully'); window.location.href='manage_inventory.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error while restocking');</script>";
        }
    }
}

// Mark used (decrement remaining by increasing used_count)
if (isset($_POST['mark_used'])) {
    $item_id = intval($_POST['item_id']);
    $used_qty = max(0, intval($_POST['used_quantity']));

    if ($used_qty <= 0) {
        echo "<script>alert('Enter a valid quantity');</script>";
    } else {
        // fetch remaining
        $res = mysqli_query($conn, "SELECT total_stock, used_count FROM tbl_inventory WHERE id = $item_id");
        if ($res && mysqli_num_rows($res)) {
            $it = mysqli_fetch_assoc($res);
            $remaining = intval($it['total_stock']) - intval($it['used_count']);
            if ($used_qty > $remaining) {
                echo "<script>alert('Not enough stock. Remaining: $remaining');</script>";
            } else {
                $q = "UPDATE tbl_inventory SET used_count = used_count + $used_qty, updated_at = NOW()
                      WHERE id = $item_id";
                if (mysqli_query($conn, $q)) {
                    // if no remaining left, update status
                    $res2 = mysqli_query($conn, "SELECT total_stock, used_count FROM tbl_inventory WHERE id = $item_id");
                    $it2 = mysqli_fetch_assoc($res2);
                    $rem2 = intval($it2['total_stock']) - intval($it2['used_count']);
                    if ($rem2 <= 0) {
                        mysqli_query($conn, "UPDATE tbl_inventory SET status = 'Out of Stock' WHERE id = $item_id");
                    } elseif ($rem2 <= 10) {
                        mysqli_query($conn, "UPDATE tbl_inventory SET status = 'Low' WHERE id = $item_id");
                    } else {
                        mysqli_query($conn, "UPDATE tbl_inventory SET status = 'Available' WHERE id = $item_id");
                    }

                    echo "<script>alert('Usage recorded'); window.location.href='manage_inventory.php';</script>";
                    exit;
                } else {
                    echo "<script>alert('Error updating usage');</script>";
                }
            }
        } else {
            echo "<script>alert('Item not found');</script>";
        }
    }
}

// Delete item (POST)
if (isset($_POST['delete_item'])) {
    $del_id = intval($_POST['delete_item_id']);
    if ($del_id > 0) {
        if (mysqli_query($conn, "DELETE FROM tbl_inventory WHERE id = $del_id")) {
            echo "<script>alert('Item deleted'); window.location.href='manage_inventory.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error deleting item');</script>";
        }
    }
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
      <!-- Add item uses modal -->
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="fa-solid fa-plus me-2"></i>Add Item
      </button>

      <button class="btn btn-outline-primary" onclick="location.reload()">
        <i class="fa-solid fa-arrow-rotate-right me-2"></i>Refresh
      </button>

      <!-- Delete all (keeps existing link behavior but protected) -->
      <form method="POST" style="display:inline" onsubmit="return confirm('Delete ALL inventory items? This cannot be undone.')">
        <input type="hidden" name="delete_all" value="1">
        <button type="submit" class="btn btn-outline-danger">
          <i class="fa-solid fa-trash me-2"></i>Delete All
        </button>
      </form>

      <a class="btn btn-outline-secondary" href="inventory_log.php">
        <i class="fa-regular fa-file-lines me-2"></i>View Log
      </a>
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
              <th>Total</th>
              <th>Used</th>
              <th>Remaining</th>
              <th>Availability</th>
              <th>Last Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $select_query = mysqli_query($conn, "SELECT * FROM tbl_inventory ORDER BY created_at DESC");
            $sn = 1;
            while($row = mysqli_fetch_assoc($select_query)) {
              $total = intval($row['total_stock']);
              $used = intval($row['used_count']);
              $remaining = max(0, $total - $used);

              // determine badge class
              if ($remaining <= 0) {
                $badge_class = 'text-bg-danger-subtle text-danger border border-danger';
              } elseif ($remaining <= 10) {
                $badge_class = 'text-bg-warning-subtle text-warning border border-warning';
              } else {
                $badge_class = 'text-bg-success-subtle text-success border border-success';
              }
            ?>
            <tr>
              <td><?php echo $sn; ?></td>
              <td><?php echo htmlspecialchars($row['item_name']); ?></td>
              <td><?php echo $total; ?></td>
              <td><?php echo $used; ?></td>
              <td><?php echo $remaining; ?></td>
              <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
              <td><?php echo date('M d, Y H:i', strtotime($row['updated_at'])); ?></td>
              <td>
                <div class="d-flex gap-2">
                  <!-- Edit page (optional) -->
                  <a href="edit-inventory.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-pencil me-1"></i>Edit
                  </a>

                  <!-- Restock -->
                  <button class="btn btn-sm btn-outline-success"
                    onclick="openRestockModal('<?php echo $row['id']; ?>','<?php echo htmlspecialchars(addslashes($row['item_name'])); ?>')">
                    <i class="fa-solid fa-plus me-1"></i>Restock
                  </button>

                  <!-- Mark used -->
                  <button class="btn btn-sm btn-outline-secondary"
                    onclick="openMarkUsedModal('<?php echo $row['id']; ?>','<?php echo htmlspecialchars(addslashes($row['item_name'])); ?>')">
                    <i class="fa-solid fa-box-open me-1"></i>Mark Used
                  </button>

                  <!-- Delete (POST) -->
                  <form method="POST" style="display:inline" onsubmit="return confirm('Delete this item?');">
                    <input type="hidden" name="delete_item_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_item" class="btn btn-sm btn-outline-danger">
                      <i class="fa-solid fa-trash me-1"></i>Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php $sn++; } ?>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <span class="muted">Showing all inventory items</span>
        <button class="btn btn-sm btn-outline-secondary" onclick="exportInventory()"><i class="fa-solid fa-download me-1"></i>Export</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="addItemModalLabel">Add New Inventory Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Item Name</label>
            <input type="text" name="item_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Initial Stock</label>
            <input type="number" name="total_stock" class="form-control" min="0" value="0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="Available">Available</option>
              <option value="Low">Low</option>
              <option value="Out of Stock">Out of Stock</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="restockModalLabel">Restock Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="restock_item_id" id="restock_item_id">
          <div class="mb-3">
            <label class="form-label">Item</label>
            <input type="text" id="restock_item_name" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity to Add</label>
            <input type="number" name="restock_quantity" class="form-control" min="1" value="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="restock_item" class="btn btn-success">Restock</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Mark Used Modal -->
<div class="modal fade" id="markUsedModal" tabindex="-1" aria-labelledby="markUsedModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title" id="markUsedModalLabel">Mark Item as Used</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="item_id" id="mark_item_id">
          <div class="mb-3">
            <label class="form-label">Item</label>
            <input type="text" id="mark_item_name" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity Used</label>
            <input type="number" name="used_quantity" class="form-control" min="1" value="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="mark_used" class="btn btn-secondary">Save</button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('include/footer.php'); ?>

<script>
function openRestockModal(id, name) {
  document.getElementById('restock_item_id').value = id;
  document.getElementById('restock_item_name').value = name;
  var modal = new bootstrap.Modal(document.getElementById('restockModal'));
  modal.show();
}

function openMarkUsedModal(id, name) {
  document.getElementById('mark_item_id').value = id;
  document.getElementById('mark_item_name').value = name;
  var modal = new bootstrap.Modal(document.getElementById('markUsedModal'));
  modal.show();
}

// Export table as simple CSV
function exportInventory() {
  fetch('export_inventory.php')
    .then(r => r.text())
    .then(csv => {
      const blob = new Blob([csv], {type: 'text/csv'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'inventory_export.csv';
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    }).catch(e => alert('Export failed'));
}
</script>

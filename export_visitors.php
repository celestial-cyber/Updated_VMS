<?php
session_start();
include 'connection.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="visitors_export.csv"');

$output = fopen('php://output', 'w');

// Output CSV header
fputcsv($output, ['ID', 'Name', 'Department', 'In Time', 'Out Time', 'Status', 'Goodies Count', 'Created At']);

// Fetch visitor data
// Optional event filter by name
$eventFilter = '';
if (!empty($_GET['event'])) {
    $eventName = mysqli_real_escape_string($conn, $_GET['event']);
    $eventRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT event_id FROM tbl_events WHERE event_name='$eventName' LIMIT 1"));
    if ($eventRow) {
        $eventId = (int)$eventRow['event_id'];
        $eventFilter = " WHERE event_id=$eventId ";
    }
}

$result = mysqli_query($conn, "SELECT * FROM tbl_visitors $eventFilter ORDER BY created_at DESC");

while ($row = mysqli_fetch_assoc($result)) {
    $status = $row['out_time'] ? 'Checked Out' : 'Checked In';
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['department'],
        $row['in_time'],
        $row['out_time'],
        $status,
        $row['goodies_count'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
?>

<?php
session_start();
include 'connection.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="visitors_export.csv"');

$output = fopen('php://output', 'w');

// Output CSV header
fputcsv($output, ['ID', 'Name', 'Department', 'In Time', 'Out Time', 'Status', 'Goodies Count', 'Created At']);

// Fetch visitor data
$result = mysqli_query($conn, "SELECT * FROM tbl_visitors ORDER BY created_at DESC");

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

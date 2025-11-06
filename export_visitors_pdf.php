<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
include 'connection.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Filters
$department = $_GET['department'] ?? '';
$eventName  = $_GET['event'] ?? '';

$where = "WHERE 1=1";
if ($department !== '') {
  $where .= " AND department='" . mysqli_real_escape_string($conn, $department) . "'";
}
if ($eventName !== '') {
  $ename = mysqli_real_escape_string($conn, $eventName);
  $erow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT event_id FROM tbl_events WHERE event_name='$ename' LIMIT 1"));
  if ($erow) { $where .= " AND event_id=" . (int)$erow['event_id']; }
}

$result = mysqli_query($conn, "SELECT name,email,phone,department,in_time,out_time FROM tbl_visitors $where ORDER BY in_time DESC");

// Build HTML
$rows = '';
while ($r = mysqli_fetch_assoc($result)) {
  $rows .= '<tr>'
        . '<td>' . htmlspecialchars($r['name'] ?? '—') . '</td>'
        . '<td>' . htmlspecialchars($r['email'] ?? '—') . '</td>'
        . '<td>' . htmlspecialchars($r['phone'] ?? '—') . '</td>'
        . '<td>' . htmlspecialchars($r['department'] ?? '—') . '</td>'
        . '<td>' . htmlspecialchars($r['in_time'] ?? '—') . '</td>'
        . '<td>' . htmlspecialchars($r['out_time'] ?? '—') . '</td>'
        . '</tr>';
}

$title = 'Visitors Report';
if ($eventName) { $title .= ' — ' . htmlspecialchars($eventName); }
if ($department) { $title .= ' (' . htmlspecialchars($department) . ')'; }

$html = "<!doctype html>
<html><head><meta charset='utf-8'><style>
body{font-family: DejaVu Sans, sans-serif;}
h2{margin:0 0 10px 0}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ccc;padding:6px 8px;font-size:12px}
th{background:#f2f2f2;text-align:left}
</style></head><body>
<h2>$title</h2>
<table>
  <thead><tr>
    <th>Name</th><th>Email</th><th>Mobile</th><th>Department</th><th>In</th><th>Out</th>
  </tr></thead>
  <tbody>$rows</tbody>
</table>
</body></html>";

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('visitors_report.pdf', ['Attachment' => true]);
exit;
?>






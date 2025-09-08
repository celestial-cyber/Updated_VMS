<?php
session_start();
include 'connection.php';
require 'vendor/autoload.php';
use Dompdf\Dompdf;

// ================= Get Filters =================
$department = $_GET['department'] ?? '';
$year       = $_GET['year'] ?? '';
$gender     = $_GET['gender'] ?? '';

// ================= Build Query =================
$where = "WHERE 1=1";
if ($department != '') $where .= " AND department='" . mysqli_real_escape_string($conn, $department) . "'";
if ($year != '')       $where .= " AND year_of_graduation='" . mysqli_real_escape_string($conn, $year) . "'";
if ($gender != '')     $where .= " AND gender='" . mysqli_real_escape_string($conn, $gender) . "'";

$query = "SELECT * FROM tbl_visitors $where ORDER BY in_time DESC";
$result = mysqli_query($conn, $query);

// ================= Build HTML =================
$html = "
<h2 style='text-align:center;'>Visitors Report</h2>
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<thead>
<tr style='background:#f2f2f2;'>
  <th>Name</th>
  <th>Department</th>
  <th>Year</th>
  <th>Gender</th>
  <th>In Time</th>
</tr>
</thead>
<tbody>";

while ($row = mysqli_fetch_assoc($result)) {
    $html .= "<tr>
        <td>{$row['name']}</td>
        <td>{$row['department']}</td>
        <td>{$row['year_of_graduation']}</td>
        <td>{$row['gender']}</td>
        <td>{$row['in_time']}</td>
    </tr>";
}

$html .= "</tbody></table>";

// ================= Generate PDF =================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("visitors_report.pdf", ["Attachment" => true]);

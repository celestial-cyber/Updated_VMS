<?php
session_start();
include 'connection.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

// ================= Spreadsheet =================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Headers
$sheet->setCellValue('A1', 'Name');
$sheet->setCellValue('B1', 'Department');
$sheet->setCellValue('C1', 'Year');
$sheet->setCellValue('D1', 'Gender');
$sheet->setCellValue('E1', 'In Time');

// Data
$rowCount = 2;
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue("A$rowCount", $row['name']);
    $sheet->setCellValue("B$rowCount", $row['department']);
    $sheet->setCellValue("C$rowCount", $row['year_of_graduation']);
    $sheet->setCellValue("D$rowCount", $row['gender']);
    $sheet->setCellValue("E$rowCount", $row['in_time']);
    $rowCount++;
}

// ================= Output Excel =================
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="visitors_report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();

<?php
$server="localhost";
$username="root";
$password="Somwith@07";
$databasename="vms_db";

$conn = mysqli_connect($server, $username, $password);

$abc=mysqli_select_db($conn,$databasename);

if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

if (!$abc) {
	die("Database selection failed: " . mysqli_error($conn));
}
?>
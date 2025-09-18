<?php
$server="localhost";
$username="root";
$password="root@123";
$databasename="vms_db";

// Connect to MySQL including the database
$conn = mysqli_connect($server, $username, $password, $databasename);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully!";
?>

<?php
$server="localhost";
$username="root";
$password="";
$databasename="vms_db";

$conn = mysqli_connect($server, $username, $password);

$abc=mysqli_select_db($conn,$databasename);

if(!$abc)
{
	die("disconnect");
}
else
{
	//die ("successfull");
}
?>
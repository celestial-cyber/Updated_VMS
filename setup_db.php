<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First connect without database selection
$server = "localhost";
$username = "root";
$password = "root@123";

$conn = mysqli_connect($server, $username, $password);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sqlFile = __DIR__ . '/setup.sql';
if (!file_exists($sqlFile)) {
    die('setup.sql not found');
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    die('Unable to read setup.sql');
}

// Execute multi query
if (mysqli_multi_query($conn, $sql)) {
    echo "Database and tables created successfully!\n";
    do {
        // Store first result set
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
} else {
    echo "Error creating database and tables: " . mysqli_error($conn);
}

$errors = [];
foreach ($statements as $statement) {
	if ($statement === '') continue;
	if (!mysqli_query($conn, $statement . ';')) {
		$errors[] = mysqli_error($conn);
	}
}

if ($errors) {
	echo "Completed with errors (some statements may have succeeded):\n\n";
	echo implode("\n", $errors);
	exit(1);
}

echo "Database setup completed successfully.";
?>


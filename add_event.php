<?php
session_start();
include 'connection.php';

// Check if user is logged in as admin
if (empty($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    
    // Validate inputs
    if (empty($event_name) || empty($event_date)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Insert event into database
    $query = "INSERT INTO tbl_events (event_name, event_date) VALUES ('$event_name', '$event_date')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Event created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating event: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?>
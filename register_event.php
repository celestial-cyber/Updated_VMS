<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (empty($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$member_id = $_SESSION['id'];
$name = $_SESSION['name'];

// Get POST data
$event_id = $_POST['event_id'] ?? null;
$event_name = $_POST['event_name'] ?? null;
$email = $_POST['email'] ?? null;
$phone = $_POST['phone'] ?? null;
$food_preference = $_POST['food_preference'] ?? null;
$linkedin = $_POST['linkedin'] ?? null;
$twitter = $_POST['twitter'] ?? null;
$instagram = $_POST['instagram'] ?? null;

// Validate required fields
if (!$event_id || !$event_name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Check if event exists and get event name
$event_check = mysqli_query($conn, "SELECT * FROM tbl_events WHERE event_id='$event_id'");
if (mysqli_num_rows($event_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Event not found']);
    exit();
}
$event_row = mysqli_fetch_assoc($event_check);
$event_name = $event_row['event_name'];

// Check if already registered
$existing_reg = mysqli_query($conn, "SELECT * FROM event_registrations WHERE user_id='$member_id' AND event='$event_name'");
if (mysqli_num_rows($existing_reg) > 0) {
    echo json_encode(['success' => false, 'message' => 'Already registered for this event']);
    exit();
}

// Insert registration
$sql = "INSERT INTO event_registrations (user_id, name, email, phone, food_preference, linkedin, twitter, instagram, attendance_status, event, event_date)
        VALUES ('$member_id', '$name', '$email', '$phone', '$food_preference', '$linkedin', '$twitter', '$instagram', 'REGISTERED', '$event_name', '$event_row[event_date]')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>
<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to send messages.";
    exit();
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Get the message and conversation ID from the POST request
$message = trim($_POST['message']);
$conversation_id = $_POST['conversation_id'];

// Insert the message into the database
$sql = "INSERT INTO messages (conversation_id, sender, message, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $conversation_id, $username, $message);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
$stmt->close();

$conn->close();
?>
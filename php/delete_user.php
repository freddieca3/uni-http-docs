<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is an admin
if (!isset($_SESSION['admin_username'])) {
    echo "Access denied.";
    exit();
}

// Get the user ID from the POST request
$user_id = $_POST['user_id'];

// Delete related records from the conversations table
$sql = "DELETE FROM conversations WHERE user1_id = ? OR user2_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->close();

// Delete related records from the messages table
$sql = "DELETE FROM messages WHERE sender_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Delete related records from the blocks table
$sql = "DELETE FROM blocks WHERE blocker_id = ? OR blocked_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->close();

// Delete the user from the database
$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    echo "User removed successfully.";
} else {
    echo "Failed to remove user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
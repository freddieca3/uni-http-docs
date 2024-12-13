<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to see your messages.";
    exit();
}

$conversation_id = $_GET['conversation_id'];

// Fetch messages from the database
$sql = "SELECT sender, message, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$stmt->bind_result($sender, $message, $created_at);

while ($stmt->fetch()) {
    echo "<p><strong>" . htmlspecialchars($sender) . ":</strong> " . htmlspecialchars($message) . " <small>(" . htmlspecialchars($created_at) . ")</small></p>";
}

$stmt->close();
$conn->close();
?>
<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to see your messages.']);
    exit();
}

$conversation_id = $_GET['conversation_id'];

// Fetch messages from the database
$sql = "SELECT sender, message, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender' => htmlspecialchars($row['sender']),
        'message' => htmlspecialchars($row['message']),
        'created_at' => htmlspecialchars($row['created_at'])
    ];
}

echo json_encode(['success' => true, 'messages' => $messages]);

$stmt->close();
$conn->close();
?>
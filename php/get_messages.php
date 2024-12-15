<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to see your messages.']);
    exit();
}

$chat_id = $_GET['chat_id'];

// Fetch messages from the database
$sql = "SELECT sender, message, created_at FROM messages WHERE chat_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $chat_id);
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
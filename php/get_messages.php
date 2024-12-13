<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to see your messages.']);
    exit();
}

// Fetch messages from the database
<<<<<<< HEAD
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
=======
$sql = "SELECT sender, message, created_at FROM messages ORDER BY created_at ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p><strong>" . htmlspecialchars($row['sender']) . ":</strong> " . htmlspecialchars($row['message']) . " <small>(" . htmlspecialchars($row['created_at']) . ")</small></p>";
    }
} else {
    echo "<p>No messages available.</p>";
}

>>>>>>> parent of c0d6a43 (messages)
$conn->close();
?>
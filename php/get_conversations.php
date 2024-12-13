<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to view conversations.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch conversations
$sql = "SELECT c.conversation_id, u.username 
        FROM conversations c
        JOIN users u ON (c.user1_id = u.user_id OR c.user2_id = u.user_id)
        WHERE (c.user1_id = ? OR c.user2_id = ?) AND u.user_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($conversation_id, $username);

$conversations = [];
while ($stmt->fetch()) {
    $conversations[] = ['conversation_id' => $conversation_id, 'username' => $username];
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'conversations' => $conversations]);
?>
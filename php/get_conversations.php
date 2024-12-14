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
$sql = "SELECT c.chat_id, 
               CASE 
                   WHEN c.user1_id = ? THEN u2.username 
                   ELSE u1.username 
               END AS username
        FROM conversations c
        JOIN users u1 ON c.user1_id = u1.user_id
        JOIN users u2 ON c.user2_id = u2.user_id
        WHERE c.user1_id = ? OR c.user2_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($chat_id, $username);

$conversations = [];
while ($stmt->fetch()) {
    $conversations[] = ['chat_id' => $chat_id, 'username' => $username];
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'conversations' => $conversations]);
?>
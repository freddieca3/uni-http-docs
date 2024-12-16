<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to view conversations.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch blocked user IDs
$blocked_users = [];
$sql = "SELECT blocked_id FROM blocks WHERE blocker_id = ? UNION SELECT blocker_id FROM blocks WHERE blocked_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($blocked_user_id);
while ($stmt->fetch()) {
    $blocked_users[] = $blocked_user_id;
}
$stmt->close();

// Fetch conversations
$sql = "SELECT c.conversation_id, c.chat_id, 
               CASE 
                   WHEN c.user1_id = ? THEN u2.username 
                   ELSE u1.username 
               END AS username
        FROM conversations c
        JOIN users u1 ON c.user1_id = u1.user_id
        JOIN users u2 ON c.user2_id = u2.user_id
        WHERE (c.user1_id = ? OR c.user2_id = ?)";

if (!empty($blocked_users)) {
    $placeholders = implode(',', array_fill(0, count($blocked_users), '?'));
    $sql .= " AND c.user1_id NOT IN ($placeholders) AND c.user2_id NOT IN ($placeholders)";
}

$stmt = $conn->prepare($sql);
$params = array_merge([$user_id, $user_id, $user_id], $blocked_users);
$stmt->bind_param(str_repeat('i', count($params)), ...$params);
$stmt->execute();
$stmt->bind_result($conversation_id, $chat_id, $username);

$conversations = [];
while ($stmt->fetch()) {
    $conversations[] = ['conversation_id' => $conversation_id, 'chat_id' => $chat_id, 'username' => $username];
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'conversations' => $conversations]);
?>
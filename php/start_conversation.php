<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to start a conversation.']);
    exit();
}

$user1_id = $_SESSION['user_id'];
$user2_id = $_POST['user_id'];

// Prevent users from starting a conversation with themselves
if ($user1_id == $user2_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot start a conversation with yourself.']);
    exit();
}

// Generate a unique chat ID
$chat_id = md5(min($user1_id, $user2_id) . '-' . max($user1_id, $user2_id));

// Check if a conversation already exists
$sql = "SELECT conversation_id FROM conversations WHERE chat_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $chat_id);
$stmt->execute();
$stmt->bind_result($conversation_id);
$stmt->fetch();
$stmt->close();

if (!$conversation_id) {
    // Create a new conversation
    $sql = "INSERT INTO conversations (chat_id, user1_id, user2_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $chat_id, $user1_id, $user2_id);
    if ($stmt->execute()) {
        $conversation_id = $stmt->insert_id;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to start conversation.']);
        exit();
    }
    $stmt->close();
}

echo json_encode(['success' => true, 'chat_id' => $chat_id, 'conversation_id' => $conversation_id]);
$conn->close();
?>
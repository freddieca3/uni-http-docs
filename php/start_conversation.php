<?php
session_start();
include('../includes/db_connection.php');

$user1_id = $_SESSION['user_id'];
$user2_id = $_POST['user_id'];

// Check if a conversation already exists
$sql = "SELECT conversation_id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
$stmt->execute();
$stmt->bind_result($conversation_id);
$stmt->fetch();
$stmt->close();

if ($conversation_id) {
    echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
} else {
    // Create a new conversation
    $sql = "INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user1_id, $user2_id);
    if ($stmt->execute()) {
        $conversation_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to start conversation']);
    }
    $stmt->close();
}

$conn->close();
?>
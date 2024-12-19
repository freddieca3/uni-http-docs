<?php
session_start();
include('../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $conversation_id = $_POST['conversation_id'];
    $message_text = $_POST['message_text'];

    // Check if the recipient is blocked
    $sql = "SELECT c.user1_id, c.user2_id
            FROM conversations c
            LEFT JOIN blocks b ON (b.blocker_id = ? AND b.blocked_id = c.user1_id) OR (b.blocker_id = ? AND b.blocked_id = c.user2_id)
            WHERE c.conversation_id = ? AND b.blocker_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $sender_id, $sender_id, $conversation_id);
    $stmt->execute();
    $stmt->bind_result($user1_id, $user2_id);
    $stmt->fetch();
    $stmt->close();

    if (!$user1_id || !$user2_id) {
        echo json_encode(['success' => false, 'message' => 'Cannot send message to blocked user.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $conversation_id, $sender_id, $message_text);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error sending message.']);
    }
    $stmt->close();
}
$conn->close();
?>
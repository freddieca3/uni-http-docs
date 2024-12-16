<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to send messages.']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$conversation_id = $_POST['conversation_id'];
$message_text = $_POST['message_text'];

// Fetch the recipient's user ID from the conversation
$sql = "SELECT user1_id, user2_id FROM conversations WHERE conversation_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$stmt->bind_result($user1_id, $user2_id);
$stmt->fetch();
$stmt->close();

$recipient_id = ($user1_id == $sender_id) ? $user2_id : $user1_id;

// Check if the sender has blocked the recipient or vice versa
$sql = "SELECT COUNT(*) FROM blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $sender_id, $recipient_id, $recipient_id, $sender_id);
$stmt->execute();
$stmt->bind_result($block_count);
$stmt->fetch();
$stmt->close();

if ($block_count > 0) {
    echo json_encode(['success' => false, 'message' => 'You cannot send messages to this user.']);
    exit();
}

// Insert the message into the messages table
$sql = "INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $conversation_id, $sender_id, $message_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error sending message.']);
}
$stmt->close();
$conn->close();
?>
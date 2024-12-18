<?php
session_start();
include('../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $conversation_id = $_POST['conversation_id'];
    $message_text = $_POST['message_text'];

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
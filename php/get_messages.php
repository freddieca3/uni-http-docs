<?php
session_start();
include('../includes/db_connection.php');

$conversation_id = $_GET['conversation_id'];

$stmt = $conn->prepare("SELECT sender_id, message_text, timestamp FROM messages WHERE conversation_id = ? ORDER BY timestamp ASC");
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);

$stmt->close();
$conn->close();
?>
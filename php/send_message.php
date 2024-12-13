<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to send messages.']);
    exit();
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Get the message from the POST request
$message = trim($_POST['message']);
<<<<<<< HEAD

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
    exit();
}
=======
>>>>>>> parent of c0d6a43 (messages)

// Insert the message into the database
$sql = "INSERT INTO messages (sender, message, created_at) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
<<<<<<< HEAD
<<<<<<< HEAD
$stmt->bind_param("iss", $conversation_id, $username, $message);
=======
=======
>>>>>>> parent of c0d6a43 (messages)
$stmt->bind_param("ss", $username, $message);
$stmt->execute();
$stmt->close();
>>>>>>> parent of c0d6a43 (messages)

if ($stmt->execute()) {
    // Fetch the inserted message details
    $message_id = $stmt->insert_id;
    $sql = "SELECT sender, message, created_at FROM messages WHERE message_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->bind_result($sender, $message, $created_at);
    $stmt->fetch();
    echo json_encode(['success' => true, 'message' => ['sender' => $sender, 'message' => $message, 'created_at' => $created_at]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}

$stmt->close();
$conn->close();
?>
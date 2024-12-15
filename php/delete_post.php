<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Access denied.";
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// Check if the post belongs to the logged-in user
$sql = "SELECT user_id FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($post_user_id);
$stmt->fetch();
$stmt->close();

if ($post_user_id != $user_id) {
    echo "You can only delete your own posts.";
    exit();
}

// Delete the post from the database
$sql = "DELETE FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
if ($stmt->execute()) {
    echo "Post deleted successfully.";
} else {
    echo "Failed to delete post: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
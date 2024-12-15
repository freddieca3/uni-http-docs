<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to like posts.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// Check if the user has already liked the post
$sql = "SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already liked this post.']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Insert the like into the likes table
$sql = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);
if ($stmt->execute()) {
    // Increment the like count in the posts table
    $sql = "UPDATE posts SET likes = likes + 1 WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->close();

    // Get the updated like count
    $sql = "SELECT likes FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($like_count);
    $stmt->fetch();
    echo json_encode(['success' => true, 'like_count' => $like_count]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to like post: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
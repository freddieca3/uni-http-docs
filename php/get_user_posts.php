<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to see your posts.";
    exit();
}

// Get the logged-in user's user ID
$user_id = $_SESSION['user_id'];

// Include the get_posts.php file and call the fetchPosts function with the user_id
include('get_posts.php');
fetchPosts($user_id);
?>
<?php
// Start the session and include the database connection
session_start();
include('../includes/db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Fred's Free Speech</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <div class="sidebar">
            <h3>Sidebar</h3>
            <p>Links and other information</p>
        </div>
        <div class="feed">
            <h2>Feed</h2>
            <?php
            // Include the get_user_posts.php to fetch and display posts
            include('../php/get_user_posts.php');
            ?>
        </div>
        <div class="widgets">
            <h3>Widgets</h3>
            <p>Additional content or ads</p>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Fred's Free Speech Platform</p>
    </footer>
</body>
</html>
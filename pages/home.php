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
    <main class="container">
        <h2>Feed</h2>

        <?php
        // Include the get_posts.php to fetch and display all posts
        include('../php/get_posts.php');
        ?>
    </main>
    <footer>
        <p>&copy; 2024 Fred's Free Speech Platform</p>
    </footer>
</body>
</html>
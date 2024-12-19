<?php
session_start(); // Start the session to manage user login state

// Include database connection file
include 'includes/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: pages/login.html");
    exit();
}

// User is logged in; check user role from the database
$userId = $_SESSION['user_id'];


// Redirect based on user role
header("Location: pages/login.html");
exit();
?>
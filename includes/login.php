<?php
session_start();
include('../includes/db_connection.php');

$username = $_POST['username'];
$password = $_POST['password'];

// Validate user credentials
$sql = "SELECT user_id, username FROM users WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$stmt->bind_result($user_id, $username);
$stmt->fetch();

if ($user_id) {
    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    header("Location: login.html?success=true");
} else {
    header("Location: login.html?error=Invalid+username+or+password");
}

$stmt->close();
$conn->close();
?>
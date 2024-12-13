<?php
session_start();
include('../includes/db_connection.php');

$query = $_GET['query'];
$sql = "SELECT user_id, username FROM users WHERE username LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $query . "%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$stmt->bind_result($user_id, $username);

$results = [];
while ($stmt->fetch()) {
    $results[] = ['user_id' => $user_id, 'username' => $username];
}
$stmt->close();
$conn->close();

foreach ($results as $result) {
    echo "<div class='search-result'><a href='../pages/message_user.php?user_id=" . htmlspecialchars($result['user_id']) . "'>" . htmlspecialchars($result['username']) . "</a></div>";
}
?>
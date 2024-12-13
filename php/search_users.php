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

echo json_encode($results);
?>
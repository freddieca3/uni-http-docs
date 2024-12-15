<?php
// Start the session
session_start();

// Include the database connection file
include('../includes/db_connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the logged-in user's ID
    $reporter_id = $_SESSION['user_id'];
    $reported_username = trim($_POST['username']);
    $reason = trim($_POST['reason']);

    // Fetch the reported user's ID from the database
    $sql = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $reported_username);
    $stmt->execute();
    $stmt->bind_result($reported_user_id);
    $stmt->fetch();
    $stmt->close();

    if ($reported_user_id) {
        // Insert the report into the reports table
        $sql = "INSERT INTO reports (reporter_id, reported_user_id, reason) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $reporter_id, $reported_user_id, $reason);
        if ($stmt->execute()) {
            $message = "Report submitted successfully.";
        } else {
            $message = "Failed to submit report: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Reported user not found.";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - Social Media</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container">
        <h2>Report or Block User</h2>
        <?php if (isset($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST" action="report.php">
            <label for="username">Username to Report:</label>
            <input type="text" id="username" name="username" required>
            <label for="reason">Reason:</label>
            <textarea id="reason" name="reason" rows="4" required></textarea>
            <button type="submit">Submit Report</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
</body>
</html>
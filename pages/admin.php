<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Social Media</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main class="container">
        <h2>User Management</h2>
        <div id="user-list">
            <?php
            include('../includes/db_connection.php');

            // Fetch all users from the database
            $sql = "SELECT user_id, username, email FROM users";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<ul>";
                while ($row = $result->fetch_assoc()) {
                    echo "<li>User ID: " . htmlspecialchars($row['user_id']) . " - Username: " . htmlspecialchars($row['username']) . " - Email: " . htmlspecialchars($row['email']) . " ";
                    echo "<button onclick='removeUser(" . htmlspecialchars($row['user_id']) . ")'>Remove</button> ";
                    echo "<button onclick='changePassword(" . htmlspecialchars($row['user_id']) . ")'>Change Password</button></li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No users found.</p>";
            }

            $conn->close();
            ?>
        </div>
        <h2>Reports</h2>
        <div id="reports">
            <?php
            include('../includes/db_connection.php');

            // Fetch all reports from the database
            $sql = "SELECT r.report_id, r.reason, r.created_at, u1.username AS reporter_username, u2.username AS reported_username
                    FROM reports r
                    JOIN users u1 ON r.reporter_id = u1.user_id
                    JOIN users u2 ON r.reported_user_id = u2.user_id
                    ORDER BY r.created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<p><strong>Report ID:</strong> " . htmlspecialchars($row['report_id']) . "<br>";
                    echo "<strong>Reporter:</strong> " . htmlspecialchars($row['reporter_username']) . "<br>";
                    echo "<strong>Reported User:</strong> " . htmlspecialchars($row['reported_username']) . "<br>";
                    echo "<strong>Reason:</strong> " . htmlspecialchars($row['reason']) . "<br>";
                    echo "<strong>Reported on:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
                }
            } else {
                echo "<p>No reports available.</p>";
            }

            $conn->close();
            ?>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
    <script>
        // load reports dynamically on page load
        function loadReports() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "../php/get_reports.php", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("reports").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        // remove user with confirmation
        function removeUser(userId) {
            if (confirm("Are you sure you want to remove this user?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../php/delete_user.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert(xhr.responseText);
                        location.reload();
                    } else {
                        alert("Error: " + xhr.statusText);
                    }
                };
                xhr.onerror = function () {
                    alert("Request failed.");
                };
                xhr.send("user_id=" + userId);
            }
        }

        // prompt for new password and change it
        function changePassword(userId) {
            var newPassword = prompt("Enter the new password:");
            if (newPassword) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../php/change_password.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert(xhr.responseText);
                    } else {
                        alert("Error: " + xhr.statusText);
                    }
                };
                xhr.onerror = function () {
                    alert("Request failed.");
                };
                xhr.send("user_id=" + userId + "&new_password=" + encodeURIComponent(newPassword));
            }
        }

        // load reports when DOM is ready
        document.addEventListener("DOMContentLoaded", loadReports);
    </script>
</body>

</html>
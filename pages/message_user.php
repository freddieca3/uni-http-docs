<?php
// Start the session
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user1_id = $_SESSION['user_id'];
$user2_id = $_GET['user_id'];

// Prevent users from messaging themselves
if ($user1_id == $user2_id) {
    echo "You cannot message yourself.";
    exit();
}

// Check if a conversation already exists
$sql = "SELECT conversation_id FROM conversations WHERE 
        (user1_id = ? AND user2_id = ?) OR 
        (user1_id = ? AND user2_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
$stmt->execute();
$stmt->bind_result($conversation_id);
$stmt->fetch();
$stmt->close();

if (!$conversation_id) {
    // Create a new conversation
    $sql = "INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user1_id, $user2_id);
    if ($stmt->execute()) {
        $conversation_id = $stmt->insert_id;
    } else {
        echo "Failed to start conversation.";
        exit();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message User - Social Media</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container">
        <h2>Direct Messages</h2>
        <div class="chat-window" id="chat-window">
            <!-- Messages will be loaded here -->
        </div>
        <form id="message-form">
            <input type="text" id="message-input" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
    <script>
        function loadMessages(conversationId) {
            if (navigator.onLine) {
                // Fetch messages from the server
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "../php/get_messages.php?conversation_id=" + conversationId, true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            displayMessages(response.messages, conversationId);
                            // Cache messages locally
                            localStorage.setItem('conversation_' + conversationId, JSON.stringify(response.messages));
                        }
                    }
                };
                xhr.send();
            } else {
                // Load messages from local storage
                var messages = JSON.parse(localStorage.getItem('conversation_' + conversationId)) || [];
                displayMessages(messages, conversationId);
            }
            document.getElementById("message-form").style.display = "block";
            document.getElementById("chat-window").setAttribute("data-conversation-id", conversationId);
        }

        function displayMessages(messages, conversationId) {
            const chatWindow = document.getElementById("chat-window");
            chatWindow.innerHTML = messages.map(msg => `<p><strong>${msg.sender}:</strong> ${msg.message} <small>(${msg.created_at})</small></p>`).join('');
        }

        document.getElementById("message-form").addEventListener("submit", function (e) {
            e.preventDefault();
            const message = document.getElementById("message-input").value;
            const conversationId = document.getElementById("chat-window").getAttribute("data-conversation-id");
            const timestamp = new Date().toISOString();
            const newMessage = {
                sender: 'You',
                message: message,
                created_at: timestamp
            };

            // Display the new message immediately
            displayMessages([...JSON.parse(localStorage.getItem('conversation_' + conversationId)) || [], newMessage], conversationId);
            // Update local storage
            const messages = JSON.parse(localStorage.getItem('conversation_' + conversationId)) || [];
            messages.push(newMessage);
            localStorage.setItem('conversation_' + conversationId, JSON.stringify(messages));
            document.getElementById("message-input").value = "";

            if (navigator.onLine) {
                // Send the message to the server
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../php/send_message.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status !== 200) {
                        alert("Failed to send message: " + xhr.statusText);
                    }
                };
                xhr.send("message=" + encodeURIComponent(message) + "&conversation_id=" + conversationId);
            } else {
                // Queue the message to send later
                let pending = JSON.parse(localStorage.getItem('pending_messages')) || [];
                pending.push({ conversation_id: conversationId, message: message, created_at: timestamp });
                localStorage.setItem('pending_messages', JSON.stringify(pending));
                alert("You are offline. Your message will be sent when you're back online.");
            }
        });

        function sendPendingMessages() {
            let pending = JSON.parse(localStorage.getItem('pending_messages')) || [];
            pending.forEach(pendingMsg => {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../php/send_message.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Remove the message from pending
                            pending = pending.filter(msg => !(msg.conversation_id === pendingMsg.conversation_id && msg.message === pendingMsg.message));
                            localStorage.setItem('pending_messages', JSON.stringify(pending));
                            // Reload messages to include the sent message from the server
                            loadMessages(pendingMsg.conversation_id);
                        }
                    }
                };
                xhr.send("message=" + encodeURIComponent(pendingMsg.message) + "&conversation_id=" + pendingMsg.conversation_id);
            });
        }

        // Load messages when the page loads
        document.addEventListener("DOMContentLoaded", function() {
            const conversationId = <?php echo $conversation_id; ?>;
            loadMessages(conversationId);
            if (navigator.onLine) {
                sendPendingMessages();
            }
        });
    </script>
</body>
</html>
<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Social Media</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container">
        <h2>Direct Messages</h2>
        <div class="search-container">
            <input type="text" id="user-search-input" placeholder="Search for users...">
            <div id="search-results"></div>
        </div>
        <div class="chat-window" id="chat-window">
            <!-- Messages will be loaded here -->
        </div>
        <form id="message-form" style="display: none;">
            <input type="text" id="message-input" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
    <script>
        function loadMessages(conversationId) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "../php/get_messages.php?conversation_id=" + conversationId, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("chat-window").innerHTML = xhr.responseText;
                    document.getElementById("message-form").style.display = "block";
                    document.getElementById("chat-window").setAttribute("data-conversation-id", conversationId);
                }
            };
            xhr.send();
        }

        document.getElementById("message-form").addEventListener("submit", function (e) {
            e.preventDefault();
            var message = document.getElementById("message-input").value;
            var conversationId = document.getElementById("chat-window").getAttribute("data-conversation-id");
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../php/send_message.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        loadMessages(conversationId);
                        document.getElementById("message-input").value = "";
                    } else {
                        alert("Failed to send message: " + response.message);
                    }
                }
            };
            xhr.send("message=" + encodeURIComponent(message) + "&conversation_id=" + conversationId);
        });

        document.getElementById("user-search-input").addEventListener("input", function () {
            var query = this.value;
            if (query.length > 2) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "../php/search_users.php?query=" + encodeURIComponent(query), true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        document.getElementById("search-results").innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            } else {
                document.getElementById("search-results").innerHTML = "";
            }
        });

        function startConversation(userId) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../php/start_conversation.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        loadMessages(response.conversation_id);
                    } else {
                        alert("Failed to start conversation: " + response.message);
                    }
                } else {
                    alert("Failed to start conversation: Server error");
                }
            };
            xhr.onerror = function () {
                alert("Failed to start conversation: Network error");
            };
            xhr.send("user_id=" + userId);
        }
    </script>
</body>
</html>
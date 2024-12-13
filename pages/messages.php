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
            const messages = JSON.parse(localStorage.getItem('conversation_' + conversationId)) || [];
            const chatWindow = document.getElementById("chat-window");
            chatWindow.innerHTML = messages.map(msg => `<p><strong>${msg.sender}:</strong> ${msg.message} <small>(${msg.created_at})</small></p>`).join('');
            document.getElementById("message-form").style.display = "block";
            chatWindow.setAttribute("data-conversation-id", conversationId);
        }

        document.getElementById("message-form").addEventListener("submit", function (e) {
            e.preventDefault();
            const message = document.getElementById("message-input").value;
            const conversationId = document.getElementById("chat-window").getAttribute("data-conversation-id");
            const messages = JSON.parse(localStorage.getItem('conversation_' + conversationId)) || [];
            const newMessage = {
                sender: 'You',
                message: message,
                created_at: new Date().toISOString()
            };
            messages.push(newMessage);
            localStorage.setItem('conversation_' + conversationId, JSON.stringify(messages));
            loadMessages(conversationId);
            document.getElementById("message-input").value = "";

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
        });

        document.getElementById("user-search-input").addEventListener("input", function () {
            const query = this.value;
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
            const conversationId = 'conv_' + userId;
            loadMessages(conversationId);
        }
    </script>
</body>
</html>
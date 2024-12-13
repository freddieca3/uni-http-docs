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
            if (navigator.onLine) {
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
            } else {
                alert("You are offline. Cannot start a new conversation.");
            }
        }

        // Attempt to send any pending messages when the page loads
        window.onload = function() {
            if (navigator.onLine) {
                sendPendingMessages();
            }
        };
    </script>
</body>
</html>
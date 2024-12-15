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
        <div>
            <input type="text" id="user-search-input" placeholder="Search for users...">
            <div id="search-results"></div>
        </div>
        <div id="conversation-list">
            <!-- Conversations will be loaded here -->
        </div>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
    <script>
        function loadConversations() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "../php/get_conversations.php", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        displayConversations(response.conversations);
                    }
                }
            };
            xhr.send();
        }

        function displayConversations(conversations) {
            const conversationList = document.getElementById("conversation-list");
            conversationList.innerHTML = conversations.map(conv => `<div onclick="openConversation('${conv.chat_id}')">${conv.username}</div>`).join('');
        }

        function openConversation(chatId) {
            window.location.href = `/messages/${chatId}/index.php`;
        }

        document.getElementById("user-search-input").addEventListener("input", function () {
            const query = this.value;
            if (query.length > 2) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "../php/search_users.php?query=" + encodeURIComponent(query), true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        const results = JSON.parse(xhr.responseText);
                        document.getElementById("search-results").innerHTML = results.map(user => `<div onclick="startConversation(${user.user_id})">${user.username}</div>`).join('');
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
                            openConversation(response.chat_id);
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

        window.onload = function() {
            loadConversations();
        };
    </script>
</body>
</html>
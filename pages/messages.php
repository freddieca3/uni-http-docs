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
        <div id="chat-box">
            <!-- Messages will be appended here -->
        </div>
        <form id="message-form" style="display: none;">
            <input type="text" id="message-input" placeholder="Type a message" required>
            <button type="submit">Send</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
    <script>
        const senderId = <?php echo $_SESSION['user_id']; ?>; // Logged-in user's ID
        let conversationId = null; // Will be set when a user is selected

        // Fetch conversations
        function fetchConversations() {
            fetch('../php/get_conversations.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const conversationList = document.getElementById('conversation-list');
                        conversationList.innerHTML = '';
                        data.conversations.forEach(conv => {
                            const convDiv = document.createElement('div');
                            convDiv.textContent = conv.username;
                            convDiv.addEventListener('click', () => openConversation(conv.conversation_id));
                            conversationList.appendChild(convDiv);
                        });
                    }
                });
        }

        // Open a conversation
        function openConversation(convId) {
            conversationId = convId;
            document.getElementById('message-form').style.display = 'block';
            fetchMessages();
        }

        // Fetch messages
        function fetchMessages() {
            if (conversationId) {
                fetch(`../php/get_messages.php?conversation_id=${conversationId}`)
                    .then(response => response.json())
                    .then(data => {
                        const chatBox = document.getElementById('chat-box');
                        chatBox.innerHTML = '';
                        data.forEach(message => {
                            const messageDiv = document.createElement('div');
                            messageDiv.textContent = `${message.sender_id === senderId ? 'You' : 'Them'}: ${message.message_text}`;
                            chatBox.appendChild(messageDiv);
                        });
                    });
            }
        }

        // Send a message
        document.getElementById('message-form').addEventListener('submit', e => {
            e.preventDefault();
            const messageText = document.getElementById('message-input').value;

            fetch('../php/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}&message_text=${messageText}`
            }).then(response => response.json())
              .then(() => {
                  document.getElementById('message-input').value = '';
                  fetchMessages();
              });
        });

        // Search for users
        document.getElementById('user-search-input').addEventListener('input', function () {
            const query = this.value;
            if (query.length > 2) {
                fetch(`../php/search_users.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        const searchResults = document.getElementById('search-results');
                        searchResults.innerHTML = '';
                        data.forEach(user => {
                            const userDiv = document.createElement('div');
                            userDiv.textContent = user.username;
                            userDiv.addEventListener('click', () => startConversation(user.user_id, user.username));
                            searchResults.appendChild(userDiv);
                        });
                    });
            } else {
                document.getElementById('search-results').innerHTML = '';
            }
        });

        // Start a conversation
        function startConversation(userId, username) {
            fetch('../php/start_conversation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `user_id=${userId}`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      conversationId = data.conversation_id;
                      document.getElementById('message-form').style.display = 'block';
                      fetchMessages();
                      fetchConversations(); // Refresh the conversation list
                  } else {
                      alert(data.message);
                  }
              });
        }

        // Poll messages every 2 seconds
        setInterval(fetchMessages, 2000);

        // Load conversations on page load
        document.addEventListener('DOMContentLoaded', fetchConversations);
    </script>
</body>
</html>
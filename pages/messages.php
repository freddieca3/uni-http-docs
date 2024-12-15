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
        let receiverId = null; // Will be set when a user is selected

        // Fetch messages
        function fetchMessages() {
            if (receiverId) {
                fetch(`../php/get_messages.php?receiver_id=${receiverId}`)
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
                body: `receiver_id=${receiverId}&message_text=${messageText}`
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
            receiverId = userId;
            document.getElementById('message-form').style.display = 'block';
            fetchMessages();
        }

        // Poll messages every 2 seconds
        setInterval(fetchMessages, 2000);
    </script>
</body>
</html>
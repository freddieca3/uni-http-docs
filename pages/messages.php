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
        <div id="chat-box">
            <!-- Messages will be appended here -->
        </div>
        <form id="message-form">
            <input type="text" id="message-input" placeholder="Type a message" required>
            <button type="submit">Send</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
    <script>
        const senderId = <?php echo $_SESSION['user_id']; ?>; // Logged-in user's ID
        const receiverId = 2; // Replace with the receiver's ID

        // Fetch messages
        function fetchMessages() {
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

        // Poll messages every 2 seconds
        setInterval(fetchMessages, 2000);
    </script>
</body>
</html>
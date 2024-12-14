<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to start a conversation.']);
    exit();
}

$user1_id = $_SESSION['user_id'];
$user2_id = $_POST['user_id'];

// Prevent users from starting a conversation with themselves
if ($user1_id == $user2_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot start a conversation with yourself.']);
    exit();
}

// Generate a unique chat ID
$chat_id = md5(min($user1_id, $user2_id) . '-' . max($user1_id, $user2_id));

// Check if a conversation already exists
$sql = "SELECT conversation_id FROM conversations WHERE chat_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $chat_id);
$stmt->execute();
$stmt->bind_result($conversation_id);
$stmt->fetch();
$stmt->close();

if (!$conversation_id) {
    // Create a new conversation
    $sql = "INSERT INTO conversations (chat_id, user1_id, user2_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $chat_id, $user1_id, $user2_id);
    if ($stmt->execute()) {
        $conversation_id = $stmt->insert_id;

        // Create a new directory for the conversation
        $conversation_dir = "../messages/$chat_id";
        if (!file_exists($conversation_dir)) {
            mkdir($conversation_dir, 0777, true);
        }

        // Create an index.php file in the new directory
        $index_file = fopen("$conversation_dir/index.php", "w");
        $index_content = "<?php
session_start();
include('../../includes/db_connection.php');
\$chat_id = '$chat_id';
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Conversation</title>
    <link rel='stylesheet' href='/assets/css/style.css'>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <main class='container'>
        <h2>Conversation with <?php echo htmlspecialchars(\$_GET['username']); ?></h2>
        <div class='chat-window' id='chat-window'>
            <!-- Messages will be loaded here -->
        </div>
        <form id='message-form'>
            <input type='text' id='message-input' placeholder='Type your message...' required>
            <button type='submit'>Send</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Social Media Platform</p>
    </footer>
    <script>
        function loadMessages(chatId) {
            if (navigator.onLine) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '../../php/get_messages.php?chat_id=' + chatId, true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            displayMessages(response.messages, chatId);
                            localStorage.setItem('chat_' + chatId, JSON.stringify(response.messages));
                        }
                    }
                };
                xhr.send();
            } else {
                var messages = JSON.parse(localStorage.getItem('chat_' + chatId)) || [];
                displayMessages(messages, chatId);
            }
            document.getElementById('message-form').style.display = 'block';
            document.getElementById('chat-window').setAttribute('data-chat-id', chatId);
        }

        function displayMessages(messages, chatId) {
            const chatWindow = document.getElementById('chat-window');
            chatWindow.innerHTML = messages.map(msg => `<p><strong>\${msg.sender}:</strong> \${msg.message} <small>(\${msg.created_at})</small></p>`).join('');
        }

        document.getElementById('message-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const message = document.getElementById('message-input').value;
            const chatId = document.getElementById('chat-window').getAttribute('data-chat-id');
            const timestamp = new Date().toISOString();
            const newMessage = {
                sender: 'You',
                message: message,
                created_at: timestamp
            };

            displayMessages([...JSON.parse(localStorage.getItem('chat_' + chatId)) || [], newMessage], chatId);
            const messages = JSON.parse(localStorage.getItem('chat_' + chatId)) || [];
            messages.push(newMessage);
            localStorage.setItem('chat_' + chatId, JSON.stringify(messages));
            document.getElementById('message-input').value = '';

            if (navigator.onLine) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '../../php/send_message.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (xhr.status !== 200) {
                        alert('Failed to send message: ' + xhr.statusText);
                    }
                };
                xhr.send('message=' + encodeURIComponent(message) + '&chat_id=' + chatId);
            } else {
                let pending = JSON.parse(localStorage.getItem('pending_messages')) || [];
                pending.push({ chat_id: chatId, message: message, created_at: timestamp });
                localStorage.setItem('pending_messages', JSON.stringify(pending));
                alert('You are offline. Your message will be sent when you\'re back online.');
            }
        });

        function sendPendingMessages() {
            let pending = JSON.parse(localStorage.getItem('pending_messages')) || [];
            pending.forEach(pendingMsg => {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '../../php/send_message.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            pending = pending.filter(msg => !(msg.chat_id === pendingMsg.chat_id && msg.message === pendingMsg.message));
                            localStorage.setItem('pending_messages', JSON.stringify(pending));
                            loadMessages(pendingMsg.chat_id);
                        }
                    }
                };
                xhr.send('message=' + encodeURIComponent(pendingMsg.message) + '&chat_id=' + pendingMsg.chat_id);
            });
        }

        window.onload = function() {
            const chatId = '<?php echo $chat_id; ?>';
            loadMessages(chatId);
            if (navigator.onLine) {
                sendPendingMessages();
            }
        };
    </script>
</body>
</html>";
        fwrite($index_file, $index_content);
        fclose($index_file);

        echo json_encode(['success' => true, 'chat_id' => $chat_id, 'username' => $_POST['username']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to start conversation.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => true, 'chat_id' => $chat_id, 'username' => $_POST['username']]);
}

$conn->close();
?>
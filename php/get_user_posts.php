<?php
session_start();
include('../includes/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to see your posts.";
    exit();
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Fetch the user_id from the database
$sql = "SELECT user_id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Fetch posts made by this user
$sql = "SELECT post_id, title, description, image, location, created_at, likes, comments FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($post_id, $title, $description, $image, $location, $created_at, $likes, $comments_json);

if ($stmt->num_rows > 0) {
    while ($stmt->fetch()) {
        echo "<div class='post'>";
        echo "<p><strong>" . htmlspecialchars($title) . "</strong></p>";
        echo "<p>" . htmlspecialchars($description) . "</p>";
        if ($image) {
            echo "<img src='../uploads/" . htmlspecialchars($image) . "' alt='Post Image' style='width: 100px; height: 100px;'>";
        }
        if ($location) {
            echo "<p><strong>Location:</strong> " . htmlspecialchars($location) . "</p>";
        }
        echo "<p><small>Posted on: " . htmlspecialchars($created_at) . "</small></p>";

        // Like button
        echo "<button onclick='likePost(" . htmlspecialchars($post_id) . ")'>&#x2764;</button>";
        echo "<span id='like-count-" . htmlspecialchars($post_id) . "'>" . htmlspecialchars($likes) . "</span> Likes";

        // Comment section
        $comments = json_decode($comments_json, true);
        echo "<div class='comments' id='comments-" . htmlspecialchars($post_id) . "'>";
        if ($comments) {
            foreach (array_slice($comments, 0, 3) as $comment) {
                echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . " <small>(" . htmlspecialchars($comment['created_at']) . ")</small></p>";
            }
            if (count($comments) > 3) {
                echo "<button class='toggle-button' onclick='toggleComments(" . htmlspecialchars($post_id) . ")' id='toggle-button-" . htmlspecialchars($post_id) . "'>View More &#x25BC;</button>";
                echo "<div id='more-comments-" . htmlspecialchars($post_id) . "' style='display: none;'>";
                foreach (array_slice($comments, 3) as $comment) {
                    echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . " <small>(" . htmlspecialchars($comment['created_at']) . ")</small></p>";
                }
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<form onsubmit='return addComment(event, " . htmlspecialchars($post_id) . ")'>";
        echo "<input type='text' id='comment-input-" . htmlspecialchars($post_id) . "' placeholder='Add a comment...' required>";
        echo "<button type='submit'>Comment</button>";
        echo "</form>";

        echo "</div>";
    }
} else {
    echo "No posts available.";
}

$stmt->close();
$conn->close();
?>
<script>
function likePost(postId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../php/like_post.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                document.getElementById("like-count-" + postId).innerText = response.like_count;
            } else {
                alert("Failed to like post: " + response.message);
            }
        }
    };
    xhr.send("post_id=" + postId);
}

function addComment(event, postId) {
    event.preventDefault();
    var commentInput = document.getElementById("comment-input-" + postId);
    var comment = commentInput.value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../php/add_comment.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert("Comment posted");
                location.reload();
            } else {
                alert("Failed to add comment: " + response.message);
            }
        }
    };
    xhr.send("post_id=" + postId + "&comment=" + encodeURIComponent(comment));
}

function toggleComments(postId) {
    var moreComments = document.getElementById("more-comments-" + postId);
    var toggleButton = document.getElementById("toggle-button-" + postId);
    if (moreComments.style.display === "none") {
        moreComments.style.display = "block";
        toggleButton.innerHTML = "View Less &#x25B2;";
    } else {
        moreComments.style.display = "none";
        toggleButton.innerHTML = "View More &#x25BC;";
    }
}
</script>
<style>
.toggle-button {
    background: none;
    border: none;
    color: #005f99;
    cursor: pointer;
    padding: 0;
    font-size: 1em;
    text-decoration: underline;
}
.toggle-button:hover {
    color: #004080;
}
</style>
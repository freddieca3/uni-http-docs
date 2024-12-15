<?php
session_start();
include('../includes/db_connection.php');

function fetchPosts($user_id = null) {
    global $conn;

    // Base SQL query to fetch posts
    $sql = "SELECT p.post_id, p.title, p.description, p.image, p.location, p.created_at, p.likes, p.comments, u.username, u.profile_picture, p.user_id
            FROM posts p 
            JOIN users u ON p.user_id = u.user_id";

    // Add condition to filter by user_id if provided
    if ($user_id !== null) {
        $sql .= " WHERE p.user_id = ?";
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);

    if ($user_id !== null) {
        $stmt->bind_param("i", $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='post'>";
            echo "<div class='post-header'>";
            if ($row['profile_picture']) {
                echo "<img src='../uploads/" . htmlspecialchars($row['profile_picture']) . "' alt='Profile Picture' class='profile-picture'>";
            } else {
                echo "<img src='../assets/images/default-profile.png' alt='Default Profile Picture' class='profile-picture'>";
            }
            echo "<p><strong>" . htmlspecialchars($row['title']) . "</strong> by " . htmlspecialchars($row['username']) . "</p>";
            echo "</div>";
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
            if ($row['image']) {
                echo "<div class='image-container'>";
                echo "<img src='../uploads/" . htmlspecialchars($row['image']) . "' alt='Post Image'>";
                echo "</div>";
            }
            if ($row['location']) {
                echo "<p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
            }
            echo "<p><small>Posted on: " . htmlspecialchars($row['created_at']) . "</small></p>";

            // Like button
            echo "<button onclick='likePost(" . htmlspecialchars($row['post_id']) . ")'>&#x2764;</button>";
            echo "<span id='like-count-" . htmlspecialchars($row['post_id']) . "'>" . htmlspecialchars($row['likes']) . "</span> Likes";

            // Delete button for the logged-in user's posts
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
                echo "<button class='delete-button' onclick='deletePost(" . htmlspecialchars($row['post_id']) . ")'>Delete</button>";
            }

            // Comment section
            $comments = json_decode($row['comments'], true);
            echo "<div class='comments' id='comments-" . htmlspecialchars($row['post_id']) . "'>";
            if ($comments) {
                foreach (array_slice($comments, 0, 3) as $comment) {
                    echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . " <small>(" . htmlspecialchars($comment['created_at']) . ")</small></p>";
                }
                if (count($comments) > 3) {
                    echo "<button class='toggle-button' onclick='toggleComments(" . htmlspecialchars($row['post_id']) . ")' id='toggle-button-" . htmlspecialchars($row['post_id']) . "'>View More &#x25BC;</button>";
                    echo "<div id='more-comments-" . htmlspecialchars($row['post_id']) . "' style='display: none;'>";
                    foreach (array_slice($comments, 3) as $comment) {
                        echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . " <small>(" . htmlspecialchars($comment['created_at']) . ")</small></p>";
                    }
                    echo "</div>";
                }
            }
            echo "</div>";
            echo "<form onsubmit='return addComment(event, " . htmlspecialchars($row['post_id']) . ")'>";
            echo "<input type='text' id='comment-input-" . htmlspecialchars($row['post_id']) . "' placeholder='Add a comment...' required>";
            echo "<button type='submit'>Comment</button>";
            echo "</form>";

            echo "</div>";
        }
    } else {
        echo "No posts available.";
    }

    $stmt->close();
}

if (isset($_GET['user_id'])) {
    fetchPosts($_GET['user_id']);
} else {
    fetchPosts();
}
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

function deletePost(postId) {
    if (confirm("Are you sure you want to delete this post?")) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../php/delete_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert("Post deleted successfully.");
                location.reload();
            } else {
                alert("Failed to delete post: " + xhr.statusText);
            }
        };
        xhr.send("post_id=" + postId);
    }
}
</script>
<style>
.post-header {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.post-header img {
    flex-shrink: 0;
}

.post-header p {
    margin: 0;
    flex-grow: 1;
}

.delete-button {
    background-color: red;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 4px;
    margin-left: 10px;
}

.delete-button:hover {
    background-color: darkred;
}
</style>
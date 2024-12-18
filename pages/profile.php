<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Fred's Free Speech</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        #profile-edit-form, #new-post-form {
            display: none;
        }
        #edit-profile-btn, #create-post-btn {
            background-color: navy;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
        #edit-profile-btn:hover, #create-post-btn:hover {
            background-color: darkblue;
        }
    </style> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCZlCp0Zt62EittcZsPueFGo-QRwRDQBcE&libraries=places" async defer></script>
    <script src="../assets/js/interactive-map.js"></script>
    <script>
        function toggleProfileEditForm() {
            var form = document.getElementById("profile-edit-form");
            if (form.style.display === "none" || form.style.display === "") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }

        function toggleNewPostForm() {
            var form = document.getElementById("new-post-form");
            if (form.style.display === "none" || form.style.display === "") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }

        function loadUserPosts() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "../php/get_posts.php?user_id=<?php echo $user_id; ?>", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("user-posts").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        let cropper;

        function previewImage(event) {
            const imagePreviewContainer = document.getElementById('image-preview-container');
            const imagePreview = document.getElementById('image-preview');
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.style.display = 'block';

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(imagePreview, {
                    aspectRatio: 1, // Adjust aspect ratio as needed
                    viewMode: 1,
                    autoCropArea: 1,
                });
            };

            reader.readAsDataURL(file);
        }

        function submitPostForm(event) {
            event.preventDefault();
            const form = document.getElementById('new-post-form');
            const formData = new FormData(form);

            if (cropper) {
                cropper.getCroppedCanvas().toBlob((blob) => {
                    formData.append('croppedImage', blob, 'croppedImage.png');
                    sendFormData(formData);
                });
            } else {
                sendFormData(formData);
            }
        }

        function sendFormData(formData) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../php/post_processes.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Post created successfully!');
                        document.getElementById('new-post-form').reset();
                        document.getElementById('image-preview-container').style.display = 'none';
                        loadUserPosts();
                    } else {
                        alert('Failed to create post: ' + response.message);
                    }
                }
            };
            xhr.send(formData);
        }

        function deletePost(postId) {
            if (confirm("Are you sure you want to delete this post?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../php/delete_post.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert("Post deleted successfully.");
                        loadUserPosts();
                    } else {
                        alert("Failed to delete post: " + xhr.statusText);
                    }
                };
                xhr.send("post_id=" + postId);
            }
        }

        // Load posts when page loads
        document.addEventListener("DOMContentLoaded", function() {
            loadUserPosts();
        });

        // Display alert if profile was updated
        <?php if (isset($_SESSION['profile_updated']) && $_SESSION['profile_updated'] === true): ?>
            alert("Profile updated successfully!");
            <?php unset($_SESSION['profile_updated']); ?>
        <?php endif; ?>
    </script>
</head>
<body>
<?php include '../includes/header.php'; ?>
    <main class="container">
        <h2>Profile</h2>
        <div class="profile-info">
            <?php if (!empty($profile_picture)) : ?>
                <img src="../uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 100px; height: 100px;">
            <?php else : ?>
                <img src="../assets/images/default-profile.png" alt="Default Profile Picture" style="width: 100px; height: 100px;">
            <?php endif; ?>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Bio:</strong> <?php echo htmlspecialchars($bio); ?></p>
        </div>
        <button id="edit-profile-btn" onclick="toggleProfileEditForm()">Edit Profile</button>
        <form id="profile-edit-form" method="POST" action="profile.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            <label for="bio">Bio:</label>
            <textarea id="bio" name="bio" rows="4" cols="50"><?php echo htmlspecialchars($bio); ?></textarea>
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" accept=".jpg, .jpeg, .png">
            <button type="submit">Update Profile</button>
        </form>

        <h2>Your Posts</h2>
        <button id="create-post-btn" onclick="toggleNewPostForm()">Create New Post</button>
        <form id="new-post-form" method="POST" action="../php/post_processes.php" enctype="multipart/form-data" onsubmit="return submitPostForm(event)">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" cols="50" required></textarea>
            <label for="image">Upload Image:</label>
            <input type="file" id="image" name="image" accept=".jpg, .jpeg, .png" onchange="previewImage(event)">
            <div id="image-preview-container" style="display: none;">
                <img id="image-preview" style="max-width: 100%;">
            </div>
            <label for="locationSearch">Search Location:</label>
            <input type="text" class="form-control pac-target-input" id="locationSearch" placeholder="Search location" autocomplete="off">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" readonly>
            <button type="submit">Create Post</button>
        </form>
        <div id="user-posts">
            <?php
            // Include the get_posts.php file and call the fetchPosts function with the logged-in user's user ID
            include('../php/get_posts.php');
            fetchPosts($user_id);
            ?>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 Fred's Free Speech Platform</p>
    </footer>
</body>
</html>
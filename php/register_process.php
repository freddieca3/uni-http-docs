<?php
// Include the database connection file
include('../includes/db_connection.php');

// Function to validate the password
function validatePassword($password) {
    if (strlen($password) < 12) {
        return "Password must be at least 12 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "Password must contain at least one special character.";
    }
    return true;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect the form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate the password
    $passwordValidationResult = validatePassword($password);
    if ($passwordValidationResult !== true) {
        echo "Error: " . $passwordValidationResult;
        exit();
    }

    // Hash the password using SHA-512
    $hashed_password = hash('sha512', $password);

    // Prepare the SQL statement
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters and execute the statement
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: ../pages/login.html?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect to the registration page if the request method is not POST
    header("Location: ../pages/register.html");
    exit();
}
?>
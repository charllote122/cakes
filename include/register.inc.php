<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $username = trim($_POST['text']); // Changed from 'Username' to 'text'
    $pwd = trim($_POST['pwd']);
    $email = trim($_POST['email']);
    $pwd2 = trim($_POST['pwd2']);

    // Basic validation
    if (empty($username) || empty($email) || empty($pwd) || empty($pwd2)) {
        die("All fields are required.");
    }

    if ($pwd !== $pwd2) {
        die("Passwords do not match.");
    }

    // Hash the password for security
    $hashedPassword = password_hash($pwd, PASSWORD_DEFAULT);

    try {
        require_once 'model.php';
        
        // Check if the email already exists
        $checkEmailQuery = "SELECT COUNT(*) FROM users WHERE email = :email";
        $checkStat = $pdo->prepare($checkEmailQuery);
        $checkStat->bindParam(":email", $email);
        $checkStat->execute();
        $emailExists = $checkStat->fetchColumn() > 0;

        if ($emailExists) {
            die("Email is already registered. Please use a different email.");
        }

        // Insert user into the database using 'pwd' instead of 'password'
        $query = "INSERT INTO users (username, email, pwd) VALUES (:username, :email, :pwd)";
        $stat = $pdo->prepare($query);
        $stat->bindParam(":username", $username);
        $stat->bindParam(":email", $email);
        $stat->bindParam(":pwd", $hashedPassword); // Use hashed password
        $stat->execute();

        // Clean up
        $pdo = null;
        $stat = null;
        echo "User registered successfully.";
        header("Location: ../index.php?register=success");
        exit(); // Exit to prevent further execution
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Handle the case when the form is not submitted correctly
    die("Invalid request.");
}

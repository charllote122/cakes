<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (isset($_SESSION['user_id'])) {
    header("Location: ../pay/payment.php"); 
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $email = trim($_POST['email']);
    $pwd = trim($_POST['pwd']);
    if (empty($email) || empty($pwd)) {
        die("All fields are required.");
    }
    try {
        require_once 'model.php'; 
        $checkEmailQuery = "SELECT * FROM users WHERE email = :email";
        $checkStat = $pdo->prepare($checkEmailQuery);
        $checkStat->bindParam(":email", $email);
        $checkStat->execute();
        $user = $checkStat->fetch(PDO::FETCH_ASSOC); 
        if ($user) {
            if ($pwd === $user['pwd']) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: ../pay/checkout.php");
                exit(); // Prevent further execution
            } else {
                echo "Invalid password. Entered: $pwd, Stored: " . $user['pwd']; // Debugging output
                die("Invalid password.");
            }
        } else {
            die("Email not found.");
        }

        // Clean up
        $checkStat = null;
        $pdo = null;
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Handle the case when the form is not submitted correctly
    die("Invalid request.");
}
?>

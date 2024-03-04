<?php
session_start();

// Database connection
$servername = "216.246.47.8";
$port = "3306";
$username = "usxchzyi_p0sadp";
$password = "8llOdjCx6O6C";
$database = "usxchzyi_posadp";

$conn = new mysqli($servername . ':' . $port, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get username and password from form
$username = $_POST['username'];
$password = $_POST['password'];

// SQL query to check login credentials
$sql = "SELECT * FROM Users WHERE username='$username' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User found
    $user_row = $result->fetch_assoc();
    $user_id = $user_row['user_id'];

    // Set user_id in session
    $_SESSION['user_id'] = $user_id;

    // Redirect to declare cash page
    $_SESSION['username'] = $username;
    header("Location: declare_cash.php");
} else {
    // Invalid credentials, redirect back to login page
    header("Location: login.php");
}

$conn->close();
?>

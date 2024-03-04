<?php
session_start();

// Database connection
$servername = "216.246.47.8";
$port = "3306";
$username = "usxchzyi_p0sadp";
$password = "8llOdjCx6O6C";
$database = "usxchzyi_posadp";

$conn = new mysqli($servername . ':' . $port, $username, $password, $database);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get initial cash amount from form
    $initial_cash_amount = $_POST['initial_cash_amount'];
    
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Insert cash entry for the logged-in user
    $insert_cash_sql = "INSERT INTO Cash_Entries (user_id, date, cash_amount) VALUES ('$user_id', CURRENT_DATE(), '$initial_cash_amount')";
    if ($conn->query($insert_cash_sql) === TRUE) {
        // Cash entry inserted successfully, redirect to products page
        header("Location: products.php");
        exit;
    } else {
        echo "Error inserting cash entry: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declare Initial Cash</title>
</head>
<body>
    <h2>Declare Initial Cash Amount</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="initial_cash_amount">Initial Cash Amount:</label>
        <input type="number" id="initial_cash_amount" name="initial_cash_amount" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>

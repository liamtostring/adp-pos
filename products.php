<?php
session_start(); // Start the session

// Check if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page
    exit(); // Terminate script
}

// Check if the cart session variable is not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array(); // Initialize cart as an empty array
}

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

// Handle form submission for adding products to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Update cart session variable
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

// Initialize variables for pagination
$itemsPerPage = 50;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Initialize variable for search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch products from database with pagination and search
$sql = "SELECT * FROM Products";

// Add search query if provided
if (!empty($search)) {
    $sql .= " WHERE name LIKE '%$search%'";
}

// Add pagination
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
        <a href="logout.php">Logout</a>
    </div>
    <div class="wrapper">
        <div class="container">
            <div class="product-list">
                <h2>Products List</h2>
                <div class="search-box">
                    <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <input type="text" name="search" placeholder="Search products" value="<?php echo $search; ?>">
                        <button type="submit">Search</button>
                    </form>
                </div>
                <table>
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>".$row['product_id']."</td>";
                            echo "<td>".$row['name']."</td>";
                            echo "<td>".$row['price']."</td>";
                            echo "<td>".$row['category_id']."</td>";
                            echo "<td>";
                            echo "<form class='add-to-cart-form' method='post'>";
                            echo "<input type='hidden' name='product_id' value='".$row['product_id']."'>";
                            echo "<input type='number' name='quantity' value='1' min='1'>";
                            echo "<button type='submit'>Add to Cart</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No products found</td></tr>";
                    }
                    ?>
                </table>
                <?php
                // Pagination links
                $sql = "SELECT COUNT(*) AS total FROM Products";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $totalItems = $row['total'];
                $totalPages = ceil($totalItems / $itemsPerPage);

                echo "<div class='pagination'>";
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo "<a href='products.php?page=$i&search=$search'" . ($i == $page ? " class='active'" : "") . ">$i</a>";
                }
                echo "</div>";
                ?>
            </div>
            <div class="cart">
                <?php include('cart.php'); ?>
            </div>
        </div>
    </div>
</body>
</html>

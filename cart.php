<?php
// Check if a session is already active
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session if it's not already started
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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle updating quantity
    if (isset($_POST['productId']) && isset($_POST['quantity'])) {
        $productId = $_POST['productId'];
        $quantity = $_POST['quantity'];
        if ($quantity > 0) {
            $_SESSION['cart'][$productId] = $quantity;
        } else {
            unset($_SESSION['cart'][$productId]); // Remove product from cart if quantity is zero or less
        }
        // Output updated cart
        outputCart();
        exit; // Ensure no further output is sent after processing
    }

    // Handle removing product from cart
    if (isset($_POST['removeProductId'])) {
        $removeProductId = $_POST['removeProductId'];
        unset($_SESSION['cart'][$removeProductId]); // Remove product from cart
        // Output updated cart
        outputCart();
        exit; // Ensure no further output is sent after processing
    }

    // Handle emptying cart
    if (isset($_POST['emptyCart'])) {
        unset($_SESSION['cart']); // Empty the cart
        // Output updated cart
        outputCart();
        exit; // Ensure no further output is sent after processing
    }

    // Handle customer search
    if (isset($_POST['customerSearch'])) {
        $searchTerm = $_POST['customerSearch'];
        $sql = "SELECT name FROM Customers WHERE name LIKE '%$searchTerm%'";
        $result = $conn->query($sql);
        $customers = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row['name'];
            }
        }
        echo json_encode($customers);
        exit;
    }

    // Handle customer selection and retrieve customer information
    if (isset($_POST['selectedCustomer'])) {
        $customerName = $_POST['selectedCustomer'];
        $sql = "SELECT * FROM Customers WHERE name='$customerName'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $customerInfo = $result->fetch_assoc();
            echo json_encode($customerInfo);
            exit;
        }
    }
}

/// Function to output the cart HTML
function outputCart($initialLoad = true) {
    // Check if the cart is empty
    $cartEmpty = empty($_SESSION['cart']);

    // Output the "Shopping Cart" title
    if ($initialLoad) {
        echo "<h2>Shopping Cart</h2>";
    }

    if (!$cartEmpty) {
        echo "<div id='cart-content'>";
        echo "<table>";
        echo "<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr>";
        $totalPrice = 0;
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            // Fetch product details from database
            $sql = "SELECT * FROM Products WHERE product_id='$productId'";
            $result = $GLOBALS['conn']->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $productName = $row['name'];
                $price = $row['price'];
                $total = $price * $quantity;
                $totalPrice += $total;
                echo "<tr>";
                echo "<td>$productName</td>";
                echo "<td>$price</td>";
                echo "<td><input type='number' class='quantity-input' value='$quantity' min='1'></td>";
                echo "<td class='total'>$total</td>";
                echo "<td><button class='update-btn' data-product-id='$productId'>Update</button>";
                echo "<button class='remove-btn' data-product-id='$productId'>Remove</button></td>";
                echo "</tr>";
            }
        }
        echo "<tr><td colspan='3'><b>Total:</b></td><td><b id='total-price'>$totalPrice</b></td><td></td></tr>";

        // Fetch existing customers from the Customers table
        echo "<tr><td colspan='5'>";
        echo "<label for='customer'>Select Customer:</label>";
        echo "<input type='text' id='customer' name='customer' placeholder='Type to search'>";
        echo "<div id='customer-suggestions'></div>";
        echo "</td></tr>";

        // Form fields for customer information
        echo "<tr><td colspan='5'>";
        echo "<label for='name'>Name:</label>";
        echo "<input type='text' id='name' name='name' readonly><br>";
        echo "<label for='email'>Email:</label>";
        echo "<input type='email' id='email' name='email' readonly><br>";
        echo "<label for='phone'>Phone:</label>";
        echo "<input type='text' id='phone' name='phone' readonly><br>";
        echo "<label for='address'>Address:</label>";
        echo "<input type='text' id='address' name='address' readonly><br>";

        // Add additional fields from the Customers table
        echo "<label for='rfc'>RFC:</label>";
        echo "<input type='text' id='rfc' name='rfc' readonly><br>";
        echo "<label for='razon_social'>Razon Social:</label>";
        echo "<input type='text' id='razon_social' name='razon_social' readonly><br>";
        echo "<label for='calle'>Calle:</label>";
        echo "<input type='text' id='calle' name='calle' readonly><br>";
        echo "<label for='numero_exterior'>Numero Exterior:</label>";
        echo "<input type='text' id='numero_exterior' name='numero_exterior' readonly><br>";
        echo "<label for='numero_interior'>Numero Interior:</label>";
        echo "<input type='text' id='numero_interior' name='numero_interior' readonly><br>";
        echo "<label for='colonia'>Colonia:</label>";
        echo "<input type='text' id='colonia' name='colonia' readonly><br>";
        echo "<label for='codigo_postal'>Codigo Postal:</label>";
        echo "<input type='text' id='codigo_postal' name='codigo_postal' readonly><br>";
        echo "<label for='municipio'>Municipio:</label>";
        echo "<input type='text' id='municipio' name='municipio' readonly><br>";
        echo "<label for='estado'>Estado:</label>";
        echo "<input type='text' id='estado' name='estado' readonly><br>";
        echo "<label for='uso_de_cfdi'>Uso de CFDI:</label>";
        echo "<input type='text' id='uso_de_cfdi' name='uso_de_cfdi' readonly><br>";
        echo "<label for='regimen_fiscal'>Regimen Fiscal:</label>";
        echo "<input type='text' id='regimen_fiscal' name='regimen_fiscal' readonly><br>";
        echo "</td></tr>";

        echo "</table>";
        echo "<button id='empty-cart-btn'>Empty Cart</button>";
        echo "</div>";
    } else {
        echo "<p>Your cart is empty.</p>";
    }
}

// Output cart initially with the title
outputCart();

?>
<script>
    // Function to attach event listeners to update, remove, and empty buttons
    function attachEventListeners() {
        // AJAX for updating quantity
        document.querySelectorAll('.update-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const quantity = this.parentElement.parentElement.querySelector('.quantity-input').value;
                updateCart(productId, quantity);
            });
        });

        // AJAX for removing product
        document.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                removeProduct(productId);
            });
        });

        // AJAX for emptying cart
        document.getElementById('empty-cart-btn').addEventListener('click', function() {
            emptyCart();
        });

        // Event listener for customer search
        const customerInput = document.getElementById('customer');
        customerInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm === '') {
                document.getElementById('customer-suggestions').innerHTML = '';
                return;
            }
            fetchCustomerSuggestions(searchTerm);
        });

        // Event listener for customer selection
        const customerSuggestions = document.getElementById('customer-suggestions');
        customerSuggestions.addEventListener('click', function(event) {
            if (event.target.tagName === 'DIV') {
                const selectedCustomer = event.target.textContent;
                document.getElementById('customer').value = selectedCustomer;
                fetchCustomerInformation(selectedCustomer);
            }
        });
    }

// Function to fetch customer suggestions
function fetchCustomerSuggestions(searchTerm) {
    const formData = new FormData();
    formData.append('customerSearch', searchTerm);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        displayCustomerSuggestions(data);
    })
    .catch(error => {
        console.error('Error fetching customer suggestions:', error);
        // Handle the error here, e.g., display a message to the user
    });
}


    // Function to display customer suggestions
    function displayCustomerSuggestions(customers) {
        const suggestionsDiv = document.getElementById('customer-suggestions');
        suggestionsDiv.innerHTML = '';
        customers.forEach(customer => {
            const suggestion = document.createElement('div');
            suggestion.textContent = customer;
            suggestionsDiv.appendChild(suggestion);
        });
    }

// Function to fetch customer information
function fetchCustomerInformation(customerName) {
    const formData = new FormData();
    formData.append('selectedCustomer', customerName);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text(); // Read the response as text
    })
    .then(data => {
        if (data.trim() !== '') { // Check if response is not empty
            try {
                const jsonData = JSON.parse(data); // Try parsing as JSON
                console.log('Received data:', jsonData);
                if (jsonData && Object.keys(jsonData).length > 0) {
                    populateCustomerInformation(jsonData);
                } else {
                    throw new Error('Empty or invalid JSON data received');
                }
            } catch (error) {
                throw new Error('Error parsing JSON data: ' + error.message);
            }
        } else {
            throw new Error('Empty response received');
        }
    })
    .catch(error => {
        console.error('Error fetching customer information:', error);
        // Handle the error here, e.g., display a message to the user
    });
}


    // Function to populate customer information in form fields
    function populateCustomerInformation(customerInfo) {
        document.getElementById('name').value = customerInfo.name;
        document.getElementById('email').value = customerInfo.email;
        document.getElementById('phone').value = customerInfo.phone;
        document.getElementById('address').value = customerInfo.address;

        // Populate additional fields
        document.getElementById('rfc').value = customerInfo.rfc;
        document.getElementById('razon_social').value = customerInfo.razon_social;
        document.getElementById('calle').value = customerInfo.calle;
        document.getElementById('numero_exterior').value = customerInfo.numero_exterior;
        document.getElementById('numero_interior').value = customerInfo.numero_interior;
        document.getElementById('colonia').value = customerInfo.colonia;
        document.getElementById('codigo_postal').value = customerInfo.codigo_postal;
        document.getElementById('municipio').value = customerInfo.municipio;
        document.getElementById('estado').value = customerInfo.estado;
        document.getElementById('uso_de_cfdi').value = customerInfo.uso_de_cfdi;
        document.getElementById('regimen_fiscal').value = customerInfo.regimen_fiscal;
    }

    // Function to update the cart content
    function updateCartContent(data) {
        document.getElementById('cart-content').innerHTML = data;
        // Reattach event listeners
        attachEventListeners();
    }

    // Function to update cart quantity
    function updateCart(productId, quantity) {
        const formData = new FormData();
        formData.append('productId', productId);
        formData.append('quantity', quantity);

        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            updateCartContent(data);
        });
    }

    // Function to remove product from cart
    function removeProduct(productId) {
        const formData = new FormData();
        formData.append('removeProductId', productId);

        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            updateCartContent(data);
        });
    }

    // Function to empty the cart
    function emptyCart() {
        const formData = new FormData();
        formData.append('emptyCart', '1');

        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            updateCartContent(data);
        });
    }

    // Attach initial event listeners
    attachEventListeners();
</script>

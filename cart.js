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
}

// Function to attach event listeners to customer suggestions
function attachCustomerSuggestionListeners() {
    const suggestions = document.querySelectorAll('#customer-suggestions div');
    suggestions.forEach(suggestion => {
        suggestion.addEventListener('click', function() {
            const selectedCustomer = this.textContent;
            document.getElementById('customer').value = selectedCustomer;
            // Clear suggestions
            document.getElementById('customer-suggestions').innerHTML = '';
            // Send selected customer to server
            selectCustomer(selectedCustomer);
        });
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
    .then(response => response.json())
    .then(data => {
        displayCustomerSuggestions(data);
        // Attach event listeners to suggestions
        attachCustomerSuggestionListeners();
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

// Function to send selected customer to server
function selectCustomer(selectedCustomer) {
    const formData = new FormData();
    formData.append('selectedCustomer', selectedCustomer);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Update cart content with selected customer's information
        document.getElementById('cart-content').innerHTML = data;
    });
}

// Attach initial event listeners
attachEventListeners();

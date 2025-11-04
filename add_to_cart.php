<?php
// Start the session to access cart data
session_start();

// Include the database connection
require_once 'includes/db.php';

// 1. Check if a product ID is provided via the URL
if (isset($_GET['id'])) {
    
    // 2. Sanitize the product ID
    // Casting to (int) is a simple and effective way to sanitize
    // a number, preventing SQL injection.
    $product_id = (int)$_GET['id'];
    
    try {
        // 3. Check if the product actually exists and is in stock
        $sql = "SELECT * FROM products WHERE id = ? AND stock > 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // 4. Product exists. Initialize the cart if it's not already.
            // $_SESSION['cart'] will be an associative array.
            // The key will be the product_id, and the value will be the quantity.
            // e.g., [ 1 => 2, 5 => 1 ] (2 of product 1, 1 of product 5)
            
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // 5. Add the item to the cart or update quantity
            if (isset($_SESSION['cart'][$product_id])) {
                // Product is already in the cart, increment quantity
                $_SESSION['cart'][$product_id]++; 
            } else {
                // Product is not in the cart, add it with quantity 1
                $_SESSION['cart'][$product_id] = 1;
            }

            // 6. Set a success message to show on the cart page
            $_SESSION['success_message'] = $product['name'] . ' has been added to your cart!';

        } else {
            // Product doesn't exist or is out of stock
            $_SESSION['error_message'] = 'Product not found or out of stock.';
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    // No product ID was provided
    $_SESSION['error_message'] = 'No product specified.';
}

// 7. Redirect the user back to the cart page
header('Location: cart.php');
exit;
?>
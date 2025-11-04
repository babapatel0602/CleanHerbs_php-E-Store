<?php
// Start the session to access the cart
session_start();

// Check if the cart session exists
if (isset($_SESSION['cart'])) {

    // --- Logic for REMOVING an item ---
    // This is triggered by the "Remove" link, which is a GET request
    if (isset($_GET['remove'])) {
        $product_id = (int)$_GET['remove'];
        
        // Check if the product is in the cart
        if (isset($_SESSION['cart'][$product_id])) {
            // Unset (remove) it from the session array
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['success_message'] = 'Product removed from cart.';
        }
    }

    // --- Logic for UPDATING an item quantity ---
    // This is triggered by the "Update" form, which is a POST request
    if (isset($_POST['update'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        // Check if product is in cart
        if (isset($_SESSION['cart'][$product_id])) {
            
            if ($quantity > 0) {
                // Update the quantity in the session
                $_SESSION['cart'][$product_id] = $quantity;
                $_SESSION['success_message'] = 'Cart updated.';
            } else {
                // If quantity is 0 or less, remove the item
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['success_message'] = 'Product removed from cart.';
            }
        }
    }
}

// Redirect back to the cart page to show the changes
header('Location: cart.php');
exit;
?>
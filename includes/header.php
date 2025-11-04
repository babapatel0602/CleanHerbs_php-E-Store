<?php
// We will start the user session on every page
// This is needed to track the shopping cart and login status
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean Herbs - Your Ayurvedic Shop</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
    <header>
        <div class="container">
            <h1 class="logo"><a href="index.php">Clean Herbs</a></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li>
    <a href="cart.php">
        Cart 
        <?php 
        // Calculate total number of unique items in cart
        $cart_item_count = 0;
        if (isset($_SESSION['cart'])) {
            $cart_item_count = count($_SESSION['cart']);
        }
        
        // Display count if it's more than 0
        if ($cart_item_count > 0) {
            echo '<span class="cart-count">' . $cart_item_count . '</span>';
        }
        ?>
    </a>
</li>
                    <?php 
                    // Simple logic to show "Login/Register" or "Logout"
                    if (isset($_SESSION['user_id'])) {
                        // User is logged in
                        echo '<li><a href="logout.php">Logout</a></li>';
                        // Check if the user is an admin
                        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                            echo '<li><a href="admin/index.php">Admin Panel</a></li>';
                        }
                    } else {
                        // User is a guest
                        echo '<li><a href="login.php">Login</a></li>';
                        echo '<li><a href="register.php">Register</a></li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </header>

</body>
</html>
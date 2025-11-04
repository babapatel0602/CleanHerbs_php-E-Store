<?php
// Include header (which starts the session)
require_once 'includes/header.php';
// Include database connection
require_once 'includes/db.php';

// Initialize variables
$cart_items = [];
$total_price = 0;

// Check if the cart session variable exists and is not empty
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    
    // Get the product IDs from the cart session
    // The keys of the $_SESSION['cart'] array are the product IDs
    $product_ids = array_keys($_SESSION['cart']);
    
    // Create a string of '?,?,?' - one for each product ID.
    // This is for our prepared statement's "IN" clause.
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    try {
        // Fetch product details for ALL items in the cart in ONE query.
        // This is much more efficient than looping and doing one query per item.
        $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        
        // Execute the query, passing the array of product IDs
        $stmt->execute($product_ids);
        $products_in_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build a final cart items array with all details
        foreach ($products_in_db as $product) {
            $product_id = $product['id'];
            $quantity = $_SESSION['cart'][$product_id];
            
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'image' => $product['image'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'line_total' => $product['price'] * $quantity
            ];
            
            // Calculate the total price
            $total_price += $product['price'] * $quantity;
        }
        
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
    }
}

// Check for and display any success or error "flash messages"
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']); // Clear the message
}
if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']); // Clear the message
}
?>

<div class="cart-container">
    <h2>Your Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        
        <p>Your cart is empty. <a href="index.php">Go shopping!</a></p>
        
    <?php else: ?>
        
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
           <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td class="cart-product-info">
                        <img src="images/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                    </td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <form action="cart_update.php" method="POST" class="cart-quantity-form">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="99">
                            <button type="submit" name="update" class="btn-update">Update</button>
                        </form>
                    </td>
                    <td>₹<?php echo number_format($item['line_total'], 2); ?></td>
                    <td>
                        <a href="cart_update.php?remove=<?php echo $item['id']; ?>" class="btn-remove">Remove</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="cart-summary">
            <h3>Cart Total: ₹<?php echo number_format($total_price, 2); ?></h3>
            
            <?php 
            // Protect the checkout button
            // Only show it if the user is logged in
            if (isset($_SESSION['user_id'])): 
            ?>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            <?php else: ?>
                <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to proceed to checkout.</p>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
    
</div>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
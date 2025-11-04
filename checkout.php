<?php
// Include header (starts session)
require_once 'includes/header.php';
// Include database connection
require_once 'includes/db.php';

// --- PAGE PROTECTION ---
// 1. Check if user is logged in. If not, redirect to login.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to view the checkout page.";
    header('Location: login.php');
    exit;
}

// 2. Check if cart is empty. If so, redirect to cart.
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = "Your cart is empty. You cannot check out.";
    header('Location: cart.php');
    exit;
}

// --- SERVER-SIDE CART CALCULATION ---
// We re-calculate the total on the server to ensure the user
// didn't change the prices in the HTML before submitting.
$cart_items = [];
$total_price = 0;

$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));

try {
    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($product_ids);
    $products_in_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products_in_db as $product) {
        $product_id = $product['id'];
        $quantity = $_SESSION['cart'][$product_id];
        
        $cart_items[] = [ // We'll need this array for the order processing
            'id' => $product_id,
            'price' => $product['price'],
            'quantity' => $quantity
        ];
        
        $total_price += $product['price'] * $quantity;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- ORDER PROCESSING (when form is submitted) ---
$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    
    // Simple validation
    if (empty($full_name)) { $errors[] = "Full name is required."; }
    if (empty($address)) { $errors[] = "Address is required."; }

    if (empty($errors)) {
        // --- DATABASE TRANSACTION ---
        // This is a critical concept. A transaction ensures that ALL
        // queries succeed, or NONE of them do. This prevents creating
        // an order without items, or items without an order.
        try {
            // 1. Start the transaction
            $pdo->beginTransaction();

            // 2. Insert the main order into the 'orders' table
            $sql_order = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
            $stmt_order = $pdo->prepare($sql_order);
            $stmt_order->execute([$_SESSION['user_id'], $total_price]);
            
            // 3. Get the ID of the new order we just created
            $order_id = $pdo->lastInsertId();

            // 4. Insert each item from the cart into 'order_items'
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_item = $pdo->prepare($sql_item);
            
            foreach ($cart_items as $item) {
                $stmt_item->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            // 5. If all queries were successful, commit (save) the changes
            $pdo->commit();
            
            // 6. CRITICAL: Clear the shopping cart from the session
            unset($_SESSION['cart']);
            
            // 7. Redirect to a success page
            header('Location: order_success.php');
            exit;

        } catch (Exception $e) {
            // 8. If any query failed, roll back (undo) all changes
            $pdo->rollBack();
            $errors[] = "Your order could not be placed. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<div class="checkout-container">
    <h2 style="text-align:center">Checkout</h2>
    
    <?php
    // --- Display Error Messages (if any) ---
    if (!empty($errors)):
    ?>
        <div class="alert alert-danger">
            <?php
            foreach ($errors as $error):
                echo '<p>' . htmlspecialchars($error) . '</p>';
            endforeach;
            ?>
        </div>
    <?php
    endif;
    ?>
    
    <div class="checkout-row">
        <div class="checkout-form">
            <h3>Shipping Information</h3>
            <form action="checkout.php" method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </div>
            </form>
        </div>
        
        <div class="order-summary">
            <h3>Your Order</h3>
            <table class="cart-table">
                <tbody>
                    <?php 
                    // We already fetched the products, so we re-use the
                    // $products_for_display array (which is $products_in_db)
                    foreach ($products_in_db as $product) {
                        $quantity = $_SESSION['cart'][$product['id']];
                        $line_total = $product['price'] * $quantity;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($product['name']) . " (x$quantity)</td>";
                        echo "<td>₹" . number_format($line_total, 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <hr>
            <h4>Total: ₹<?php echo number_format($total_price, 2); ?></h4>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
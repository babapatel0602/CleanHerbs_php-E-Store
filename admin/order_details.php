<?php
// Include the admin header (security)
require_once 'includes/admin_header.php';
// Include the main database connection
require_once '../includes/db.php';

// --- 1. Get the Order ID ---
if (!isset($_GET['id'])) {
    header('Location: manage_orders.php');
    exit;
}
$order_id = (int)$_GET['id'];


// --- 2. Fetch the Main Order Details (and User) ---
try {
    $sql_order = "
        SELECT 
            orders.*, 
            users.username, 
            users.email 
        FROM 
            orders 
        JOIN 
            users ON orders.user_id = users.id 
        WHERE 
            orders.id = ?
    ";
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([$order_id]);
    $order = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found.");
    }

    // --- 3. Fetch the Items in this Order ---
    // We join with 'products' to get the product name
    $sql_items = "
        SELECT 
            order_items.*, 
            products.name 
        FROM 
            order_items 
        JOIN 
            products ON order_items.product_id = products.id 
        WHERE 
            order_items.order_id = ?
    ";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$order_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    require_once 'includes/admin_footer.php';
    exit;
}
?>

<h2>Order Details (ID: <?php echo $order['id']; ?>)</h2>

<div classs="order-details-container">

    <div class="order-section">
        <h3>Customer Details</h3>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
    </div>

    <div class="order-section">
        <h3>Order Summary</h3>
        <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
        <p><strong>Date:</strong> <?php echo $order['created_at']; ?></p>
    </div>

    <div class="order-section">
        <h3>Items in this Order</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price (at purchase)</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?> (Product ID: <?php echo $item['product_id']; ?>)</td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <a href="manage_orders.php" class="btn btn-secondary" style="margin-top: 2rem;">Back to All Orders</a>

</div>

<?php
// Include the admin footer
require_once 'includes/admin_footer.php';
?>
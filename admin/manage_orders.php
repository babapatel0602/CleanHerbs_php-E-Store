<?php
// Include the admin header (security)
require_once 'includes/admin_header.php';
// Include the main database connection
require_once '../includes/db.php';

// --- Fetch all orders ---
// We need to join with the 'users' table to get the customer's username
try {
    $sql = "
        SELECT 
            orders.*, 
            users.username 
        FROM 
            orders 
        JOIN 
            users ON orders.user_id = users.id 
        ORDER BY 
            created_at DESC
    ";
    
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
    $orders = []; // Set to empty array on error
}
?>

<h2>Manage Orders</h2>

<?php if (empty($orders)): ?>
    <div class="alert">No orders found.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date Placed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['username']); ?> (ID: <?php echo $order['user_id']; ?>)</td>
                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                <td><?php echo $order['created_at']; ?></td>
                <td>
                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-action btn-edit">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


<?php
// Include the admin footer
require_once 'includes/admin_footer.php';
?>
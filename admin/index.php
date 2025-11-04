<?php
// 1. Include the admin header - this includes the security check
require_once 'includes/admin_header.php';

// 2. Include the main database connection
require_once '../includes/db.php'; // Note: ../ goes one directory up

// We can add logic here to fetch stats
$order_count = 0;
$product_count = 0;

try {
    // Get total number of orders
    $stmt_orders = $pdo->query("SELECT COUNT(*) FROM orders");
    $order_count = $stmt_orders->fetchColumn();
    
    // Get total number of products
    $stmt_products = $pdo->query("SELECT COUNT(*) FROM products");
    $product_count = $stmt_products->fetchColumn();

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Failed to fetch dashboard stats: " . $e->getMessage() . "</div>";
}
?>

<div class="adminContainer">
    <h1>Admin Dashboard</h1>
<h3>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>

<br/>
<div class="dashboard-stats">
    <div class="stat-box"> 
        <h3>Total Orders : <?php echo $order_count; ?></h3> 
    </div>
    <div class="stat-box">
        <h3>Total Products : <?php echo $product_count; ?></h3>
    </div>
</div>
<br/>
<div class="dashboard-links">
    <h3>Quick Links</h3>

    <a href="manage_products.php" class="btn">Manage Products</a>&nbsp;&nbsp;
    <a href="manage_orders.php" class="btn">Manage Orders</a>
</div>
</div>


<?php
// 3. Include the admin footer
require_once 'includes/admin_footer.php';
?>
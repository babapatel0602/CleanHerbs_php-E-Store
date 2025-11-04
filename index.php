<?php
// Step 1: Include the header.
// This starts the session and outputs the <head> and <header>
require_once 'includes/header.php';

// Step 2: Include the database connection.
// This gives us access to the $pdo variable.
require_once 'includes/db.php';

// We'll put our main logic in a try/catch block
// This is good practice for database operations.
try {
    // Step 3: Write our SQL query to get all products
    $sql = "SELECT * FROM products WHERE stock > 0";
    
    // Step 4: Execute the query
    // We use $pdo->query() because there are no user variables.
    // It's a simple, safe query that doesn't need a prepared statement.
    $stmt = $pdo->query($sql);
    
    // Step 5: Fetch all results into an array
    // PDO::FETCH_ASSOC formats the array with column names as keys,
    // which makes it easy to use in our HTML (e.g., $product['name']).
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If the database query fails, show a user-friendly error.
    echo "<div class='alert alert-danger'>Error fetching products: " . $e->getMessage() . "</div>";
    $products = []; // Set products to an empty array so the page doesn't break
}
?>

<section class="hero">
    <h2>Welcome to Clean Herbs</h2>
    <p>Discover the power of nature with our premium Ayurvedic powders.</p>
</section>

<section class="product-grid-container">
    <h2>Our Products</h2>
    
    <div class="product-grid">
        <?php
        // Step 6: Check if our $products array has any items
        if (count($products) > 0):
            
            // Step 7: Loop through each product in the $products array
            // The 'foreach' loop assigns each item to the $product variable.
            foreach ($products as $product):
        ?>
            <div class="product-item">
                <img src="images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                
                <p class="price">₹<?php echo htmlspecialchars($product['price']); ?></p>
                
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                
                <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" class="btn">Add to Cart</a>
            </div>

        <?php
            // End of the foreach loop
            endforeach;
        
        else:
            // This message shows if the database table is empty
        ?>
            <p>No products found. Please check back later!</p>
        <?php
        // End of the if statement
        endif; 
        ?>
    </div> </section>

<?php
// Step 8: Include the footer
// This closes the <body> and <html> tags opened in header.php
require_once 'includes/footer.php';
?>
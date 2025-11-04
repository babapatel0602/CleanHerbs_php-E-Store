<?php
// Include the admin header (which includes security and session start)
require_once 'includes/admin_header.php';
// Include the main database connection
require_once '../includes/db.php';

// Define the path for image uploads
// '../images/products/' means "go up one folder, then into images/products/"
define('UPLOAD_PATH', '../images/products/');

// Initialize variables
$action = $_GET['action'] ?? 'list'; // Default action is 'list'
$product_id = $_GET['id'] ?? null;
$product = null;
$errors = [];
$success_message = '';

// --- Handle Form Submissions (Create & Update) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $current_image = $_POST['current_image'] ?? ''; // For updates
    
    // --- Validation ---
    if (empty($name)) { $errors[] = 'Product name is required.'; }
    if (empty($description)) { $errors[] = 'Description is required.'; }
    if ($price <= 0) { $errors[] = 'Price must be a positive number.'; }
    if ($stock < 0) { $errors[] = 'Stock cannot be negative.'; }
    
    // --- Image Upload Logic ---
    $image_name = $current_image; // Default to old image
    
    // Check if a new file was uploaded
    // $_FILES['image']['error'] == 0 means "no error, file uploaded"
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        
        $temp_name = $_FILES['image']['tmp_name'];
        // Create a unique, simple filename
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = UPLOAD_PATH . $image_name;
        
        // Validate image type
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($image_type != "jpg" && $image_type != "png" && $image_type != "jpeg") {
            $errors[] = "Sorry, only JPG, JPEG, & PNG files are allowed.";
        }
        
        // Try to move the uploaded file
        if (empty($errors)) {
            if (move_uploaded_file($temp_name, $target_file)) {
                // File upload success
                // If this was an update and there was an old image, delete it
                if (!empty($current_image) && $current_image != $image_name) {
                    @unlink(UPLOAD_PATH . $current_image); // @ supresses errors if file not found
                }
            } else {
                $errors[] = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // --- Database Operation (if no errors) ---
    if (empty($errors)) {
        try {
            if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
                // --- UPDATE Product ---
                $product_id_to_update = $_POST['product_id'];
                $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $price, $stock, $image_name, $product_id_to_update]);
                $success_message = 'Product updated successfully!';
            } else {
                // --- CREATE Product ---
                $sql = "INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $price, $stock, $image_name]);
                $success_message = 'Product added successfully!';
            }
            $action = 'list'; // Go back to the list view after success
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// --- Handle DELETE Action ---
if ($action == 'delete' && $product_id) {
    try {
        // First, get the image name so we can delete the file
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $image_to_delete = $stmt->fetchColumn();

        // Delete the product from the database
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        // Delete the image file from the server
        if ($image_to_delete) {
            @unlink(UPLOAD_PATH . $image_to_delete);
        }
        
        $success_message = 'Product deleted successfully!';
        $action = 'list'; // Set action to list to refresh the product list
        
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

// --- Fetch Data for 'list' or 'edit' ---
if ($action == 'edit' && $product_id) {
    // Get the specific product to edit
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($action == 'list') {
    // Get all products to display
    $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<h2>Manage Products</h2>

<?php
// Display any success or error messages
if ($success_message) { echo "<div class='alert alert-success'>$success_message</div>"; }
if (!empty($errors)) {
    echo "<div class='alert alert-danger'>";
    foreach ($errors as $error) { echo "<p>$error</p>"; }
    echo "</div>";
}
?>

<?php
// --- Page Content: Show a form or the list ---

// We check the $action variable to decide what to show
if ($action == 'add' || $action == 'edit'):
    // --- SHOW ADD/EDIT FORM ---
    
    // Set form values
    $form_action = 'manage_products.php?action=' . $action;
    $form_title = ($action == 'add') ? 'Add New Product' : 'Edit Product';
    
    // If editing, use existing product data. If adding, use empty/default.
    $name = $product['name'] ?? $_POST['name'] ?? '';
    $description = $product['description'] ?? $_POST['description'] ?? '';
    $price = $product['price'] ?? $_POST['price'] ?? '0.00';
    $stock = $product['stock'] ?? $_POST['stock'] ?? '10';
    $image = $product['image'] ?? '';
?>

    <h3><?php echo $form_title; ?></h3>
    
    <form action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data" class="admin-form">
        
        <?php if ($action == 'edit'): ?>
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Price (₹)</label>
            <input type="number" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" step="0.01">
        </div>
        
        <div class="form-group">
            <label for="stock">Stock Quantity</label>
            <input type="number" id="stock" name="stock" class="form-control" value="<?php echo htmlspecialchars($stock); ?>" step="1">
        </div>
        
        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" class="form-control">
            
            <?php if ($action == 'edit' && $image): ?>
                <p>Current image: <?php echo htmlspecialchars($image); ?></p>
                <img src="<?php echo UPLOAD_PATH . htmlspecialchars($image); ?>" alt="Current Image" width="100">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image); ?>">
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo ($action == 'add') ? 'Add Product' : 'Update Product'; ?></button>
            <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

<?php
else:
    // --- SHOW PRODUCT LIST (default) ---
?>

    <div class="admin-table-actions">
        <a href="manage_products.php?action=add" class="btn btn-primary">Add New Product</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5">No products found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                <tr>
                    <td><img src="<?php echo UPLOAD_PATH . htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" width="50"></td>
                    <td><?php echo htmlspecialchars($prod['name']); ?></td>
                    <td>₹<?php echo number_format($prod['price'], 2); ?></td>
                    <td><?php echo $prod['stock']; ?></td>
                    <td>
                        <a href="manage_products.php?action=edit&id=<?php echo $prod['id']; ?>" class="btn-action btn-edit">Edit</a>
                        <a href="manage_products.php?action=delete&id=<?php echo $prod['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

<?php
endif; // End of $action check
?>

<?php
// Include the admin footer
require_once 'includes/admin_footer.php';
?>
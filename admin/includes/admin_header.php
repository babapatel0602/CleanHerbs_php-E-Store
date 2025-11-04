<?php
// Start the session at the very top
session_start();

// --- ADMIN PAGE SECURITY ---
// 1. Check if user is logged in
// 2. Check if user is an admin (is_admin == 1)

if (!isset($_SESSION['user_id'])) {
    // Not logged in
    // Store an error message and redirect to the *main* login page
    $_SESSION['error_message'] = "You must be logged in to access the admin panel.";
    header('Location: ../login.php'); // Note: ../ goes one directory up
    exit;
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Logged in, but NOT an admin
    // Store an error message and redirect to the *main* homepage
    $_SESSION['error_message'] = "You do not have permission to access the admin panel.";
    header('Location: ../index.php'); // Note: ../ goes one directory up
    exit;
}

// If the script gets this far, the user is a logged-in admin.
// We can now load the admin page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Clean Herbs</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1 class="logo">Clean Herbs - Admin Panel</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="manage_products.php">Products</a></li>
                    <li><a href="manage_orders.php">Orders</a></li>
                    <li><a href="../index.php" target="_blank">View Site</a></li>
                    <li><a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    
</body>
</html>
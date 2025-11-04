<?php
// Start the session at the very top
session_start();

// Include the database connection
require_once 'includes/db.php';

// Initialize variables
$errors = [];
$success_message = '';

// Check if we have a success message from registration
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    // Unset the message so it only shows on the next page load
    unset($_SESSION['success_message']);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Validate data
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Invalid email format.'; }
    if (empty($password)) { $errors[] = 'Password is required.'; }

    // 3. If no validation errors, try to find the user
    if (empty($errors)) {
        try {
            // Find the user by their email using a prepared statement
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Verify the user and password
            // Check if a user was found AND if the password matches
            // password_verify() securely compares the plain-text password
            // with the hash we stored in the database.
            if ($user && password_verify($password, $user['password'])) {
                
                // Password is correct!
                // 5. Store user data in the session.
                // This is what keeps the user "logged in" across pages.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // 6. Redirect to the homepage
                header('Location: index.php');
                exit;

            } else {
                // Invalid email or password
                $errors[] = 'Invalid email or password.';
            }

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
} // End of POST request handling

?>

<?php
// Include the header
require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Login</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php
            foreach ($errors as $error):
                echo '<p>' . htmlspecialchars($error) . '</p>';
            endforeach;
            ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
    <p class="form-link">Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
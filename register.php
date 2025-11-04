<?php
// We must start the session at the very top,
// before any HTML is sent. This lets us use $_SESSION.
session_start();

// Include our database connection
require_once 'includes/db.php';

// Initialize an array to hold error messages
$errors = [];

// Check if the form was submitted using the 'POST' method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Get and sanitize form data
    // trim() removes whitespace from the beginning and end
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 2. Validate the data
    if (empty($username)) { $errors[] = 'Username is required.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid email address is required.'; }
    if (empty($password)) { $errors[] = 'Password is required.'; }
    if ($password !== $confirm_password) { $errors[] = 'Passwords do not match.'; }

    // 3. Check if email already exists (only if other data is valid)
    if (empty($errors)) {
        try {
            // This is a PREPARED STATEMENT.
            // The '?' is a placeholder. This is a critical security feature
            // that prevents SQL INJECTION attacks.
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            
            // We execute the statement, passing the user's email safely
            // into the '?' placeholder.
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // User already exists
                $errors[] = 'This email address is already registered.';
            }

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    // 4. If there are still no errors, create the new user
    if (empty($errors)) {
        
        // SECURITY: Hash the password
        // We NEVER store passwords in plain text.
        // password_hash() creates a strong, one-way, secure hash.
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Insert the new user using another prepared statement
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            // Execute the insert, passing the safe values
            $stmt->execute([$username, $email, $hashed_password]);

            // Set a "flash message" in the session to show on the login page
            $_SESSION['success_message'] = 'Registration successful! You can now log in.';
            
            // Redirect the user to login.php
            header('Location: login.php');
            // exit() is crucial after a header redirect
            // to stop the script from running any further.
            exit; 

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
} // End of POST request handling

?>

<?php
// Now that all PHP logic is done, we can include the header.
require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Create an Account</h2>

    <?php
    // --- Display Error Messages ---
    // Check if our $errors array has anything in it
    if (!empty($errors)):
    ?>
        <div class="alert alert-danger">
            <?php
            // Loop through each error and display it
            foreach ($errors as $error):
                echo '<p>' . htmlspecialchars($error) . '</p>';
            endforeach;
            ?>
        </div>
    <?php
    endif;
    // --- End of Error Display ---
    ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>
    <p class="form-link">Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php
// Finally, include the footer to close the page.
require_once 'includes/footer.php';
?>
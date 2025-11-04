<?php
// 1. Start the session
// We need to start the session to be able to access and destroy it.
session_start();

// 2. Unset all session variables
// This clears all data from the $_SESSION array (like user_id, username)
$_SESSION = [];

// 3. Destroy the session
// This removes the session file from the server.
session_destroy();

// 4. Redirect the user to the login page
header('Location: login.php?status=loggedout');
exit;
?>
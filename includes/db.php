<?php
/* This is our database connection file.
It's included in any script that needs to talk to the database.
*/

// Database connection details
$host = 'localhost';      // Our server, usually localhost
$dbname = 'cleanherbs_db';  // The name of the database we created in Step 1
$username = 'root';       // The default username for XAMPP's MySQL
$password = '';           // The default password for XAMPP's MySQL (it's empty)

// Set up the DSN (Data Source Name)
// This string tells PHP what kind of database we're connecting to (mysql),
// where it is (host), and its name (dbname).
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // 1. Create a new PDO (PHP Data Objects) instance
    // This is the modern, secure way to connect to a database in PHP.
    $pdo = new PDO($dsn, $username, $password);

    // 2. Set some PDO options for error handling
    // This tells PDO to "throw an exception" (a critical error) if
    // something goes wrong with a query, which is good for debugging.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // 3. If the connection fails, "catch" the error
    // The 'die()' function stops the script immediately and prints a message.
    // We show the error message ($e->getMessage()) so we know what went wrong.
    die("Database connection failed: " . $e->getMessage());
}

// If the script gets this far, $pdo is our working database connection.
// We can now include this file in other scripts to get access to $pdo.
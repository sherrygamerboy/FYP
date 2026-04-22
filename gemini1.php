<?php
// Database configuration
$host     = 'localhost';
$dbName   = 'your_database';
$dbUser   = 'your_username';
$dbPass   = 'your_password';
$charset  = 'utf8mb4';

// Set up DSN and options for PDO
$dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if data was sent via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = $_POST['username'] ?? '';
    $passInput = $_POST['password'] ?? '';

    if (!empty($userInput) && !empty($passInput)) {
        // Prepare statement to find the user
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
        $stmt->execute([$userInput]);
        $user = $stmt->fetch();

        // Verify the password against the stored hash
        if ($user && password_verify($passInput, $user['password_hash'])) {
            echo "Success";
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "Please provide both username and password.";
    }
}
?>
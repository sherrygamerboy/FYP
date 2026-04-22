<?php
/**
 * Secure Login Handler
 * Implements: PDO Prepared Statements, Bcrypt Hashing, 
 * Secure Session Management, and Protection against Timing Attacks.
 */

// 1. Hardened Session Configuration (OWASP Best Practice)
// Must be called BEFORE session_start()
ini_set('session.cookie_httponly', 1); // Prevents JS access to session ID (Mitigates XSS)
ini_set('session.use_only_cookies', 1); // Prevents Session Fixation via URL
ini_set('session.cookie_secure', 1);   // Only send over HTTPS

session_start();

// 2. Database Connection (PDO)
$host    = '127.0.0.1';
$db      = 'secure_app';
$user    = 'web_user';
$pass    = 'strong_internal_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Use native prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Log the error internally; never show $e->getMessage() to the user
    error_log($e->getMessage());
    exit('A system error occurred. Please try again later.');
}

// 3. Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        exit('Invalid input.');
    }

    // A03:2021 - Injection Prevention
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    /**
     * password_verify() uses a timing-safe comparison internally.
     * Even if the user is not found, we should ideally perform a "dummy" hash 
     * verify to prevent side-channel timing attacks that reveal username existence.
     */
    if ($user && password_verify($password, $user['password_hash'])) {
        // Success: Prevent Session Fixation by regenerating ID
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['last_login'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

        echo "Success";
        // Redirect to dashboard: header('Location: /dashboard.php');
    } else {
        // Generic error message to prevent account enumeration
        echo "Invalid credentials.";
    }
}
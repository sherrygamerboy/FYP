<?php
// --- Security headers (defense-in-depth) ---
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 0'); // modern browsers use CSP instead
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none';");

// --- Secure session configuration ---
$secure   = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$httponly = true;

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',          // set to your domain if needed
    'secure'   => $secure,
    'httponly' => $httponly,
    'samesite' => 'Strict',    // or 'Lax' depending on your needs
]);

session_start();

// --- CSRF check (assumes you set a token in the login form) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    exit('Invalid CSRF token');
}

// --- Basic input handling ---
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    exit('Invalid input');
}

// --- Database connection (PDO with exceptions) ---
$dsn      = 'mysql:host=localhost;dbname=your_database;charset=utf8mb4';
$dbUser   = 'your_db_user';
$dbPass   = 'your_db_password';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Do not leak details to user
    http_response_code(500);
    exit('Internal Server Error');
}

// --- Fetch user by username using prepared statement ---
$sql = 'SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

// --- Constant-time check to avoid user enumeration ---
$valid = false;
if ($user) {
    // password_hash() with PASSWORD_BCRYPT (or PASSWORD_DEFAULT) must be used at registration
    $valid = password_verify($password, $user['password_hash']);
}

// Optional: rehash if algorithm/cost changed
if ($valid && password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $update  = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
    $update->execute([':hash' => $newHash, ':id' => $user['id']]);
}

if (!$valid) {
    // Consider logging failed attempts and implementing rate limiting / lockout
    http_response_code(401);
    exit('Invalid username or password');
}

// --- Successful authentication ---
// Regenerate session ID to prevent fixation
session_regenerate_id(true);

// Store minimal identity info in session
$_SESSION['user_id']   = $user['id'];
$_SESSION['username']  = $user['username'];
$_SESSION['logged_in'] = true;

// Optionally rotate CSRF token after login
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Redirect to dashboard or return JSON
header('Location: /dashboard.php');
exit;

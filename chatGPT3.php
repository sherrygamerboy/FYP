<?php
declare(strict_types=1);

// -------------------------
// Secure session setup
// -------------------------
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '', // set your domain if needed
    'secure'   => $secure,     // true in HTTPS
    'httponly' => true,        // JS cannot access cookie
    'samesite' => 'Strict'     // or 'Lax' if you need cross-site POST redirects
]);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_start();

// -------------------------
// Basic request validation
// -------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Optional but recommended: CSRF token check
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    exit('Invalid request');
}

// Normalize inputs
$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    // Generic message to avoid user enumeration
    exit('Invalid credentials');
}

// -------------------------
// Database connection (PDO)
// -------------------------
$dsn = 'mysql:host=localhost;dbname=your_database;charset=utf8mb4';
$dbUser = 'your_db_user';
$dbPass = 'your_db_password';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Do not leak details
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    exit('Server error');
}

// -------------------------
// Fetch user with prepared statement
// -------------------------
$stmt = $pdo->prepare(
    'SELECT id, username, password_hash, is_active
     FROM users
     WHERE username = :username
     LIMIT 1'
);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

// Use a dummy hash to mitigate timing attacks when user not found
$dummyHash = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG'; // valid bcrypt format

$hashToCheck = $user['password_hash'] ?? $dummyHash;
$validPassword = password_verify($password, $hashToCheck);

// Optionally upgrade hash if needed (cost changes, etc.)
if ($user && $validPassword && password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
    $newHash = password_hash($password, PASSWORD_BCRYPT);
    $upd = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
    $upd->execute([':h' => $newHash, ':id' => $user['id']]);
}

// -------------------------
// Authentication decision
// -------------------------
if (!$user || !$validPassword || (int)$user['is_active'] !== 1) {
    // Generic message; consider adding rate limiting / lockouts
    exit('Invalid credentials');
}

// -------------------------
// Successful login handling
// -------------------------
// Prevent session fixation
session_regenerate_id(true);

// Minimal session data
$_SESSION['user_id']  = (int)$user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['logged_in'] = true;
$_SESSION['last_regen'] = time();

// Optionally rotate session ID periodically
if (!isset($_SESSION['last_regen']) || time() - $_SESSION['last_regen'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regen'] = time();
}

// Optional: record login event / IP / UA for auditing
// error_log(sprintf('User %s logged in from %s', $user['username'], $_SERVER['REMOTE_ADDR'] ?? 'unknown'));

echo 'Success';
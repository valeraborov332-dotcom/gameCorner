<?php
/**
 * Database Connection Configuration.
 *
 * Establishes a PDO connection to the MySQL database.
 * Detects environment (Localhost vs Production) to select correct credentials.
 * Implements "Auto-Login" functionality by checking the 'remember_me' cookie
 * and validating the token against the database.
 *
 * @package GameCorner
 * @author  Cirael
 */
// Database Configuration
$host = 'localhost';
$charset = 'utf8mb4';

// Environment Check (Local vs Production)
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    $db   = 'game_reviews'; 
    $user = 'root';
    $pass = ''; 
} 
else {
    $db   = 'NAME_HERE';
    $user = 'USER_HERE';
    $pass = 'PASSWORD_HERE';
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// --- AUTO-LOGIN VIA COOKIES ---
// If user is NOT in session but HAS a cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    
    $token = $_COOKIE['remember_me'];
    
    // Find user with this token in DB
    $stmt_cookie = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt_cookie->execute([$token]);
    $user_cookie = $stmt_cookie->fetch();

    if ($user_cookie) {
        // Token matches -> Restore Session
        $_SESSION['user_id'] = $user_cookie['id'];
        $_SESSION['username'] = $user_cookie['username'];
        $_SESSION['is_admin'] = $user_cookie['is_admin'];
    } else {
        // Token invalid (old or fake) -> Delete cookie
        setcookie('remember_me', '', time() - 3600, "/");
    }
}
?>
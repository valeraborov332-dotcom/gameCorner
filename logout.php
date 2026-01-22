<?php 
/**
 * Logout Handler.
 *
 * Destroys the user session.
 * Clears the 'remember_me' cookie and removes the token from the database.
 * Redirects the user to the homepage.
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
require_once 'db.php'; // Required to access $pdo

// 1. Clear token in Database (Security)
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// 2. Destroy Session
session_unset();
session_destroy();

// 3. Delete Cookie (Set expiration time to past)
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, "/");
    unset($_COOKIE['remember_me']);
}

header("Location: index.php");
exit;
?>
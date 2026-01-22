<?php
/**
 * Login Form Handler.
 *
 * Processes POST requests from login.php.
 * Verifies credentials using password_verify().
 * Generates session variables and 'remember_me' cookies/tokens.
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $login = trim($_POST['login_identifier']); 
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        
        // 1. Set Session Variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin']; // <--- NEW: Store admin status
        
        // 2. "Remember Me" Logic
        if (isset($_POST['remember'])) {
            $token = bin2hex(random_bytes(32)); 
            $updateToken = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $updateToken->execute([$token, $user['id']]);
            setcookie('remember_me', $token, time() + (86400 * 30), "/", "", false, true);
        }
        
        header("Location: index.php");
        exit;
        
    } else {
        $_SESSION['error'] = "Invalid username or password!";
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
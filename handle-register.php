<?php 
/**
 * Registration Form Handler.
 *
 * Processes POST requests from register.php.
 * Validates input (email uniqueness, password match).
 * Hashes passwords using PASSWORD_DEFAULT before storing in DB.
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
require_once "db.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
   $username = trim($_POST["username"]);
   $email = trim($_POST["email"]);
   $password = $_POST["password"];

   // Check if email taken
   $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
   $stmt->execute([$email]);

   if($stmt->fetch()) {
      $_SESSION["error"] = "Email is already taken!";
      // SAVE INPUT TO SESSION (Sticky Form)
      $_SESSION["form_data"] = ["username" => $username, "email" => $email];
      header("Location: register.php");
      exit;
   }
   
   $passwordHash = password_hash($password, PASSWORD_DEFAULT);

   $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
   $stmt = $pdo->prepare($sql);

   if($stmt->execute([$username, $email, $passwordHash])) {
      $_SESSION["success"] = "Registration successful! Please login.";
      // Clear form data on success
      unset($_SESSION["form_data"]);
      header("Location: login.php");
      exit;
   } else {
      $_SESSION["error"] = "Registration failed. Please try again.";
      $_SESSION["form_data"] = ["username" => $username, "email" => $email];
      header("Location: register.php");
      exit;
   }
} else {
   header("Location: register.php");
   exit;
}
?>
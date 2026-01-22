<?php
/**
 * Registration Page View.
 *
 * Displays the user registration form.
 * Supports "sticky forms" - pre-filling values if validation fails.
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
$old_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <script src="script.js" defer></script>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
   
   <link rel="stylesheet" href="styles.css">
</head>

<body>
<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="index.php">GameCorner</a>
        </div>
        
        <form class="search-form" action="search.php" method="get">
            <input aria-label="Search game query" placeholder="search games..." type="text" name="query" id="search-input" autocomplete="off">
            <button type="submit" class="search-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"> <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/> </svg>
            </button>
            <div id="search-results" class="search-dropdown"></div>
        </form>
        
         <nav class="main-nav">
             <ul>
                 <?php if (isset($_SESSION['user_id'])): ?>
                     <li class="user-greeting">
                         Hi, <a href="profile.php" class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></a>!
                     </li>
                     <li><a href="logout.php" class="btn-logout">Logout</a></li>
                 <?php else: ?>
                     <li><a href="login.php" class="nav-link">Login</a></li>
                     <li><a href="register.php" class="btn-register">Register</a></li>
                 <?php endif; ?>
             </ul>
         </nav>
    </div> 
</header>   

   <main>
      <div class="container">
            <h2 style="text-align: center; margin-bottom: 20px;">Create Account</h2>
            
               <?php
                     if (isset($_SESSION['error'])) {
                         echo '<div style="color: #ff4444; text-align: center; margin-bottom: 15px; border: 1px solid #ff4444; padding: 10px; border-radius: 4px; background: rgba(255,68,68,0.1);">' . $_SESSION['error'] . '</div>';
                         unset($_SESSION['error']); 
                     }
               ?>
               
             <form action="handle-register.php" method="post" id="register-form" novalidate>
                
                <label for="username-reg">Username:</label>
                <input type="text" id="username-reg" name="username" 
                       value="<?php echo htmlspecialchars($old_data['username'] ?? ''); ?>" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>" required> 
                
                <label for="password-register">Password:</label>
                <input type="password" id="password-register" name="password" required>
                
                <label for="confirm-password">Confirm password:</label>
                <input type="password" id="confirm-password" name="confirm_password" required>
                
                <div id="error"></div>

                <button type="submit">Register</button>
             </form>
             
             <p class="login-prompt" style="text-align: center; margin-top: 15px;">
                 Already have an account? <a href="login.php">Login here</a>
             </p>

             <?php unset($_SESSION['form_data']); // Clear data after rendering ?>
      </div>
   </main>

   <footer>
      <div class="container">
            <p>&copy; Game-Reviews 2025</p>
      </div>
   </footer>
</body>
</html>
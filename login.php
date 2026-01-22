<?php 
/**
 * Login Page View.
 *
 * Displays the user login form.
 * Contains the "Remember Me" checkbox interface.
 * Redirects already logged-in users to the index page.
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">

   <script src="script.js" defer></script>

   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <title>login</title>

   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

   <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="styles.css">
</head>

<body>
<header class="main-header">
    <div class="container">
        
        <div class="logo">
            <a href="index.php">GameCorner</a>
        </div>
        
        <form class="search-form" action="search.php" method="get">
            <input aria-label="Search game query" placeholder="search your game..." type="text" name="query" id="search-input">
            <button type="submit" class="search-button" aria-label="Submit search">
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

                    <li>
                        <a href="profile.php" class="nav-btn-profile">My Profile</a>
                    </li>

                    <li>
                        <a href="logout.php" class="btn-logout">Logout</a>
                    </li>

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
         <h2>Login</h2>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div style="color: red; text-align: center; margin-bottom: 15px;">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']); 
            }
            if (isset($_SESSION['success'])) {
                echo '<div style="color: green; text-align: center; margin-bottom: 15px;">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

         <form action="handle-login.php" method="post" id="login-form" novalidate>
            <label for="login-identifier">Username or email:</label>
            <input type="text" id="login-identifier" name="login_identifier" required>
            <label for="password-login">Password:</label>
            <input type="password" id="password-login" name="password" required>
            <div id="error"></div>
            <div class="remember-me-container">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember me</label>
            </div>
            <button type="submit">Login</button>
         </form>
      </div>
   </main>
   <footer>
      <div class="container">
            <p>&copy; Game-Reviews 2025</p>
      </div>
   </footer>

</body>
</html>
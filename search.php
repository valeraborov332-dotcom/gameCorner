<?php
/**
 * Search Results Page.
 *
 * Handles the search query submitted from the main header.
 * Sanitizes the input and queries the database for matching game titles.
 * Displays the results in a grid layout or a friendly "Not Found" message.
 *
 * @package GameCorner
 * @author  Cirael
 * @version 1.0
 */

session_start();
require_once 'db.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];

if ($query !== '') {
    // Получаем игры И их рейтинг критиков (user_rating пока не считаем для скорости поиска, или можно добавить JOIN)
    $stmt = $pdo->prepare("SELECT * FROM games WHERE title LIKE ? ORDER BY title ASC");
    $stmt->execute(['%' . $query . '%']);
    $results = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search: <?php echo htmlspecialchars($query); ?></title>
    <script src="script.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="main-header">
        <div class="container">
            <div class="logo"><a href="index.php">GameCorner</a></div>
            
            <form class="search-form" action="search.php" method="get">
                <input id="search-input" type="text" name="query" placeholder="search games..." value="<?php echo htmlspecialchars($query); ?>" autocomplete="off">
                <button type="submit" class="search-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"> <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/> </svg>
                </button>
                <div id="search-results" class="search-dropdown"></div>
            </form>
            
            <nav class="main-nav">
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="user-greeting">
                            Hi, <a href="profile.php" class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                <span class="admin-badge">(ADMIN)</span>
                            <?php endif; ?>
                            !
                        </li>
                        <li><a href="profile.php" class="nav-btn-profile">My Profile</a></li>
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
        <div class="container content-frame">
            <h2 class="search-page-title">Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
            
            <?php if (empty($query)): ?>
                <div class="search-no-results">
                    <p>Please enter a search term.</p>
                    <a href="index.php" class="back-link">Back to all games</a>
                </div>
            
            <?php elseif (count($results) > 0): ?>
                
                <div class="games-grid">
                    <?php foreach ($results as $game): ?>
                        <a href="game.php?id=<?php echo $game['id']; ?>" class="game-card">
                            <img src="<?php echo htmlspecialchars($game['image_url']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                            
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                                
                                <div class="ratings-container">
                                    <div class="rating-box critic-score">
                                        <span class="label">Critic Score</span>
                                        <span class="score"><?php echo htmlspecialchars($game['rating']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="search-no-results">
                    <h3>No games found</h3>
                    <p>We couldn't find any games matching "<?php echo htmlspecialchars($query); ?>".</p>
                    <a href="index.php" class="back-link">Back to all games</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; Game-Reviews 2025</p>
        </div>
    </footer>
</body>
</html>
<?php
/**
 * Homepage / Game Catalog.
 *
 * Displays the main grid of games available in the database.
 * Implements pagination logic (LIMIT/OFFSET) to handle large datasets.
 * Shows average user ratings calculated dynamically from the reviews table.
 *
 * @package GameCorner
 * @author  Cirael
 * @version 1.0
 */
session_start();
require_once 'db.php';


// Pagination settings 
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sort_type = isset($_GET['sort']) ? $_GET['sort'] : 'new';

switch ($sort_type) {
    case 'alpha':
        $order_sql = "ORDER BY title ASC";
        break;
    case 'rating':
        $order_sql = "ORDER BY rating DESC";
        break;
    case 'old':
        $order_sql = "ORDER BY id ASC";
        break;
    case 'new':
    default:
        $order_sql = "ORDER BY id DESC";
        break;
}

$sql = "SELECT g.*, AVG(r.rating) as user_rating 
        FROM games g 
        LEFT JOIN reviews r ON g.id = r.game_id 
        GROUP BY g.id 
        $order_sql 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$games = $stmt->fetchAll();

$total_games = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
$total_pages = ceil($total_games / $limit);
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <script src="script.js" defer></script>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>GameCorner - Home</title>
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
            <input aria-label="Search game query" placeholder="search games..." type="text" name="query" id="search-input" autocomplete="off">
            <button type="submit" class="search-button" aria-label="Submit search">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"> <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/> </svg>
            </button>
            <div id="search-results" class="search-dropdown"></div>
         </form>
         <nav class="main-nav">
             <ul>
               <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                    
                     <li class="user-greeting">
                        Hi, <a href="profile.php" class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                        
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <span class="admin-badge">(ADMIN)</span>
                        <?php endif; ?>
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
      <div class="container content-frame">
         <h2 class="latest">Latest game reviews</h2>
         <div class="container content-frame"> <div class="catalog-header">
        <div class="sort-controls">
            <span>Sort by:</span>
            <a href="?sort=new" class="<?php echo ($sort_type == 'new') ? 'active' : ''; ?>">Newest</a>
            <a href="?sort=alpha" class="<?php echo ($sort_type == 'alpha') ? 'active' : ''; ?>">A-Z</a>
            <a href="?sort=rating" class="<?php echo ($sort_type == 'rating') ? 'active' : ''; ?>">Top Rated</a>
        </div>
         </div>
         <div class="games-grid">
             <?php foreach ($games as $game): ?>
                <a href="game.php?id=<?php echo $game['id']; ?>" class="game-card">
                   <img src="<?php echo htmlspecialchars($game['image_url']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                   <div class="card-content">
                      <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                      <div class="ratings-container">
                         <div class="rating-box critic-score">
                            <span class="label">Critic Score</span>
                            <span class="score"><?php echo htmlspecialchars($game['rating']); ?></span>
                         </div>
                         <div class="rating-box user-score">
                            <span class="label">User Score</span>
                            <span class="score">
                                <?php echo $game['user_rating'] ? round($game['user_rating'], 1) : '-'; ?>
                            </span>
                         </div>
                      </div>
                   </div>
                </a> 
             <?php endforeach; ?>
         </div>

         <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort_type; ?>" class="prev">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort_type; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort_type; ?>" class="next">Next</a>
                <?php endif; ?>
            <?php endif; ?> </div>
               </main>

   <footer>
      <div class="container">
            <p>&copy; Game-Reviews 2025</p>
      </div>
   </footer>
</body>
</html>
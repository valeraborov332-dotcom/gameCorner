<?php
/**
 * User Profile Page.
 *
 * Displays the currently logged-in user's information.
 * Lists all reviews written by the user with options to edit or delete them.
 * Provides access to Admin Panel if the user has admin privileges.
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
require_once 'db.php';

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Handle Review Deletion (from profile)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = intval($_POST['review_id']);
    
    // Check if review belongs to user
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    
    header("Location: profile.php");
    exit;
}

// 3. Fetch User Info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// 4. Fetch User's Reviews (Joined with Games table to get game title)
$sql_reviews = "SELECT r.*, g.title as game_title, g.id as game_id, g.image_url as game_image 
                FROM reviews r 
                JOIN games g ON r.game_id = g.id 
                WHERE r.user_id = ? 
                ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql_reviews);
$stmt->execute([$user_id]);
$my_reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <script src="script.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="main-header">
        <div class="container">
            <div class="logo"><a href="index.php">GameCorner</a></div>
            
            <form class="search-form" action="search.php" method="get">
                <input id="search-input" type="text" name="query" placeholder="search games..." autocomplete="off">
                <button type="submit" class="search-button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"> <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/> </svg></button>
                <div id="search-results" class="search-dropdown"></div>
            </form>
            
            <nav class="main-nav">
                <ul>
                    <li class="user-greeting">Hi, <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>!</li>
                     <?php if ($user['is_admin'] == 1): ?>
                         <li>
                             <a href="admin.php" class="nav-btn-admin">Admin Panel</a>
                         </li>
                     <?php endif; ?>
                    <li><a href="logout.php" class="btn-logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="profile-stats">Reviews written: <span><?php echo count($my_reviews); ?></span></p>
                </div>
            </div>

            <div class="content-frame" style="margin-top: 30px;">
                <h2 style="text-align: left; border-bottom: 1px solid #333; padding-bottom: 15px; margin-bottom: 20px;">My Reviews</h2>

                <?php if (count($my_reviews) > 0): ?>
                    <div class="reviews-list">
                        <?php foreach ($my_reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-meta">
                                        <a href="game.php?id=<?php echo $review['game_id']; ?>" class="review-game-title">
                                            <?php echo htmlspecialchars($review['game_title']); ?>
                                        </a>
                                        <span class="review-rating">⭐ <?php echo $review['rating']; ?>/10</span>
                                    </div>
                                    <div class="review-actions">
                                        <a href="edit-review.php?id=<?php echo $review['id']; ?>" class="btn-edit">Edit</a>
                                        <form action="" method="POST" class="delete-form" onsubmit="return confirm('Delete this review?');">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" name="delete_review" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="review-date"><?php echo date("F j, Y", strtotime($review['created_at'])); ?></div>
                                <div class="review-text"><?php echo nl2br(htmlspecialchars($review['content'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #888;">You haven't written any reviews yet.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <footer>
        <div class="container"><p>&copy; Game-Reviews 2025</p></div>
    </footer>
</body>
</html>
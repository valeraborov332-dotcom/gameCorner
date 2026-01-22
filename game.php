<?php
/**
 * Game Detail Page.
 *
 * Displays full information about a specific game (Title, Description, Trailer).
 * Handles the logic for:
 * - Displaying existing reviews.
 * - Submitting new reviews (with image upload support).
 * - Deleting reviews (for authors and admins).
 *
 * Uses $_GET['id'] to identify the game.
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
require_once 'db.php';


if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$game_id = intval($_GET['id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

$already_reviewed = false;
if ($user_id) {
    $check_stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND game_id = ?");
    $check_stmt->execute([$user_id, $game_id]);
    if ($check_stmt->fetch()) {
        $already_reviewed = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!$user_id) {
        header("Location: login.php");
        exit;
    }

    if (isset($_POST['submit_review'])) {
        
        if ($already_reviewed) {
             header("Location: game.php?id=" . $game_id);
             exit;
        }

        $rating = intval($_POST['rating']);
        $content = trim($_POST['content']);
        
        $image_paths_json = null;

        if (isset($_FILES['review_images']) && !empty($_FILES['review_images']['name'][0])) {
            $uploaded_paths = [];
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (!is_dir('uploads')) mkdir('uploads', 0777, true);

            foreach ($_FILES['review_images']['name'] as $key => $filename) {
                $file_tmp = $_FILES['review_images']['tmp_name'][$key];
                $file_error = $_FILES['review_images']['error'][$key];
                
                if ($file_error === 0) {
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    if (in_array($file_ext, $allowed)) {
                        $new_name = uniqid('review_') . '_' . $key . '.' . $file_ext;
                        $destination = 'uploads/' . $new_name;

                        if (move_uploaded_file($file_tmp, $destination)) {
                            $uploaded_paths[] = $destination;
                        }
                    }
                }
            }

            if (count($uploaded_paths) > 0) {
                $image_paths_json = json_encode($uploaded_paths);
            }
        }

        if ($rating >= 1 && $rating <= 10 && !empty($content)) {
            $sql = "INSERT INTO reviews (game_id, user_id, rating, content, image_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$game_id, $user_id, $rating, $content, $image_paths_json]);
            
            header("Location: game.php?id=" . $game_id);
            exit;
        }
    }

    elseif (isset($_POST['delete_review'])) {
        $review_id = intval($_POST['review_id']);
        
        $stmt_check = $pdo->prepare("SELECT user_id FROM reviews WHERE id = ?");
        $stmt_check->execute([$review_id]);
        $review_author_id = $stmt_check->fetchColumn();

        if ($user_id == $review_author_id || $is_admin) {
            $sql = "DELETE FROM reviews WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$review_id]);
        }

        header("Location: game.php?id=" . $game_id);
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game) die("Game not found!");

$sql_reviews = "SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.game_id = ? ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql_reviews);
$stmt->execute([$game_id]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['title']); ?></title>
    <script src="script.js" defer></script>
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
                <input id="search-input" type="text" name="query" placeholder="search games..." aria-label="Search" autocomplete="off">
                <button type="submit" class="search-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"> <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/> </svg>
                </button>
                <div id="search-results" class="search-dropdown"></div>
            </form>
            
            <nav class="main-nav">
                <ul>
                    <?php if ($user_id): ?>
                        <li class="user-greeting">
                            Hi, <a href="profile.php" class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                            <?php if ($is_admin): ?>
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
        <div class="container">
            
            <div class="game-header-block">
                <h1><?php echo htmlspecialchars($game['title']); ?></h1>
                <div class="game-rating-badge">
                    Critic Score: <span><?php echo htmlspecialchars($game['rating']); ?>/10</span>
                </div>
            </div>

            <div class="game-main-content">
                <div class="game-poster">
                    <img src="<?php echo htmlspecialchars($game['image_url']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                </div>

                <div class="game-synopsis">
                    <h3>About the game</h3>
                    <p>
                        <?php echo nl2br(htmlspecialchars($game['description'] ?: "No description available yet.")); ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($game['trailer_url'])): ?>
                <div class="game-cinematic-trailer">
                    <h3>Official Trailer</h3>
                    <div class="video-wrapper">
                        <iframe src="<?php echo htmlspecialchars($game['trailer_url']); ?>" title="Trailer" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="reviews-section">
                <h2>User Reviews (<?php echo count($reviews); ?>)</h2>

                <?php if ($user_id): ?>

                    <?php if (!$already_reviewed): ?>
                        
                        <div class="review-form-container">
                            <h3>Write a Review</h3>
                            <form action="" method="POST" class="review-form" enctype="multipart/form-data" onsubmit="if(this.submitted) return false; this.submitted = true; return true;">
                                
                                <div class="form-group">
                                    <label for="rating">Rating (1-10):</label>
                                    <select name="rating" id="rating" required>
                                        <option value="10">10 - Masterpiece</option>
                                        <option value="9">9 - Amazing</option>
                                        <option value="8">8 - Great</option>
                                        <option value="7">7 - Good</option>
                                        <option value="6">6 - Fine</option>
                                        <option value="5">5 - Average</option>
                                        <option value="1">1 - Terrible</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <textarea name="content" rows="4" placeholder="Share your thoughts..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="review_images" class="file-upload-label">
                                        <span class="file-upload-icon">📷</span> Attach Screenshots (optional)
                                    </label>
                                    <input type="file" name="review_images[]" id="review_images" accept="image/*" multiple class="file-input-field">
                                </div>

                                <button type="submit" name="submit_review" class="btn-register">Post Review</button>
                            </form>
                        </div>

                    <?php else: ?>
                        
                        <div class="review-form-container review-success">
                            <h3>Thanks for your feedback!</h3>
                            <p>You have already reviewed this game.</p>
                        </div>

                    <?php endif; ?> 

                <?php else: ?>

                    <p class="login-prompt">Please <a href="login.php">login</a> to write a review.</p>

                <?php endif; ?>

                
                <div class="reviews-list">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-meta">
                                        <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                        <span class="review-rating">⭐ <?php echo $review['rating']; ?>/10</span>
                                    </div>
                                    
                                    <?php if (($user_id && $user_id == $review['user_id']) || $is_admin): ?>
                                        <div class="review-actions">
                                            
                                            <?php if ($user_id == $review['user_id']): ?>
                                                <a href="edit-review.php?id=<?php echo $review['id']; ?>" class="btn-edit">Edit</a>
                                            <?php endif; ?>

                                            <form action="" method="POST" class="delete-form" onsubmit="return confirm('Delete review?');">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <button type="submit" name="delete_review" class="btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="review-date">
                                    <?php echo date("F j, Y", strtotime($review['created_at'])); ?>
                                </div>
                                
                                <div class="review-text">
                                    <?php echo nl2br(htmlspecialchars($review['content'])); ?>
                                </div>

                                <?php if (!empty($review['image_path'])): ?>
                                    <div class="review-gallery">
                                        <?php 
                                            $images = json_decode($review['image_path']);
                                            if (!is_array($images)) {
                                                $images = [$review['image_path']];
                                            }
                                        ?>
                                        <?php foreach ($images as $img_src): ?>
                                            <a href="<?php echo htmlspecialchars($img_src); ?>" target="_blank">
                                                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="User screenshot" class="review-img">
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-reviews-message">No reviews yet. Be the first!</p>
                    <?php endif; ?>
                </div>

            </div>
        </div> 
    </main>

    <footer>
        <div class="container">
            <p>&copy; Game-Reviews 2025</p>
        </div>
    </footer>
</body>
</html>
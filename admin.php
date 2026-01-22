<?php
/**
 * Admin Dashboard - Manage Games.
 *
 * Accessible only to users with 'is_admin' = 1.
 * Provides a form to add new games to the database.
 * Handles automatic YouTube link conversion (watch?v= -> embed/).
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
require_once 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

// Handle Add Game
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $title = trim($_POST['title']);
    $rating = floatval($_POST['rating']);
    $image_url = trim($_POST['image_url']);
    $description = trim($_POST['description']);
    $trailer_url = trim($_POST['trailer_url']);

    if (!empty($title) && !empty($image_url)) {
        // Auto-convert YouTube link
        $trailer_url = str_replace("watch?v=", "embed/", $trailer_url);
        // Remove timecode parameters if present (&t=...)
        $trailer_url = preg_replace('/&.*/', '', $trailer_url);

        $sql = "INSERT INTO games (title, description, image_url, rating, trailer_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$title, $description, $image_url, $rating, $trailer_url])) {
            $success_msg = "Game '$title' added successfully!";
        } else {
            $error_msg = "Error adding game.";
        }
    } else {
        $error_msg = "Title and Image URL are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    GameCorner <span class="admin-logo-badge">(ADMIN)</span>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="nav-link">Back to Site</a></li>
                    <li><a href="logout.php" class="btn-logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container admin-container-narrow">
            
            <h2 class="page-title">Admin Dashboard</h2>

            <a href="admin-users.php" class="admin-nav-link">← Manage Users</a>

            <?php if ($success_msg): ?>
                <div class="review-form-container review-success" style="margin-bottom: 20px; padding: 20px;">
                    <h3 style="margin: 0; font-size: 18px;"><?php echo $success_msg; ?></h3>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="review-form-container" style="border-top-color: #ff4444; margin-bottom: 20px; padding: 20px; text-align: center; color: #ff4444;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="review-form-container">
                <h3>Add New Game</h3>
                
                <form action="" method="POST" class="review-form">
                    
                    <div class="form-group">
                        <label>Game Title:</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="form-group">
                        <label>Image URL (Poster):</label>
                        <input type="text" name="image_url" required placeholder="https://...">
                    </div>

                    <div class="form-group">
                        <label>Critic Rating (0-10):</label>
                        <input type="number" name="rating" step="0.1" min="0" max="10" required>
                    </div>

                    <div class="form-group">
                        <label>Trailer URL (YouTube):</label>
                        <input type="text" name="trailer_url" placeholder="https://www.youtube.com/watch?v=...">
                    </div>

                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" rows="5" required></textarea>
                    </div>

                    <button type="submit" name="add_game" class="btn-register">Add Game to Database</button>

                </form>
            </div>

        </div>
    </main>

</body>
</html>
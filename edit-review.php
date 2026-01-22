<?php
/**
 * Edit Review Logic.
 *
 * Allows the author of a review to update their rating, text content, and images.
 * Handles deletion of specific images and uploading of new ones.
 * Enforces ownership check (only the author can edit).
 *
 * @package GameCorner
 * @author  Cirael
 */
session_start();
require_once 'db.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$review_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch Review
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
$stmt->execute([$review_id, $user_id]);
$review = $stmt->fetch();

if (!$review) {
    die("Review not found or access denied.");
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $content = trim($_POST['content']);

    // --- IMAGE HANDLING START ---
    $current_images_json = $review['image_path'];
    $kept_images = [];

    // Decode existing images
    $old_images_list = [];
    if (!empty($current_images_json)) {
        $decoded = json_decode($current_images_json);
        $old_images_list = is_array($decoded) ? $decoded : [$current_images_json];
    }

    $delete_list = isset($_POST['delete_images']) ? $_POST['delete_images'] : [];

    // Process deletions
    foreach ($old_images_list as $img_path) {
        if (in_array($img_path, $delete_list)) {
            // Delete file from server
            if (file_exists($img_path)) {
                unlink($img_path); 
            }
        } else {
            // Keep file
            $kept_images[] = $img_path;
        }
    }

    // Process new uploads
    $new_uploaded_paths = [];
    if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);

        foreach ($_FILES['new_images']['name'] as $key => $filename) {
            $file_tmp = $_FILES['new_images']['tmp_name'][$key];
            $file_error = $_FILES['new_images']['error'][$key];

            if ($file_error === 0) {
                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($file_ext, $allowed)) {
                    $new_name = uniqid('review_') . '_edit_' . $key . '.' . $file_ext;
                    $destination = 'uploads/' . $new_name;

                    if (move_uploaded_file($file_tmp, $destination)) {
                        $new_uploaded_paths[] = $destination;
                    }
                }
            }
        }
    }

    // Merge and save
    $final_images_list = array_merge($kept_images, $new_uploaded_paths);
    $final_json = (count($final_images_list) > 0) ? json_encode($final_images_list) : null;
    // --- IMAGE HANDLING END ---

    if ($rating >= 1 && $rating <= 10 && !empty($content)) {
        $sql = "UPDATE reviews SET rating = ?, content = ?, image_path = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rating, $content, $final_json, $review_id]);

        header("Location: game.php?id=" . $review['game_id']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Review</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    
    <script src="edit-review.js" defer></script>
</head>
<body>

    <header class="main-header">
        <div class="container">
            <div class="logo"><a href="index.php">GameCorner</a></div>
        </div>
    </header>

    <main>
        <div class="container" style="max-width: 600px; margin-top: 50px;">
            
            <div class="review-form-container">
                <h3>Edit your review</h3>
                
                <form action="" method="POST" class="review-form" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label for="rating">Rating (1-10):</label>
                        <select name="rating" id="rating" required>
                            <?php for($i=10; $i>=1; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($i == $review['rating']) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content">Review:</label>
                        <textarea name="content" id="content" rows="6" required><?php echo htmlspecialchars($review['content']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label style="color: #bbb; display:block; margin-bottom: 8px;">Images:</label>

                        <?php 
                            $images = [];
                            if ($review['image_path']) {
                                $decoded = json_decode($review['image_path']);
                                $images = is_array($decoded) ? $decoded : [$review['image_path']];
                            }
                        ?>

                        <?php if (count($images) > 0): ?>
                            <div class="edit-gallery">
                                <?php foreach ($images as $img_src): ?>
                                    <div class="edit-image-item">
                                        <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Review img">
                                        <label class="delete-checkbox-label">
                                            <input type="checkbox" name="delete_images[]" value="<?php echo htmlspecialchars($img_src); ?>">
                                            Delete
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <label for="new_images" class="file-upload-label">
                            <span class="file-upload-icon">📷</span> Add new images
                        </label>
                        <input type="file" name="new_images[]" id="new_images" accept="image/*" multiple class="file-input-field">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-register">Save Changes</button>
                        <a href="game.php?id=<?php echo $review['game_id']; ?>" class="btn-cancel">Cancel</a>
                    </div>

                </form>
            </div>

        </div>
    </main>

</body>
</html>
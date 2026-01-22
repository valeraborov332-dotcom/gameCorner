<?php
/**
 * Admin Dashboard - Manage Users.
 *
 * Accessible only to users with 'is_admin' = 1.
 * Displays a list of all registered users.
 * Allows promoting regular users to Admins and demoting Admins.
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

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = intval($_POST['user_id']);
    $new_role = intval($_POST['new_role']);

    if ($target_user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->execute([$new_role, $target_user_id]);
    }
    header("Location: admin-users.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
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
                    <li><a href="admin.php" class="nav-link">Manage Games</a></li>
                    <li><a href="index.php" class="nav-link">Back to Site</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container admin-container">
            <div class="review-form-container">
                <h3>Manage Users</h3>
                
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if ($u['is_admin']): ?>
                                        <span class="role-badge admin">Admin</span>
                                    <?php else: ?>
                                        <span class="role-badge user">User</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" class="user-action-form">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            
                                            <?php if ($u['is_admin']): ?>
                                                <input type="hidden" name="new_role" value="0">
                                                <button type="submit" class="btn-edit btn-action-demote">Demote</button>
                                            <?php else: ?>
                                                <input type="hidden" name="new_role" value="1">
                                                <button type="submit" class="btn-edit btn-action-promote">Promote</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <span class="current-user-label">(You)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
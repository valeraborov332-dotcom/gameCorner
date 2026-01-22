<?php
/**
 * AJAX Search API Endpoint.
 *
 * Receives a search query via GET request.
 * Returns a JSON array of matching games (id, title, image, rating).
 * Used by the JavaScript live search feature in the header.
 *
 * @package GameCorner
 * @author  Cirael
 */
require_once 'db.php';

if (!isset($_GET['query'])) exit;

$query = trim($_GET['query']);

if (strlen($query) > 0) {
    $sql = "SELECT g.id, g.title, g.rating, g.image_url, AVG(r.rating) as user_rating 
            FROM games g 
            LEFT JOIN reviews r ON g.id = r.game_id 
            WHERE g.title LIKE ? 
            GROUP BY g.id 
            LIMIT 5";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
}
?>
<?php
/**
 * AJAX Toggle Favorite Endpoint
 * Hidden Places Explorer
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

if (!is_user_logged_in()) {
    echo json_encode([
        'status' => 'unauthorized',
        'message' => 'Please sign in to save this destination to your favorites.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$currentUser = current_user();
$userId = (int)$currentUser['id'];
$destinationId = isset($_POST['destination_id']) ? (int)$_POST['destination_id'] : 0;

if ($destinationId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid destination ID.']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Verify destination exists
    $checkDest = $pdo->prepare("SELECT id, title FROM destinations WHERE id = ?");
    $checkDest->execute([$destinationId]);
    $dest = $checkDest->fetch();

    if (!$dest) {
        echo json_encode(['status' => 'error', 'message' => 'Destination not found.']);
        exit;
    }

    // Check if already favorited
    $checkFav = $pdo->prepare("SELECT id FROM favourites WHERE user_id = ? AND destination_id = ?");
    $checkFav->execute([$userId, $destinationId]);
    $fav = $checkFav->fetch();

    if ($fav) {
        // Remove from favorites
        $delStmt = $pdo->prepare("DELETE FROM favourites WHERE id = ?");
        $delStmt->execute([$fav['id']]);

        echo json_encode([
            'status' => 'success',
            'action' => 'removed',
            'message' => 'Removed "' . $dest['title'] . '" from your saved places.'
        ]);
    } else {
        // Add to favorites
        $insStmt = $pdo->prepare("INSERT INTO favourites (user_id, destination_id) VALUES (?, ?)");
        $insStmt->execute([$userId, $destinationId]);

        echo json_encode([
            'status' => 'success',
            'action' => 'added',
            'message' => 'Added "' . $dest['title'] . '" to your saved places!'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database operation error: ' . $e->getMessage()
    ]);
}

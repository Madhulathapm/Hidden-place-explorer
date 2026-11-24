<?php
/**
 * Delete Destination Action
 * Hidden Places Explorer
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

require_admin_login();
$baseUrl = get_base_url();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['token'] ?? '';

if ($id <= 0 || !verify_csrf_token($token)) {
    set_flash_message('error', 'Invalid deletion request or security token.');
    header('Location: ' . $baseUrl . '/admin/destinations.php');
    exit;
}

try {
    $pdo = getDBConnection();
    // Get title for confirmation message
    $titleStmt = $pdo->prepare("SELECT title FROM destinations WHERE id = ?");
    $titleStmt->execute([$id]);
    $title = $titleStmt->fetchColumn();

    if ($title) {
        $delStmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
        $delStmt->execute([$id]);
        set_flash_message('success', 'Destination "' . $title . '" and all associated reviews/favorites have been permanently deleted.');
    } else {
        set_flash_message('error', 'Destination not found.');
    }
} catch (Exception $e) {
    set_flash_message('error', 'Database deletion error: ' . $e->getMessage());
}

header('Location: ' . $baseUrl . '/admin/destinations.php');
exit;

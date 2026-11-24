<?php
/**
 * Admin Panel Common Header
 * Hidden Places Explorer
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

require_admin_login();
$currentAdmin = current_admin();
$baseUrl = get_base_url();
$csrfToken = generate_csrf_token();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

// Quick badge counts
$unreadContactsCount = 0;
$pendingReviewsCount = 0;
try {
    $pdo = getDBConnection();
    $unreadContactsCount = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread'")->fetchColumn();
    $pendingReviewsCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    // Graceful
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | Admin Console' : 'Explorer Control Room' ?></title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Base App CSS + Admin CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <i class="fas fa-compass"></i>
        <span>Hidden Places Admin</span>
    </div>

    <ul class="admin-nav">
        <li class="admin-nav-item">
            <a href="<?= $baseUrl ?>/admin/dashboard.php" class="admin-nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="<?= $baseUrl ?>/admin/destinations.php" class="admin-nav-link <?= in_array($currentPage, ['destinations', 'destination-add', 'destination-edit']) ? 'active' : '' ?>">
                <i class="fas fa-map-marked-alt"></i>
                <span>Destinations</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="<?= $baseUrl ?>/admin/categories.php" class="admin-nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>">
                <i class="fas fa-layer-group"></i>
                <span>Categories</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="<?= $baseUrl ?>/admin/users.php" class="admin-nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Explorers / Users</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="<?= $baseUrl ?>/admin/reviews.php" class="admin-nav-link <?= $currentPage === 'reviews' ? 'active' : '' ?>">
                <i class="fas fa-star"></i>
                <span>Reviews</span>
                <?php if ($pendingReviewsCount > 0): ?>
                    <span class="admin-badge-count" style="background: #e09f3e;"><?= $pendingReviewsCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="<?= $baseUrl ?>/admin/contacts.php" class="admin-nav-link <?= $currentPage === 'contacts' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i>
                <span>Inquiries</span>
                <?php if ($unreadContactsCount > 0): ?>
                    <span class="admin-badge-count"><?= $unreadContactsCount ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>

    <div class="admin-sidebar-footer">
        <a href="<?= $baseUrl ?>/index.php" target="_blank" class="admin-nav-link" style="color: #92aba0; font-size: 0.85rem; margin-bottom: 6px;">
            <i class="fas fa-external-link-alt"></i>
            <span>View Public Site</span>
        </a>
        <a href="<?= $baseUrl ?>/admin/logout.php" class="admin-nav-link" style="color: #f87171; font-size: 0.85rem;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Admin Logout</span>
        </a>
    </div>
</aside>

<!-- Admin Main Container -->
<div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
        <div style="display: flex; align-items: center; gap: 16px;">
            <button type="button" class="admin-mobile-toggle" aria-label="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="admin-topbar-title"><?= isset($pageTitle) ? e($pageTitle) : 'Admin Console' ?></h1>
        </div>

        <div class="admin-topbar-user">
            <div class="admin-user-info">
                <div class="admin-user-name"><?= e($currentAdmin['name']) ?></div>
                <div class="admin-user-role"><?= e($currentAdmin['role']) ?></div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #166534; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?= strtoupper(substr($currentAdmin['name'], 0, 1)) ?>
            </div>
        </div>
    </header>

    <!-- Flash message notice inside Admin -->
    <?= render_flash_messages() ?>

    <div class="admin-content">

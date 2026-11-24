<?php
/**
 * Common Public Header
 * Hidden Places Explorer
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$currentUser = current_user();
$baseUrl = get_base_url();
$csrfToken = generate_csrf_token();

// Fetch favorites count for logged in user
$favoritesCount = 0;
if ($currentUser) {
    try {
        $pdo = getDBConnection();
        $stmtFavCount = $pdo->prepare("SELECT COUNT(*) FROM favourites WHERE user_id = ?");
        $stmtFavCount->execute([$currentUser['id']]);
        $favoritesCount = (int)$stmtFavCount->fetchColumn();
    } catch (Exception $e) {
        $favoritesCount = 0;
    }
}

// Current page identifier for active class
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | Hidden Places Explorer' : 'Hidden Places Explorer - Discover Earth’s Secret Gems' ?></title>
    
    <!-- Google Fonts & Font Awesome 6 Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main Custom CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<body data-base-url="<?= $baseUrl ?>" class="<?= isset($bodyClass) ? e($bodyClass) : '' ?>">

<!-- Header & Navigation -->
<header class="site-header">
    <div class="container">
        <nav class="navbar">
            <a href="<?= $baseUrl ?>/index.php" class="brand-logo">
                <i class="fas fa-compass"></i>
                <span>Hidden Places</span>
            </a>

            <!-- Navigation Links -->
            <ul class="nav-menu">
                <li><a href="<?= $baseUrl ?>/index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= $baseUrl ?>/destinations.php" class="nav-link <?= $currentPage === 'destinations' || $currentPage === 'destination' ? 'active' : '' ?>">Explore Gems</a></li>
                <li><a href="<?= $baseUrl ?>/categories.php" class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>">Categories</a></li>
                <li><a href="<?= $baseUrl ?>/contact.php" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
            </ul>

            <!-- Auth / Action Buttons -->
            <div class="nav-actions">
                <?php if ($currentUser): ?>
                    <!-- Logged In User Dropdown -->
                    <div class="user-dropdown">
                        <button type="button" class="user-dropdown-btn">
                            <div class="user-avatar-badge">
                                <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                            </div>
                            <span><?= e(explode(' ', $currentUser['name'])[0]) ?></span>
                            <i class="fas fa-chevron-down" style="font-size: 0.75rem; opacity: 0.8;"></i>
                        </button>
                        <ul class="user-dropdown-menu">
                            <li class="dropdown-header">
                                <div class="dropdown-user-name"><?= e($currentUser['name']) ?></div>
                                <div class="dropdown-user-email"><?= e($currentUser['email']) ?></div>
                            </li>
                            <li>
                                <a href="<?= $baseUrl ?>/favorites.php">
                                    <i class="fas fa-heart text-danger"></i>
                                    <span>Saved Places</span>
                                    <?php if ($favoritesCount > 0): ?>
                                        <span class="badge-status badge-approved" style="margin-left:auto; font-size:0.7rem; padding: 2px 6px;"><?= $favoritesCount ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $baseUrl ?>/profile.php">
                                    <i class="fas fa-user-circle"></i>
                                    <span>My Profile</span>
                                </a>
                            </li>
                            <li style="border-top: 1px solid #f0eee6; margin-top: 4px;">
                                <a href="<?= $baseUrl ?>/logout.php" style="color: var(--color-danger);">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Sign Out</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest Buttons -->
                    <a href="<?= $baseUrl ?>/login.php" class="btn btn-outline-white btn-sm">Sign In</a>
                    <a href="<?= $baseUrl ?>/register.php" class="btn btn-accent btn-sm">Join Free</a>
                <?php endif; ?>

                <!-- Mobile Nav Toggle -->
                <button type="button" class="mobile-nav-toggle" aria-label="Toggle Navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </div>
</header>

<!-- Flash Alerts Container -->
<?= render_flash_messages() ?>
<main>

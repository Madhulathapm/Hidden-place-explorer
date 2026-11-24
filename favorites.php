<?php
/**
 * User Saved Destinations (Favorites) Page
 * Hidden Places Explorer
 */
$pageTitle = "My Saved Hidden Places";
$bodyClass = "page-favorites";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

require_user_login();
$currentUser = current_user();
$pdo = getDBConnection();

// Fetch favorited destinations
$stmt = $pdo->prepare("
    SELECT d.*, c.name AS category_name, c.slug AS category_slug, f.created_at AS saved_at,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM favourites f
    JOIN destinations d ON f.destination_id = d.id
    JOIN categories c ON d.category_id = c.id
    LEFT JOIN reviews r ON d.id = r.destination_id AND r.status = 'approved'
    WHERE f.user_id = ?
    GROUP BY d.id
    ORDER BY f.id DESC
");
$stmt->execute([$currentUser['id']]);
$favorites = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div style="background: var(--color-primary-dark); color: var(--color-white); padding: 50px 0 60px;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <i class="fas fa-heart text-danger" style="font-size: 1.6rem;"></i>
            <h1 style="font-size: 2.8rem; font-family: var(--font-serif); color: var(--color-white); margin: 0;">
                My Saved Places
            </h1>
        </div>
        <p style="color: #cbd8d1; font-size: 1.1rem; max-width: 600px;">
            Your personal collection of remote sanctuaries, waterfalls, and uncharted routes.
        </p>
    </div>
</div>

<div class="container section">
    <?php if (empty($favorites)): ?>
        <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 60px 30px; text-align: center; max-width: 600px; margin: 0 auto; box-shadow: var(--shadow-sm);">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px;">
                <i class="far fa-heart"></i>
            </div>
            <h2 style="font-size: 1.6rem; color: var(--color-primary-dark); margin-bottom: 10px;">No Saved Places Yet</h2>
            <p style="color: var(--color-text-muted); font-size: 1rem; line-height: 1.6; margin-bottom: 24px;">
                Whenever you discover a secluded cave, wild canyon, or emerald waterfall you love, click the heart icon on any card to store it here for your future journeys.
            </p>
            <a href="<?= $baseUrl ?>/destinations.php" class="btn btn-primary btn-lg">
                <i class="fas fa-compass"></i> Discover Hidden Places
            </a>
        </div>
    <?php else: ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <p style="color: var(--color-text-muted); font-size: 1.05rem; margin: 0;">
                You have saved <strong><?= count($favorites) ?></strong> secret <?= count($favorites) === 1 ? 'place' : 'places' ?> to your wanderlust list.
            </p>
            <a href="<?= $baseUrl ?>/destinations.php" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-plus"></i> Find More Gems
            </a>
        </div>

        <div class="destinations-grid">
            <?php foreach ($favorites as $dest): 
                $diffClass = 'difficulty-' . strtolower($dest['difficulty']);
            ?>
                <div class="gem-card">
                    <div class="gem-card-header">
                        <img src="<?= e($dest['image_url']) ?>" alt="<?= e($dest['title']) ?>" class="gem-card-img" loading="lazy">
                        <span class="gem-badge-category"><?= e($dest['category_name']) ?></span>
                        <span class="gem-badge-difficulty <?= $diffClass ?>"><?= e($dest['difficulty']) ?></span>
                        
                        <!-- AJAX Favorite Toggle (Active) -->
                        <button type="button" class="fav-toggle-btn is-active" 
                                data-id="<?= $dest['id'] ?>" 
                                data-csrf="<?= e($csrfToken) ?>"
                                aria-label="Remove from favorites"
                                title="Remove from favorites">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>

                    <div class="gem-card-body">
                        <div class="gem-rating-row">
                            <span class="gem-card-location">
                                <i class="fas fa-map-marker-alt text-gold"></i> <?= e($dest['country']) ?>
                            </span>
                            <?= render_stars((float)$dest['avg_rating']) ?>
                        </div>

                        <h3 class="gem-card-title">
                            <a href="<?= $baseUrl ?>/destination.php?id=<?= $dest['id'] ?>"><?= e($dest['title']) ?></a>
                        </h3>
                        <p class="gem-card-tagline"><?= e($dest['tagline']) ?></p>

                        <div class="gem-card-footer">
                            <span class="gem-season-badge">
                                <i class="far fa-calendar-alt"></i> <?= e($dest['best_season']) ?>
                            </span>
                            <a href="<?= $baseUrl ?>/destination.php?id=<?= $dest['id'] ?>" class="btn btn-primary btn-sm">
                                View Place <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

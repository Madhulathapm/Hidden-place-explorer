<?php
/**
 * Destination Details Page
 * Hidden Places Explorer
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

$pdo = getDBConnection();
$currentUser = current_user();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . get_base_url() . '/destinations.php');
    exit;
}

// Increment views count
$pdo->prepare("UPDATE destinations SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

// Fetch destination details
$stmt = $pdo->prepare("
    SELECT d.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
    FROM destinations d
    JOIN categories c ON d.category_id = c.id
    WHERE d.id = ?
");
$stmt->execute([$id]);
$dest = $stmt->fetch();

if (!$dest) {
    header('Location: ' . get_base_url() . '/destinations.php');
    exit;
}

$pageTitle = $dest['title'];

// Decode gallery images
$gallery = [];
if (!empty($dest['gallery'])) {
    $gallery = json_decode($dest['gallery'], true) ?: [];
}

// Handle New Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$currentUser) {
        set_flash_message('error', 'You must be logged in to post a review.');
        header("Location: destination.php?id={$id}#reviews-section");
        exit;
    }

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Invalid security token. Please try again.');
        header("Location: destination.php?id={$id}#reviews-section");
        exit;
    }

    $rating = (int)($_POST['rating'] ?? 5);
    $reviewTitle = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        set_flash_message('error', 'Please choose a rating between 1 and 5 stars.');
    } elseif (empty($comment)) {
        set_flash_message('error', 'Please write your review thoughts before submitting.');
    } else {
        $stmtRev = $pdo->prepare("
            INSERT INTO reviews (destination_id, user_id, rating, title, comment, status)
            VALUES (?, ?, ?, ?, ?, 'approved')
        ");
        $stmtRev->execute([$id, $currentUser['id'], $rating, $reviewTitle, $comment]);
        set_flash_message('success', 'Your review has been shared with fellow explorers!');
        header("Location: destination.php?id={$id}#reviews-section");
        exit;
    }
}

// Fetch Reviews & Ratings
$stmtReviews = $pdo->prepare("
    SELECT r.*, u.name AS user_name, u.avatar AS user_avatar
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.destination_id = ? AND r.status = 'approved'
    ORDER BY r.id DESC
");
$stmtReviews->execute([$id]);
$reviews = $stmtReviews->fetchAll();

$totalReviews = count($reviews);
$avgRating = 5.0;
if ($totalReviews > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avgRating = $sum / $totalReviews;
}

$isFav = is_favorited($id, $currentUser ? (int)$currentUser['id'] : null, $pdo);
$diffClass = 'difficulty-' . strtolower($dest['difficulty']);

include __DIR__ . '/includes/header.php';
?>

<!-- Destination Detail Hero -->
<section class="detail-hero">
    <img src="<?= e($dest['image_url']) ?>" alt="<?= e($dest['title']) ?>" class="detail-hero-img">
    <div class="detail-hero-overlay">
        <div class="container">
            <div class="detail-hero-content">
                <span class="detail-location-pill">
                    <i class="fas fa-map-marker-alt text-gold"></i> <?= e($dest['location']) ?>, <?= e($dest['country']) ?>
                </span>
                <h1 class="detail-title"><?= e($dest['title']) ?></h1>
                <p style="font-size: 1.25rem; color: #e1ede6; max-width: 800px; margin-bottom: 20px;">
                    <?= e($dest['tagline']) ?>
                </p>
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.4); padding: 8px 16px; border-radius: var(--radius-pill);">
                        <?= render_stars((float)$avgRating) ?>
                        <span style="color: #ffffff; font-size: 0.85rem; font-weight: 600;">(<?= $totalReviews ?> <?= $totalReviews === 1 ? 'Review' : 'Reviews' ?>)</span>
                    </div>

                    <!-- AJAX Favorite Button -->
                    <button type="button" class="btn btn-accent fav-toggle-btn-inline <?= $isFav ? 'is-active' : '' ?>"
                            onclick="document.querySelector('.fav-toggle-btn').click();">
                        <i class="<?= $isFav ? 'fas' : 'far' ?> fa-heart"></i>
                        <span><?= $isFav ? 'Saved to Favorites' : 'Save Destination' ?></span>
                    </button>
                    <!-- Hidden button for standard trigger -->
                    <button type="button" class="fav-toggle-btn <?= $isFav ? 'is-active' : '' ?>" 
                            data-id="<?= $dest['id'] ?>" 
                            data-csrf="<?= e($csrfToken) ?>" 
                            style="display:none;"></button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Specs Metric Bar -->
<div class="container">
    <div class="detail-specs-bar">
        <div class="spec-item">
            <div class="spec-icon"><i class="fas fa-mountain"></i></div>
            <div>
                <div class="spec-label">Terrain Difficulty</div>
                <div class="spec-value"><?= e($dest['difficulty']) ?></div>
            </div>
        </div>
        <div class="spec-item">
            <div class="spec-icon"><i class="far fa-calendar-check"></i></div>
            <div>
                <div class="spec-label">Prime Season</div>
                <div class="spec-value"><?= e($dest['best_season']) ?></div>
            </div>
        </div>
        <div class="spec-item">
            <div class="spec-icon"><i class="far fa-clock"></i></div>
            <div>
                <div class="spec-label">Duration</div>
                <div class="spec-value"><?= e($dest['estimated_duration'] ?? '1-2 Days') ?></div>
            </div>
        </div>
        <div class="spec-item">
            <div class="spec-icon"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="spec-label">Biome Realm</div>
                <div class="spec-value"><?= e($dest['category_name']) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Destination Details & Sidebar -->
<div class="container" style="padding-bottom: 80px;">
    <div class="detail-grid">
        <!-- Main Description & Details -->
        <div>
            <h2 style="font-size: 1.8rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin-bottom: 16px;">
                About This Sacred Landmark
            </h2>
            <div class="detail-prose">
                <p><?= nl2br(e($dest['description'])) ?></p>
            </div>

            <!-- Travel Tips Box -->
            <?php if (!empty($dest['travel_tips'])): ?>
                <div class="detail-tips-box">
                    <h3 class="detail-tips-title">
                        <i class="fas fa-shield-alt text-gold"></i> Essential Field &amp; Safety Notes
                    </h3>
                    <p style="color: #2e473b; font-size: 0.95rem; line-height: 1.6;">
                        <?= nl2br(e($dest['travel_tips'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Photo Gallery (if available) -->
            <?php if (!empty($gallery)): ?>
                <h3 style="font-size: 1.4rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin: 30px 0 16px;">
                    Expedition Gallery
                </h3>
                <div class="detail-gallery">
                    <img src="<?= e($dest['image_url']) ?>" alt="<?= e($dest['title']) ?>" onclick="window.open(this.src, '_blank')">
                    <?php foreach ($gallery as $galImg): ?>
                        <img src="<?= e($galImg) ?>" alt="Gallery Image" onclick="window.open(this.src, '_blank')">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Reviews & Ratings Section -->
            <section class="reviews-section" id="reviews-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                    <h2 style="font-size: 1.8rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin: 0;">
                        Explorer Field Reviews
                    </h2>
                    <a href="#leave-review-card" class="btn btn-outline-dark btn-sm">Write Review</a>
                </div>

                <!-- Rating summary score card -->
                <div class="reviews-summary-card">
                    <div class="rating-big-score">
                        <div class="score-num"><?= number_format($avgRating, 1) ?></div>
                        <?= render_stars((float)$avgRating) ?>
                        <div class="score-count">Based on <?= $totalReviews ?> <?= $totalReviews === 1 ? 'Explorer' : 'Explorers' ?></div>
                    </div>
                    <div style="flex: 1; border-left: 1px solid var(--color-cream-border); padding-left: 30px;">
                        <h4 style="font-size: 1.05rem; margin-bottom: 6px; color: var(--color-primary-dark);">Community Verified</h4>
                        <p style="font-size: 0.9rem; color: var(--color-text-muted); margin: 0;">
                            Reviews are submitted by verified community members who have visited or documented this location.
                        </p>
                    </div>
                </div>

                <!-- Review list -->
                <div class="review-list">
                    <?php if (empty($reviews)): ?>
                        <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 30px; text-align: center; color: var(--color-text-muted);">
                            <i class="far fa-comment-dots" style="font-size: 2rem; margin-bottom: 10px; color: var(--color-accent);"></i>
                            <p style="margin: 0;">No reviews recorded yet for this hidden gem. Be the first adventurer to share your dispatch!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-item-card">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-avatar">
                                            <?= strtoupper(substr($rev['user_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="reviewer-name"><?= e($rev['user_name']) ?></div>
                                            <div class="review-date"><?= date('F j, Y', strtotime($rev['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <?= render_stars((float)$rev['rating']) ?>
                                </div>
                                <?php if (!empty($rev['title'])): ?>
                                    <div class="review-title">"<?= e($rev['title']) ?>"</div>
                                <?php endif; ?>
                                <p class="review-body"><?= nl2br(e($rev['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Review Card -->
                <div class="add-review-card" id="leave-review-card">
                    <h3 style="font-size: 1.35rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin-bottom: 8px;">
                        Add Your Field Dispatch
                    </h3>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 20px;">
                        Share your tips, trail condition observations, and experiences to assist future explorers.
                    </p>

                    <?php if ($currentUser): ?>
                        <form action="destination.php?id=<?= $id ?>" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="form-group">
                                <label class="form-label">Your Rating (1 to 5 Stars):</label>
                                <div class="star-picker">
                                    <input type="radio" id="star5" name="rating" value="5" checked><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                                </div>
                                <div id="selected-rating-text" style="font-size: 0.85rem; font-weight: 600; color: var(--color-primary); margin-top: -10px; margin-bottom: 16px;">
                                    5 Stars - Truly Extraordinary Experience
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="review-title" class="form-label">Headline / Summary</label>
                                <input type="text" id="review-title" name="title" class="form-control" placeholder="e.g. Magnificent twilight views, bring sturdy boots" required>
                            </div>

                            <div class="form-group">
                                <label for="review-comment" class="form-label">Detailed Notes &amp; Observations</label>
                                <textarea id="review-comment" name="comment" class="form-control" rows="4" placeholder="Detail the trail conditions, access permits, lighting, and any environmental precautions..." required></textarea>
                            </div>

                            <button type="submit" name="submit_review" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Review
                            </button>
                        </form>
                    <?php else: ?>
                        <div style="background: var(--color-cream-card); padding: 20px; border-radius: var(--radius-sm); border: 1px solid var(--color-cream-border);">
                            <p style="margin-bottom: 12px; color: var(--color-text-main); font-weight: 500;">
                                Please log in with your explorer account to submit a review and rating.
                            </p>
                            <a href="<?= $baseUrl ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-sign-in-alt"></i> Sign In to Review
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Sidebar / Interactive Map & Navigator -->
        <aside>
            <!-- Google Maps Card -->
            <div class="map-widget-card">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--color-cream-border); background: #fdfbf7;">
                    <h3 style="font-size: 1.1rem; color: var(--color-primary-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-map-marked-alt text-gold"></i> Geographic Location
                    </h3>
                </div>

                <div class="map-embed-wrapper">
                    <iframe src="<?= e($dest['map_embed_url']) ?>" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

                <div class="map-widget-body">
                    <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 14px;">
                        <strong>Coordinates:</strong> <?= e($dest['latitude'] ?? 'N/A') ?>, <?= e($dest['longitude'] ?? 'N/A') ?>
                    </p>
                    <a href="<?= e($dest['map_link']) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-block">
                        <i class="fas fa-directions"></i> Open in Google Maps
                    </a>
                </div>
            </div>

            <!-- Fast Explorer Actions Card -->
            <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm);">
                <h4 style="font-size: 1.05rem; color: var(--color-primary-dark); margin-bottom: 14px;">Explorer Actions</h4>
                
                <button type="button" class="btn btn-outline-dark btn-block fav-btn-sidebar" onclick="document.querySelector('.fav-toggle-btn').click();" style="margin-bottom: 10px;">
                    <i class="<?= $isFav ? 'fas fa-heart text-danger' : 'far fa-heart' ?>"></i> 
                    <?= $isFav ? 'Remove from Saved' : 'Add to My Saved Places' ?>
                </button>

                <a href="<?= $baseUrl ?>/destinations.php?category=<?= $dest['category_id'] ?>" class="btn btn-outline-white btn-block" style="background: var(--color-cream-alt); color: var(--color-primary); border-color: var(--color-cream-border);">
                    <i class="fas fa-compass"></i> More in <?= e($dest['category_name']) ?>
                </a>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

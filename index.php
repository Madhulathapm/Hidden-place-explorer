<?php
/**
 * Home Page
 * Hidden Places Explorer
 */
$pageTitle = "Discover Earth's Most Awe-Inspiring Hidden Gems";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

$pdo = getDBConnection();
$currentUser = current_user();

// 1. Fetch Categories with destination count
$stmtCat = $pdo->query("
    SELECT c.*, COUNT(d.id) AS destination_count 
    FROM categories c 
    LEFT JOIN destinations d ON c.id = d.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC 
    LIMIT 6
");
$categories = $stmtCat->fetchAll();

// 2. Fetch Featured & Top Destinations
$stmtFeatured = $pdo->query("
    SELECT d.*, c.name AS category_name, c.slug AS category_slug,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM destinations d
    JOIN categories c ON d.category_id = c.id
    LEFT JOIN reviews r ON d.id = r.destination_id AND r.status = 'approved'
    WHERE d.is_featured = 1
    GROUP BY d.id
    ORDER BY d.views_count DESC
    LIMIT 6
");
$featuredDestinations = $stmtFeatured->fetchAll();

// If fewer than 6 featured, grab recent destinations to complete grid
if (count($featuredDestinations) < 6) {
    $stmtExtra = $pdo->query("
        SELECT d.*, c.name AS category_name, c.slug AS category_slug,
               COALESCE(AVG(r.rating), 5.0) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM destinations d
        JOIN categories c ON d.category_id = c.id
        LEFT JOIN reviews r ON d.id = r.destination_id AND r.status = 'approved'
        GROUP BY d.id
        ORDER BY d.id DESC
        LIMIT 6
    ");
    $featuredDestinations = $stmtExtra->fetchAll();
}

// 3. Fetch Recent Community Reviews
$stmtReviews = $pdo->query("
    SELECT r.*, u.name AS user_name, u.avatar AS user_avatar, d.title AS destination_title, d.slug AS destination_slug
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN destinations d ON r.destination_id = d.id
    WHERE r.status = 'approved'
    ORDER BY r.id DESC
    LIMIT 3
");
$recentReviews = $stmtReviews->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-tagline-badge">
            <i class="fas fa-compass"></i> Curated Secret Sanctuaries
        </div>
        <h1 class="hero-title">
            Uncover The World's Most<br><span style="color: var(--color-gold);">Secret &amp; Sacred</span> Places
        </h1>
        <p class="hero-subtitle">
            Venture beyond the crowded tourist paths into primordial forests, subterranean caverns, and turquoise oasis canyons carefully mapped by our explorer community.
        </p>

        <!-- Search Form Card -->
        <div class="hero-search-card">
            <form action="<?= $baseUrl ?>/destinations.php" method="GET" class="hero-search-form">
                <div class="search-field-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" placeholder="Search by name, country, or canyon..." autocomplete="off">
                </div>
                <div class="search-field-group">
                    <i class="fas fa-layer-group"></i>
                    <select name="category" aria-label="Select Category">
                        <option value="">All Sacred Realms</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="search-field-group" style="max-width: 170px;">
                    <i class="fas fa-mountain"></i>
                    <select name="difficulty" aria-label="Select Difficulty">
                        <option value="">Any Terrain</option>
                        <option value="Easy">Easy</option>
                        <option value="Moderate">Moderate</option>
                        <option value="Challenging">Challenging</option>
                        <option value="Expert">Expert</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-accent btn-lg" style="flex-shrink: 0;">
                    <i class="fas fa-compass"></i> Explore Now
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Category Highlights -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-eyebrow">Explore By Biome</span>
            <h2 class="section-title">Enchanted Landscapes &amp; Sanctuaries</h2>
            <p class="section-description">
                From submerged caverns illuminated by mid-day sun to ancient windswept dragon-blood plateaus, choose your next journey.
            </p>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="<?= $baseUrl ?>/destinations.php?category=<?= $category['id'] ?>" class="category-card">
                    <img src="<?= e($category['image_url']) ?>" alt="<?= e($category['name']) ?>" class="category-card-img" loading="lazy">
                    <div class="category-card-overlay">
                        <i class="fas <?= e($category['icon'] ?? 'fa-compass') ?> category-card-icon"></i>
                        <h3 class="category-card-title"><?= e($category['name']) ?></h3>
                        <span class="category-card-count"><?= $category['destination_count'] ?> Hidden Gems Mapped</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Destinations Grid -->
<section class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; flex-wrap: wrap; gap: 20px;">
            <div class="section-header" style="margin-bottom: 0;">
                <span class="section-eyebrow">Handpicked Wonders</span>
                <h2 class="section-title">Featured Hidden Gems</h2>
                <p class="section-description">
                    Rare geographic spectacles vetted for environmental preservation and extraordinary natural beauty.
                </p>
            </div>
            <a href="<?= $baseUrl ?>/destinations.php" class="btn btn-outline-dark">
                View All Destinations <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="destinations-grid">
            <?php foreach ($featuredDestinations as $dest): 
                $isFav = is_favorited((int)$dest['id'], $currentUser ? (int)$currentUser['id'] : null, $pdo);
                $diffClass = 'difficulty-' . strtolower($dest['difficulty']);
            ?>
                <div class="gem-card">
                    <div class="gem-card-header">
                        <img src="<?= e($dest['image_url']) ?>" alt="<?= e($dest['title']) ?>" class="gem-card-img" loading="lazy">
                        <span class="gem-badge-category"><?= e($dest['category_name']) ?></span>
                        <span class="gem-badge-difficulty <?= $diffClass ?>"><?= e($dest['difficulty']) ?></span>
                        
                        <!-- AJAX Favorite Toggle -->
                        <button type="button" class="fav-toggle-btn <?= $isFav ? 'is-active' : '' ?>" 
                                data-id="<?= $dest['id'] ?>" 
                                data-csrf="<?= e($csrfToken) ?>"
                                aria-label="Save to favorites"
                                title="<?= $isFav ? 'Remove from favorites' : 'Save to favorites' ?>">
                            <i class="<?= $isFav ? 'fas' : 'far' ?> fa-heart"></i>
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
                                Explore Gem <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Explore With Us (Value Proposition) -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-eyebrow">Our Philosophy</span>
            <h2 class="section-title">The Explorer's Creed</h2>
            <p class="section-description">
                We believe true travel is about solitude, reverence for indigenous ecology, and uncovering secrets with gentle footsteps.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
            <div style="background: var(--color-white); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-cream-border); text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-primary-bg); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px;">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 12px; color: var(--color-primary-dark);">Leave No Trace</h3>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Every route and sanctuary includes strict ecological preservation advice to ensure these untouched wonderlands remain wild for future generations.
                </p>
            </div>

            <div style="background: var(--color-white); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-cream-border); text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-primary-bg); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px;">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 12px; color: var(--color-primary-dark);">Verified Coordinates</h3>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Each destination contains real GPS coordinates, high-resolution topographical maps, and practical seasonal timing guidelines.
                </p>
            </div>

            <div style="background: var(--color-white); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-cream-border); text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-primary-bg); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px;">
                    <i class="fas fa-users"></i>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 12px; color: var(--color-primary-dark);">Explorer Reviews</h3>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Authentic accounts and ratings submitted by genuine trekkers, mountaineers, and solitary explorers who have walked the terrain.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Community Testimonials / Recent Reviews -->
<?php if (!empty($recentReviews)): ?>
<section class="section">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-eyebrow">Field Notes</span>
            <h2 class="section-title">Dispatches from Fellow Explorers</h2>
            <p class="section-description">
                Real firsthand accounts from adventurers who recently discovered these secret horizons.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            <?php foreach ($recentReviews as $rev): ?>
                <div class="review-item-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">
                                <?= strtoupper(substr($rev['user_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="reviewer-name"><?= e($rev['user_name']) ?></div>
                                <div class="review-date">Reviewed <a href="<?= $baseUrl ?>/destination.php?id=<?= $rev['destination_id'] ?>" style="font-weight: 600;"><?= e($rev['destination_title']) ?></a></div>
                            </div>
                        </div>
                        <?= render_stars((float)$rev['rating']) ?>
                    </div>
                    <?php if (!empty($rev['title'])): ?>
                        <div class="review-title">"<?= e($rev['title']) ?>"</div>
                    <?php endif; ?>
                    <p class="review-body"><?= e($rev['comment']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action Banner -->
<section style="background: linear-gradient(rgba(15, 45, 32, 0.9), rgba(15, 45, 32, 0.9)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80') center/cover; padding: 90px 0; color: var(--color-white); text-align: center;">
    <div class="container">
        <span class="hero-tagline-badge" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: var(--color-white);">
            Join Our Global Circle
        </span>
        <h2 style="font-size: 2.8rem; font-family: var(--font-serif); color: var(--color-white); margin: 16px 0 20px;">
            Ready to Map Your Next Remote Adventure?
        </h2>
        <p style="font-size: 1.15rem; color: #d4e3dc; max-width: 620px; margin: 0 auto 36px;">
            Create a free explorer account to save your favorite sanctuaries, contribute field notes, and access GPS navigation coordinates.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <?php if (!is_user_logged_in()): ?>
                <a href="<?= $baseUrl ?>/register.php" class="btn btn-accent btn-lg">
                    <i class="fas fa-user-plus"></i> Create Free Account
                </a>
            <?php endif; ?>
            <a href="<?= $baseUrl ?>/destinations.php" class="btn btn-outline-white btn-lg">
                <i class="fas fa-compass"></i> Browse All Places
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

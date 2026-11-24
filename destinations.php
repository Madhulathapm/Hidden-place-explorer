<?php
/**
 * Explore Destinations Page
 * Hidden Places Explorer
 */
$pageTitle = "Explore Hidden Places & Secret Sanctuaries";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

$pdo = getDBConnection();
$currentUser = current_user();

// Fetch filter options
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Get Search & Filter parameters
$search = trim($_GET['q'] ?? '');
$categoryFilter = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$difficultyFilter = trim($_GET['difficulty'] ?? '');
$sortBy = trim($_GET['sort'] ?? 'newest');

// Build SQL Query
$sql = "
    SELECT d.*, c.name AS category_name, c.slug AS category_slug,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM destinations d
    JOIN categories c ON d.category_id = c.id
    LEFT JOIN reviews r ON d.id = r.destination_id AND r.status = 'approved'
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (d.title LIKE ? OR d.description LIKE ? OR d.location LIKE ? OR d.country LIKE ?)";
    $term = "%{$search}%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if ($categoryFilter) {
    $sql .= " AND d.category_id = ?";
    $params[] = $categoryFilter;
}

if (!empty($difficultyFilter) && in_array($difficultyFilter, ['Easy', 'Moderate', 'Challenging', 'Expert'])) {
    $sql .= " AND d.difficulty = ?";
    $params[] = $difficultyFilter;
}

$sql .= " GROUP BY d.id ";

// Sorting logic
$sql .= match($sortBy) {
    'rating' => " ORDER BY avg_rating DESC, d.views_count DESC",
    'popular' => " ORDER BY d.views_count DESC",
    'title' => " ORDER BY d.title ASC",
    default => " ORDER BY d.id DESC"
};

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div style="background: var(--color-primary-dark); color: var(--color-white); padding: 50px 0 60px;">
    <div class="container">
        <h1 style="font-size: 2.8rem; font-family: var(--font-serif); color: var(--color-white); margin-bottom: 10px;">
            Explore Hidden Gems
        </h1>
        <p style="color: #cbd8d1; font-size: 1.1rem; max-width: 600px;">
            Search through remote waterfalls, subterranean grottos, enchanted ridges, and undiscovered wonderlands.
        </p>
    </div>
</div>

<div class="container" style="padding-top: 30px; padding-bottom: 80px;">
    <div class="explore-layout">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <div class="filter-header">
                <h3 style="font-size: 1.15rem; color: var(--color-primary-dark); margin: 0;"><i class="fas fa-sliders-h"></i> Filters</h3>
                <?php if (!empty($search) || $categoryFilter || !empty($difficultyFilter)): ?>
                    <a href="<?= $baseUrl ?>/destinations.php" style="font-size: 0.85rem; color: var(--color-danger); font-weight: 600;">Clear All</a>
                <?php endif; ?>
            </div>

            <form action="<?= $baseUrl ?>/destinations.php" method="GET" id="filter-form">
                <!-- Keyword input -->
                <div class="filter-group">
                    <label class="filter-title"><i class="fas fa-search"></i> Keywords</label>
                    <input type="text" name="q" class="filter-input-search" placeholder="Search by name or country..." value="<?= e($search) ?>">
                </div>

                <!-- Category filter -->
                <div class="filter-group">
                    <label class="filter-title"><i class="fas fa-compass"></i> Categories</label>
                    <ul class="filter-options">
                        <li>
                            <label class="filter-radio-label">
                                <input type="radio" name="category" value="" <?= empty($categoryFilter) ? 'checked' : '' ?> onchange="this.form.submit()">
                                <span>All Categories</span>
                            </label>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <label class="filter-radio-label">
                                    <input type="radio" name="category" value="<?= $cat['id'] ?>" <?= $categoryFilter === (int)$cat['id'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <span><?= e($cat['name']) ?></span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Difficulty filter -->
                <div class="filter-group">
                    <label class="filter-title"><i class="fas fa-hiking"></i> Terrain Difficulty</label>
                    <ul class="filter-options">
                        <li>
                            <label class="filter-radio-label">
                                <input type="radio" name="difficulty" value="" <?= empty($difficultyFilter) ? 'checked' : '' ?> onchange="this.form.submit()">
                                <span>Any Difficulty</span>
                            </label>
                        </li>
                        <?php foreach (['Easy', 'Moderate', 'Challenging', 'Expert'] as $diff): ?>
                            <li>
                                <label class="filter-radio-label">
                                    <input type="radio" name="difficulty" value="<?= $diff ?>" <?= $difficultyFilter === $diff ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <span><?= $diff ?></span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Preserve Sort -->
                <input type="hidden" name="sort" value="<?= e($sortBy) ?>">

                <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
            </form>
        </aside>

        <!-- Main Content List -->
        <main>
            <div class="explore-results-header">
                <div>
                    <h2 style="font-size: 1.4rem; color: var(--color-primary-dark); margin: 0;">
                        Showing <?= count($destinations) ?> <?= count($destinations) === 1 ? 'Secret Gem' : 'Secret Gems' ?>
                    </h2>
                    <?php if (!empty($search)): ?>
                        <span style="font-size: 0.9rem; color: var(--color-text-muted);">Results matching: "<strong><?= e($search) ?></strong>"</span>
                    <?php endif; ?>
                </div>

                <!-- Sort dropdown -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label for="sort-select" style="font-size: 0.9rem; font-weight: 600; color: var(--color-text-muted);">Sort By:</label>
                    <select id="sort-select" class="sort-select" onchange="const form = document.getElementById('filter-form'); form.querySelector('input[name=sort]').value = this.value; form.submit();">
                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newly Discovered</option>
                        <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                        <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Most Viewed</option>
                        <option value="title" <?= $sortBy === 'title' ? 'selected' : '' ?>>Title (A-Z)</option>
                    </select>
                </div>
            </div>

            <?php if (empty($destinations)): ?>
                <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 50px 30px; text-align: center; margin-top: 20px;">
                    <div style="font-size: 3rem; color: var(--color-accent); margin-bottom: 16px;"><i class="fas fa-compass"></i></div>
                    <h3 style="font-size: 1.4rem; color: var(--color-primary-dark); margin-bottom: 8px;">No Hidden Places Found</h3>
                    <p style="color: var(--color-text-muted); max-width: 450px; margin: 0 auto 24px;">
                        No destinations matched your selected filters or keywords. Try broadening your criteria.
                    </p>
                    <a href="<?= $baseUrl ?>/destinations.php" class="btn btn-primary">Reset Filters</a>
                </div>
            <?php else: ?>
                <div class="destinations-grid">
                    <?php foreach ($destinations as $dest): 
                        $isFav = is_favorited((int)$dest['id'], $currentUser ? (int)$currentUser['id'] : null, $pdo);
                        $diffClass = 'difficulty-' . strtolower($dest['difficulty']);
                    ?>
                        <div class="gem-card">
                            <div class="gem-card-header">
                                <img src="<?= e($dest['image_url']) ?>" alt="<?= e($dest['title']) ?>" class="gem-card-img" loading="lazy">
                                <span class="gem-badge-category"><?= e($dest['category_name']) ?></span>
                                <span class="gem-badge-difficulty <?= $diffClass ?>"><?= e($dest['difficulty']) ?></span>
                                
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
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

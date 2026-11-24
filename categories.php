<?php
/**
 * Categories Listing Page
 * Hidden Places Explorer
 */
$pageTitle = "Explore All Secret Biomes & Categories";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

$pdo = getDBConnection();

$stmt = $pdo->query("
    SELECT c.*, COUNT(d.id) AS destination_count 
    FROM categories c 
    LEFT JOIN destinations d ON c.id = d.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC
");
$categories = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div style="background: var(--color-primary-dark); color: var(--color-white); padding: 50px 0 60px;">
    <div class="container">
        <h1 style="font-size: 2.8rem; font-family: var(--font-serif); color: var(--color-white); margin-bottom: 10px;">
            Secret Realms &amp; Biomes
        </h1>
        <p style="color: #cbd8d1; font-size: 1.1rem; max-width: 600px;">
            Discover uncharted places classified by distinct geographic topography and ecological marvels.
        </p>
    </div>
</div>

<div class="container section">
    <div class="categories-grid" style="grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 30px;">
        <?php foreach ($categories as $cat): ?>
            <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
                <div style="height: 200px; overflow: hidden; position: relative;">
                    <img src="<?= e($cat['image_url']) ?>" alt="<?= e($cat['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; top: 14px; right: 14px; background: rgba(15, 45, 32, 0.85); color: var(--color-white); padding: 4px 12px; border-radius: var(--radius-pill); font-size: 0.8rem; font-weight: 600;">
                        <?= $cat['destination_count'] ?> Places
                    </div>
                </div>
                <div style="padding: 24px; display: flex; flex-direction: column; flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <i class="fas <?= e($cat['icon'] ?? 'fa-compass') ?>" style="color: var(--color-accent); font-size: 1.25rem;"></i>
                        <h3 style="font-size: 1.3rem; color: var(--color-primary-dark); margin: 0;"><?= e($cat['name']) ?></h3>
                    </div>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 20px; flex: 1;">
                        <?= e($cat['description']) ?>
                    </p>
                    <a href="<?= $baseUrl ?>/destinations.php?category=<?= $cat['id'] ?>" class="btn btn-outline-dark btn-sm" style="align-self: flex-start;">
                        Explore <?= e($cat['name']) ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

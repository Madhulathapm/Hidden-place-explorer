<?php
/**
 * Destination Management (List & Actions)
 * Hidden Places Explorer
 */
$pageTitle = "Manage Destinations";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();

// Handle Quick Toggle Featured
if (isset($_GET['toggle_featured']) && isset($_GET['id'])) {
    $destId = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE destinations SET is_featured = NOT is_featured WHERE id = ?");
    $stmt->execute([$destId]);
    set_flash_message('success', 'Destination featured status toggled.');
    header('Location: ' . $baseUrl . '/admin/destinations.php');
    exit;
}

// Fetch all destinations with category and metrics
$destinations = $pdo->query("
    SELECT d.*, c.name AS category_name,
           COUNT(DISTINCT r.id) AS review_count,
           COUNT(DISTINCT f.id) AS favorite_count
    FROM destinations d
    JOIN categories c ON d.category_id = c.id
    LEFT JOIN reviews r ON d.id = r.destination_id
    LEFT JOIN favourites f ON d.id = f.destination_id
    GROUP BY d.id
    ORDER BY d.id DESC
")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; color: var(--admin-primary); margin: 0 0 4px;">Destinations Catalog (<?= count($destinations) ?>)</h2>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem; margin: 0;">Add, modify, or remove secret travel landmarks.</p>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <input type="text" id="admin-table-search" placeholder="Quick search..." style="padding: 9px 14px; border: 1px solid var(--admin-border); border-radius: 6px; font-size: 0.9rem;">
        <a href="<?= $baseUrl ?>/admin/destination-add.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add New Destination
        </a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Title &amp; Tagline</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Difficulty</th>
                    <th>Featured</th>
                    <th>Views</th>
                    <th>Reviews</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($destinations)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px;">No destinations found in database.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($destinations as $d): ?>
                        <tr>
                            <td>
                                <img src="<?= e($d['image_url']) ?>" alt="Thumb" class="table-thumb" loading="lazy">
                            </td>
                            <td>
                                <strong style="font-size: 0.95rem; color: var(--admin-primary);"><?= e($d['title']) ?></strong>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted); max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($d['tagline']) ?>
                                </div>
                            </td>
                            <td><span class="badge-status" style="background:#eaf3ee; color:#1b4332;"><?= e($d['category_name']) ?></span></td>
                            <td><?= e($d['country']) ?></td>
                            <td>
                                <span class="badge-status difficulty-<?= strtolower($d['difficulty']) ?>">
                                    <?= e($d['difficulty']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= $baseUrl ?>/admin/destinations.php?toggle_featured=1&id=<?= $d['id'] ?>" title="Click to toggle featured state">
                                    <?php if ($d['is_featured']): ?>
                                        <span class="badge-featured"><i class="fas fa-star"></i> Featured</span>
                                    <?php else: ?>
                                        <span style="color: #9ca3af; font-size: 0.8rem;">Standard</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td><?= number_format($d['views_count']) ?></td>
                            <td><?= $d['review_count'] ?></td>
                            <td>
                                <div class="action-btn-group" style="justify-content: flex-end;">
                                    <a href="<?= $baseUrl ?>/destination.php?id=<?= $d['id'] ?>" target="_blank" class="btn-icon" title="View Public Destination">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= $baseUrl ?>/admin/destination-edit.php?id=<?= $d['id'] ?>" class="btn-icon" title="Edit Destination">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= $baseUrl ?>/admin/destination-delete.php?id=<?= $d['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon btn-icon-danger" title="Delete Destination" onclick="return confirmDelete('Are you sure you want to permanently delete \'<?= addslashes(e($d['title'])) ?>\'?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<?php
/**
 * Review Moderation
 * Hidden Places Explorer
 */
$pageTitle = "Field Reviews Moderation";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();

// Handle Status Toggle (approved/pending)
if (isset($_GET['toggle_status']) && isset($_GET['id']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $rId = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE reviews SET status = IF(status = 'approved', 'pending', 'approved') WHERE id = ?");
        $stmt->execute([$rId]);
        set_flash_message('success', 'Review publication status toggled.');
    } else {
        set_flash_message('error', 'Invalid security token.');
    }
    header('Location: reviews.php');
    exit;
}

// Handle Delete Review
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $rId = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$rId]);
        set_flash_message('success', 'Review deleted successfully.');
    } else {
        set_flash_message('error', 'Invalid security token.');
    }
    header('Location: reviews.php');
    exit;
}

// Fetch all reviews
$reviews = $pdo->query("
    SELECT r.*, u.name AS user_name, u.email AS user_email, d.title AS dest_title, d.id AS dest_id
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN destinations d ON r.destination_id = d.id
    ORDER BY r.id DESC
")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; color: var(--admin-primary); margin: 0 0 4px;">Explorer Reviews (<?= count($reviews) ?>)</h2>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem; margin: 0;">Moderate community field notes, verify ratings, and maintain discourse quality.</p>
    </div>
    <div>
        <input type="text" id="admin-table-search" placeholder="Search reviews..." style="padding: 9px 14px; border: 1px solid var(--admin-border); border-radius: 6px; font-size: 0.9rem;">
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Destination</th>
                    <th>Explorer</th>
                    <th>Rating</th>
                    <th style="max-width: 300px;">Dispatch / Comment</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr><td colspan="7" class="text-center" style="padding: 24px;">No reviews found.</td></tr>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td>
                                <a href="<?= $baseUrl ?>/destination.php?id=<?= $rev['dest_id'] ?>" target="_blank" style="font-weight: 700; color: var(--admin-primary);">
                                    <?= e($rev['dest_title']) ?>
                                </a>
                            </td>
                            <td>
                                <strong><?= e($rev['user_name']) ?></strong><br>
                                <span style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= e($rev['user_email']) ?></span>
                            </td>
                            <td>
                                <span style="color: #f59e0b; font-weight: 700;"><?= (int)$rev['rating'] ?> &starf;</span>
                            </td>
                            <td style="max-width: 300px;">
                                <?php if (!empty($rev['title'])): ?>
                                    <strong style="display: block; font-size: 0.85rem; color: var(--admin-primary);">"<?= e($rev['title']) ?>"</strong>
                                <?php endif; ?>
                                <span style="font-size: 0.85rem; color: #435b50; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= e($rev['comment']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--admin-text-muted); white-space: nowrap;">
                                <?= date('M j, Y', strtotime($rev['created_at'])) ?>
                            </td>
                            <td>
                                <a href="reviews.php?toggle_status=1&id=<?= $rev['id'] ?>&token=<?= $csrfToken ?>" title="Click to toggle approval">
                                    <span class="badge-status badge-<?= e($rev['status']) ?>">
                                        <?= ucfirst(e($rev['status'])) ?>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="action-btn-group" style="justify-content: flex-end;">
                                    <a href="reviews.php?toggle_status=1&id=<?= $rev['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon" title="Toggle Status">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                    <a href="reviews.php?delete=<?= $rev['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon btn-icon-danger" title="Delete Review" onclick="return confirmDelete('Delete this review permanently?');">
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

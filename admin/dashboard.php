<?php
/**
 * Admin Dashboard Overview
 * Hidden Places Explorer
 */
$pageTitle = "Dashboard Overview";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();

// Fetch statistics metrics
$totalDestinations = (int)$pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$totalContacts = (int)$pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();

// Fetch recent reviews
$stmtRecentRev = $pdo->query("
    SELECT r.*, u.name AS user_name, d.title AS dest_title, d.id AS dest_id
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN destinations d ON r.destination_id = d.id
    ORDER BY r.id DESC
    LIMIT 5
");
$recentReviews = $stmtRecentRev->fetchAll();

// Fetch recent contact inquiries
$stmtRecentContacts = $pdo->query("
    SELECT * FROM contacts ORDER BY id DESC LIMIT 5
");
$recentContacts = $stmtRecentContacts->fetchAll();
?>

<!-- Metrics Grid -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-icon-box icon-box-emerald">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <div>
            <div class="metric-label">Total Destinations</div>
            <div class="metric-value"><?= $totalDestinations ?></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box icon-box-blue">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="metric-label">Registered Explorers</div>
            <div class="metric-value"><?= $totalUsers ?></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box icon-box-gold">
            <i class="fas fa-star"></i>
        </div>
        <div>
            <div class="metric-label">Field Reviews</div>
            <div class="metric-value"><?= $totalReviews ?></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box icon-box-purple">
            <i class="fas fa-envelope"></i>
        </div>
        <div>
            <div class="metric-label">Inquiries &amp; Messages</div>
            <div class="metric-value"><?= $totalContacts ?></div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="display: flex; gap: 14px; margin-bottom: 30px; flex-wrap: wrap;">
    <a href="<?= $baseUrl ?>/admin/destination-add.php" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Add New Destination
    </a>
    <a href="<?= $baseUrl ?>/admin/categories.php" class="btn btn-outline-dark" style="background:#ffffff;">
        <i class="fas fa-folder-plus"></i> Manage Categories
    </a>
    <a href="<?= $baseUrl ?>/admin/reviews.php" class="btn btn-outline-dark" style="background:#ffffff;">
        <i class="fas fa-check-double"></i> Review Moderation
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <!-- Recent Reviews -->
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="fas fa-star text-gold"></i> Recent Field Reviews</h3>
            <a href="<?= $baseUrl ?>/admin/reviews.php" style="font-size: 0.85rem; font-weight: 600;">View All &rarr;</a>
        </div>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Destination</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentReviews)): ?>
                        <tr><td colspan="4" class="text-center" style="padding: 24px; color: var(--admin-text-muted);">No reviews posted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentReviews as $rev): ?>
                            <tr>
                                <td>
                                    <a href="<?= $baseUrl ?>/destination.php?id=<?= $rev['dest_id'] ?>" target="_blank" style="font-weight: 600;">
                                        <?= e($rev['dest_title']) ?>
                                    </a>
                                </td>
                                <td><?= e($rev['user_name']) ?></td>
                                <td><?= (int)$rev['rating'] ?> &starf;</td>
                                <td>
                                    <span class="badge-status badge-<?= e($rev['status']) ?>"><?= ucfirst(e($rev['status'])) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Inquiries -->
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="fas fa-envelope text-gold"></i> Latest Inquiries</h3>
            <a href="<?= $baseUrl ?>/admin/contacts.php" style="font-size: 0.85rem; font-weight: 600;">View All &rarr;</a>
        </div>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Explorer</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentContacts)): ?>
                        <tr><td colspan="4" class="text-center" style="padding: 24px; color: var(--admin-text-muted);">No messages received yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentContacts as $msg): ?>
                            <tr>
                                <td>
                                    <strong><?= e($msg['name']) ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= e($msg['email']) ?></span>
                                </td>
                                <td><?= e($msg['subject']) ?></td>
                                <td style="font-size: 0.8rem; color: var(--admin-text-muted);"><?= date('M j', strtotime($msg['created_at'])) ?></td>
                                <td>
                                    <span class="badge-status badge-<?= e($msg['status']) ?>"><?= ucfirst(e($msg['status'])) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

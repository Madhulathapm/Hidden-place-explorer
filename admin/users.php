<?php
/**
 * User / Explorer Management
 * Hidden Places Explorer
 */
$pageTitle = "Explorer Account Management";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();

// Handle Status Toggle (active/inactive)
if (isset($_GET['toggle_status']) && isset($_GET['id']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $uId = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$uId]);
        set_flash_message('success', 'User account status toggled.');
    } else {
        set_flash_message('error', 'Invalid security token.');
    }
    header('Location: users.php');
    exit;
}

// Handle Delete User
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $uId = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$uId]);
        set_flash_message('success', 'User account and all related reviews/favorites have been removed.');
    } else {
        set_flash_message('error', 'Invalid security token.');
    }
    header('Location: users.php');
    exit;
}

// Fetch all users with metrics
$users = $pdo->query("
    SELECT u.*,
           COUNT(DISTINCT f.id) AS fav_count,
           COUNT(DISTINCT r.id) AS review_count
    FROM users u
    LEFT JOIN favourites f ON u.id = f.user_id
    LEFT JOIN reviews r ON u.id = r.user_id
    GROUP BY u.id
    ORDER BY u.id DESC
")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; color: var(--admin-primary); margin: 0 0 4px;">Registered Explorers (<?= count($users) ?>)</h2>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem; margin: 0;">Monitor user activity, reviews contribution, and manage account statuses.</p>
    </div>
    <div>
        <input type="text" id="admin-table-search" placeholder="Search explorers..." style="padding: 9px 14px; border: 1px solid var(--admin-border); border-radius: 6px; font-size: 0.9rem;">
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Avatar</th>
                    <th>Explorer</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Favorites</th>
                    <th>Reviews</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center" style="padding: 24px;">No registered users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #d4a373; color: #143d2b; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                            </td>
                            <td>
                                <strong style="color: var(--admin-primary);"><?= e($u['name']) ?></strong>
                            </td>
                            <td><?= e($u['email']) ?></td>
                            <td style="font-size: 0.85rem; color: var(--admin-text-muted);"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td><span style="font-weight: 700; color: #dc2626;"><i class="fas fa-heart"></i> <?= $u['fav_count'] ?></span></td>
                            <td><span style="font-weight: 700; color: #b45309;"><i class="fas fa-comment"></i> <?= $u['review_count'] ?></span></td>
                            <td>
                                <a href="users.php?toggle_status=1&id=<?= $u['id'] ?>&token=<?= $csrfToken ?>" title="Click to toggle status">
                                    <span class="badge-status badge-<?= e($u['status']) ?>">
                                        <?= ucfirst(e($u['status'])) ?>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="action-btn-group" style="justify-content: flex-end;">
                                    <a href="users.php?delete=<?= $u['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon btn-icon-danger" title="Delete User" onclick="return confirmDelete('Are you sure you want to permanently delete this user account?');">
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

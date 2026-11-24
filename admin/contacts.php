<?php
/**
 * Contact Inquiries Management
 * Hidden Places Explorer
 */
$pageTitle = "Contact Inquiries";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();

// Handle Status Change (mark read / replied)
if (isset($_GET['status']) && isset($_GET['id']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $msgId = (int)$_GET['id'];
        $newStatus = in_array($_GET['status'], ['unread', 'read', 'replied']) ? $_GET['status'] : 'read';
        $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $msgId]);
        set_flash_message('success', 'Inquiry marked as ' . $newStatus . '.');
    } else {
        set_flash_message('error', 'Invalid security token.');
    }
    header('Location: contacts.php');
    exit;
}

// Handle Delete Message
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $msgId = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$msgId]);
        set_flash_message('success', 'Inquiry deleted successfully.');
    } else {
        set_flash_message('error', 'Invalid security token.');
    }
    header('Location: contacts.php');
    exit;
}

// Fetch all contact messages
$messages = $pdo->query("SELECT * FROM contacts ORDER BY id DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; color: var(--admin-primary); margin: 0 0 4px;">Inquiries &amp; Place Suggestions (<?= count($messages) ?>)</h2>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem; margin: 0;">Review feedback, expedition suggestions, and user messages.</p>
    </div>
    <div>
        <input type="text" id="admin-table-search" placeholder="Search inquiries..." style="padding: 9px 14px; border: 1px solid var(--admin-border); border-radius: 6px; font-size: 0.9rem;">
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th style="max-width: 320px;">Message Body</th>
                    <th>Received</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="6" class="text-center" style="padding: 24px;">No inquiries received yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr style="<?= $msg['status'] === 'unread' ? 'background: #fbfdfc; font-weight: 500;' : '' ?>">
                            <td>
                                <strong><?= e($msg['name']) ?></strong><br>
                                <a href="mailto:<?= e($msg['email']) ?>" style="font-size: 0.78rem; color: var(--admin-primary);"><?= e($msg['email']) ?></a>
                            </td>
                            <td><?= e($msg['subject']) ?></td>
                            <td style="max-width: 320px;">
                                <div style="font-size: 0.85rem; color: #3d5449; line-height: 1.5;">
                                    <?= nl2br(e($msg['message'])) ?>
                                </div>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--admin-text-muted); white-space: nowrap;">
                                <?= date('M j, Y H:i', strtotime($msg['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= e($msg['status']) ?>">
                                    <?= ucfirst(e($msg['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btn-group" style="justify-content: flex-end;">
                                    <?php if ($msg['status'] === 'unread'): ?>
                                        <a href="contacts.php?status=read&id=<?= $msg['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon" title="Mark as Read">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php elseif ($msg['status'] === 'read'): ?>
                                        <a href="contacts.php?status=replied&id=<?= $msg['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon" title="Mark as Replied">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="mailto:<?= e($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>" class="btn-icon" title="Reply via Email Client">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                    <a href="contacts.php?delete=<?= $msg['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon btn-icon-danger" title="Delete Inquiry" onclick="return confirmDelete('Delete this inquiry?');">
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

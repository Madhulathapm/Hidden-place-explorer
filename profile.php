<?php
/**
 * User Profile & Review History
 * Hidden Places Explorer
 */
$pageTitle = "Explorer Profile";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

require_user_login();
$currentUser = current_user();
$pdo = getDBConnection();

// Fetch latest user data from DB
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$currentUser['id']]);
$user = $userStmt->fetch();

if (!$user) {
    logout_user();
    header('Location: ' . get_base_url() . '/login.php');
    exit;
}

$errors = [];

// Handle Profile Information Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        if (empty($name)) {
            $errors[] = 'Name cannot be blank.';
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
            $updateStmt->execute([$name, $bio, $user['id']]);
            
            $_SESSION['user_name'] = $name;
            set_flash_message('success', 'Profile information updated successfully.');
            header('Location: profile.php');
            exit;
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Your current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New password and confirmation do not match.';
        } else {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $pwdStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pwdStmt->execute([$hashed, $user['id']]);

            set_flash_message('success', 'Password changed successfully!');
            header('Location: profile.php');
            exit;
        }
    }
}

// Fetch user's submitted reviews
$revStmt = $pdo->prepare("
    SELECT r.*, d.title AS destination_title, d.slug AS destination_slug, d.id AS dest_id
    FROM reviews r
    JOIN destinations d ON r.destination_id = d.id
    WHERE r.user_id = ?
    ORDER BY r.id DESC
");
$revStmt->execute([$user['id']]);
$userReviews = $revStmt->fetchAll();

// Count favorites
$favCountStmt = $pdo->prepare("SELECT COUNT(*) FROM favourites WHERE user_id = ?");
$favCountStmt->execute([$user['id']]);
$favCount = (int)$favCountStmt->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<div style="background: var(--color-primary-dark); color: var(--color-white); padding: 50px 0 60px;">
    <div class="container">
        <h1 style="font-size: 2.8rem; font-family: var(--font-serif); color: var(--color-white); margin-bottom: 8px;">
            Explorer Profile
        </h1>
        <p style="color: #cbd8d1; font-size: 1.1rem; max-width: 600px;">
            Manage your credentials, bio, and review dispatches.
        </p>
    </div>
</div>

<div class="container section">
    <?php if (!empty($errors)): ?>
        <div class="flash-alert flash-error" style="margin-bottom: 24px;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errors as $eMsg): ?>
                    <div><?= e($eMsg) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px; align-items: start;">
        <!-- Left: User Overview Card -->
        <div>
            <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 32px; text-align: center; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-accent); color: var(--color-primary-dark); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 700; margin: 0 auto 16px;">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h2 style="font-size: 1.4rem; color: var(--color-primary-dark); margin-bottom: 4px;"><?= e($user['name']) ?></h2>
                <div style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 16px;"><?= e($user['email']) ?></div>
                
                <p style="font-size: 0.9rem; color: #42584e; line-height: 1.6; margin-bottom: 24px; font-style: italic;">
                    "<?= e($user['bio'] ?? 'Adventurous spirit exploring untouched sanctuaries.') ?>"
                </p>

                <div style="display: flex; justify-content: space-around; border-top: 1px solid var(--color-cream-border); padding-top: 20px;">
                    <div>
                        <div style="font-size: 1.4rem; font-weight: 800; color: var(--color-primary);"><?= $favCount ?></div>
                        <div style="font-size: 0.8rem; color: var(--color-text-muted);">Saved Places</div>
                    </div>
                    <div>
                        <div style="font-size: 1.4rem; font-weight: 800; color: var(--color-primary);"><?= count($userReviews) ?></div>
                        <div style="font-size: 0.8rem; color: var(--color-text-muted);">Reviews Written</div>
                    </div>
                </div>
            </div>

            <a href="<?= $baseUrl ?>/favorites.php" class="btn btn-outline-dark btn-block" style="margin-bottom: 12px;">
                <i class="fas fa-heart text-danger"></i> View Saved Places
            </a>
            <a href="<?= $baseUrl ?>/logout.php" class="btn btn-block" style="background: #fee2e2; color: #991b1b; border: 1px solid #f87171;">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>

        <!-- Right: Edit Profile & Password Tabs/Forms -->
        <div>
            <!-- Form 1: General Details -->
            <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                <h3 style="font-size: 1.25rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin-bottom: 20px;">
                    Personal Information
                </h3>
                <form action="profile.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address (Read Only)</label>
                        <input type="email" id="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="background:#f3f4f6; cursor:not-allowed;">
                    </div>

                    <div class="form-group">
                        <label for="bio" class="form-label">Explorer Bio / Description</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3"><?= e($user['bio']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Profile Details
                    </button>
                </form>
            </div>

            <!-- Form 2: Change Password -->
            <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                <h3 style="font-size: 1.25rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin-bottom: 20px;">
                    Change Password
                </h3>
                <form action="profile.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="At least 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>

            <!-- Review History -->
            <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 32px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.25rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin-bottom: 20px;">
                    My Review Dispatches (<?= count($userReviews) ?>)
                </h3>

                <?php if (empty($userReviews)): ?>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">You haven't submitted any field reviews yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($userReviews as $uRev): ?>
                            <div style="border: 1px solid var(--color-cream-border); border-radius: var(--radius-sm); padding: 18px; background: var(--color-cream-bg);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <a href="<?= $baseUrl ?>/destination.php?id=<?= $uRev['dest_id'] ?>" style="font-weight: 700; color: var(--color-primary-dark); font-size: 1.05rem;">
                                        <?= e($uRev['destination_title']) ?>
                                    </a>
                                    <?= render_stars((float)$uRev['rating']) ?>
                                </div>
                                <?php if (!empty($uRev['title'])): ?>
                                    <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 6px;">"<?= e($uRev['title']) ?>"</div>
                                <?php endif; ?>
                                <p style="font-size: 0.9rem; color: #435b50; line-height: 1.5; margin: 0;">
                                    <?= e($uRev['comment']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

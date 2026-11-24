<?php
/**
 * Admin Login Page
 * Hidden Places Explorer
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

if (is_admin_logged_in()) {
    header('Location: ' . get_base_url() . '/admin/dashboard.php');
    exit;
}

$baseUrl = get_base_url();
$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both administrator email and password.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Update last login
                $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

                login_admin($admin);
                set_flash_message('success', 'Authenticated as ' . $admin['name'] . ' (' . $admin['role'] . ').');
                header('Location: ' . $baseUrl . '/admin/dashboard.php');
                exit;
            } else {
                $error = 'Invalid administrator credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Authentication | Hidden Places Explorer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<body style="background: #091d14; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">

    <div style="background: #ffffff; border-radius: var(--radius-md); max-width: 440px; width: 100%; padding: 40px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: #eaf3ee; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 16px;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 style="font-size: 1.6rem; color: #0f2d20; font-family: var(--font-serif); margin-bottom: 6px;">Staff Control Room</h1>
            <p style="color: #61786e; font-size: 0.9rem; margin: 0;">Restricted administrator authentication gate.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="flash-alert flash-error" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <?= render_flash_messages() ?>

        <form action="<?= $baseUrl ?>/admin/index.php" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email" class="form-label">Admin Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="admin@hiddenplaces.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Master Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 24px; background: #143d2b;">
                <i class="fas fa-lock"></i> Enter Console
            </button>
        </form>

        <div style="background: #fdfbf7; border: 1px dashed #d1ded8; border-radius: 6px; padding: 14px; margin-top: 24px; font-size: 0.8rem; color: #354f42;">
            <strong>Default Admin Credentials:</strong><br>
            Email: <code>admin@hiddenplaces.com</code><br>
            Password: <code>Admin@12345</code>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="<?= $baseUrl ?>/index.php" style="color: #61786e; font-size: 0.85rem;">
                <i class="fas fa-arrow-left"></i> Return to Public Site
            </a>
        </div>
    </div>

</body>
</html>

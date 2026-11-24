<?php
/**
 * User Login Page
 * Hidden Places Explorer
 */
$pageTitle = "Explorer Sign In";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

// Redirect if already logged in
if (is_user_logged_in()) {
    header('Location: ' . get_base_url() . '/index.php');
    exit;
}

$pdo = getDBConnection();
$email = '';
$redirect = trim($_GET['redirect'] ?? '');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = trim($_POST['redirect'] ?? '');

        if (empty($email) || empty($password)) {
            $errors[] = 'Please provide both email and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $errors[] = 'Your explorer account has been deactivated. Please contact support.';
                } else {
                    login_user($user);
                    set_flash_message('success', 'Welcome back, ' . $user['name'] . '!');

                    // Redirect safely
                    if (!empty($redirect) && str_starts_with($redirect, '/')) {
                        header('Location: ' . $redirect);
                    } else {
                        header('Location: ' . get_base_url() . '/index.php');
                    }
                    exit;
                }
            } else {
                $errors[] = 'Invalid email address or password combination.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <div style="font-size: 2.2rem; color: var(--color-accent); margin-bottom: 8px;">
                <i class="fas fa-mountain"></i>
            </div>
            <h1 class="auth-card-title">Explorer Sign In</h1>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                Access your saved secret places and personal expedition reviews.
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="flash-alert flash-error" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= $baseUrl ?>/login.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. explorer@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Your account password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 24px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <!-- Test credentials hint box for demonstration -->
        <div style="background: var(--color-cream-card); border: 1px dashed var(--color-cream-border); border-radius: var(--radius-sm); padding: 14px 18px; margin-top: 24px; font-size: 0.82rem; color: #3b5046;">
            <strong>Demo User Account:</strong><br>
            Email: <code>elena@example.com</code><br>
            Password: <code>User@12345</code>
        </div>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--color-text-muted);">
            Don't have an account yet? 
            <a href="<?= $baseUrl ?>/register.php" style="color: var(--color-primary); font-weight: 700;">Create one for free</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

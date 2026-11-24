<?php
/**
 * User Registration Page
 * Hidden Places Explorer
 */
$pageTitle = "Join the Explorer Circle";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

// Redirect if already logged in
if (is_user_logged_in()) {
    header('Location: ' . get_base_url() . '/index.php');
    exit;
}

$pdo = getDBConnection();
$name = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security verification failed. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($name)) {
            $errors[] = 'Please enter your full name.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $errors[] = 'This email address is already registered. Please sign in instead.';
            } else {
                // Hash password with bcrypt
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $insertStmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, avatar, bio, status) 
                    VALUES (?, ?, ?, 'default_avatar.png', 'Adventurous explorer seeking uncharted horizons.', 'active')
                ");
                $insertStmt->execute([$name, $email, $hashedPassword]);
                $newUserId = (int)$pdo->lastInsertId();

                // Automatically log the new user in
                login_user([
                    'id'     => $newUserId,
                    'name'   => $name,
                    'email'  => $email,
                    'avatar' => 'default_avatar.png'
                ]);

                set_flash_message('success', 'Welcome to Hidden Places Explorer, ' . $name . '! Your account is active.');
                header('Location: ' . get_base_url() . '/index.php');
                exit;
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
                <i class="fas fa-compass"></i>
            </div>
            <h1 class="auth-card-title">Create Explorer Account</h1>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                Join our circle to save secret places, submit field notes, and download GPS waypoints.
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

        <form action="<?= $baseUrl ?>/register.php" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= e($name) ?>" placeholder="e.g. Elena Rostova" required autofocus>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. explorer@example.com" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Create Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 24px;">
                <i class="fas fa-user-plus"></i> Register Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--color-text-muted);">
            Already have an explorer account? 
            <a href="<?= $baseUrl ?>/login.php" style="color: var(--color-primary); font-weight: 700;">Sign In here</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

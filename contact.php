<?php
/**
 * Contact Page & Inquiries Form
 * Hidden Places Explorer
 */
$pageTitle = "Contact Expedition Headquarters";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

$pdo = getDBConnection();
$currentUser = current_user();

$name = $currentUser['name'] ?? '';
$email = $currentUser['email'] ?? '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Security token expired. Please reload and try again.');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            set_flash_message('error', 'All contact fields are required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('error', 'Please provide a valid email address.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO contacts (name, email, subject, message, status)
                VALUES (?, ?, ?, ?, 'unread')
            ");
            $stmt->execute([$name, $email, $subject, $message]);

            set_flash_message('success', 'Thank you! Your dispatch has reached Expedition HQ. We will respond promptly.');
            $subject = '';
            $message = '';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="background: var(--color-primary-dark); color: var(--color-white); padding: 50px 0 60px;">
    <div class="container">
        <h1 style="font-size: 2.8rem; font-family: var(--font-serif); color: var(--color-white); margin-bottom: 10px;">
            Contact Expedition HQ
        </h1>
        <p style="color: #cbd8d1; font-size: 1.1rem; max-width: 600px;">
            Suggest an uncharted hidden gem, inquire about environmental guidelines, or connect with our editorial team.
        </p>
    </div>
</div>

<div class="container section">
    <div class="contact-layout">
        <!-- Contact Info Card -->
        <div class="contact-info-card">
            <h3>Get in Touch with Explorers</h3>
            <p style="color: #c0d4cb; font-size: 0.95rem; line-height: 1.7;">
                Whether you have discovered a secluded gorge or need guidance navigating sensitive protected landscapes, our team is here to assist.
            </p>

            <ul class="contact-info-list">
                <li class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <strong style="display: block; font-size: 1rem; color: #ffffff;">Field Headquarters</strong>
                        <span style="color: #cad8d1; font-size: 0.9rem;">742 Alpine Ridge Pass, Zurich, Switzerland</span>
                    </div>
                </li>
                <li class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <strong style="display: block; font-size: 1rem; color: #ffffff;">Dispatches &amp; Press</strong>
                        <span style="color: #cad8d1; font-size: 0.9rem;">expeditions@hiddenplacesexplorer.org</span>
                    </div>
                </li>
                <li class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <strong style="display: block; font-size: 1rem; color: #ffffff;">Dispatch Response Time</strong>
                        <span style="color: #cad8d1; font-size: 0.9rem;">Within 24 to 48 standard business hours</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Contact Submission Form -->
        <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 36px; box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 1.6rem; font-family: var(--font-serif); color: var(--color-primary-dark); margin-bottom: 8px;">
                Send Us a Message
            </h2>
            <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 24px;">
                Fill out the transmission form below. All inquiries are saved directly into our secure database.
            </p>

            <form action="<?= $baseUrl ?>/contact.php" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="contact-name" class="form-label">Your Full Name</label>
                    <input type="text" id="contact-name" name="name" class="form-control" value="<?= e($name) ?>" placeholder="e.g. Elena Rostova" required>
                </div>

                <div class="form-group">
                    <label for="contact-email" class="form-label">Email Address</label>
                    <input type="email" id="contact-email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. explorer@example.com" required>
                </div>

                <div class="form-group">
                    <label for="contact-subject" class="form-label">Subject</label>
                    <input type="text" id="contact-subject" name="subject" class="form-control" value="<?= e($subject) ?>" placeholder="e.g. Suggesting a new cave in Peloponnese" required>
                </div>

                <div class="form-group">
                    <label for="contact-message" class="form-label">Message &amp; Observations</label>
                    <textarea id="contact-message" name="message" class="form-control" rows="5" placeholder="Share your message or detailed place suggestion..." required><?= e($message) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> Transmit Message
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

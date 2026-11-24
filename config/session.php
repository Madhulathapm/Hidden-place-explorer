<?php
/**
 * Session, Authentication, Security & Utility Helpers
 * Hidden Places Explorer
 */

if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

/**
 * Returns dynamic base URL of the application
 * Automatically adapts whether accessed via localhost/hidden_places_explorer or localhost/php or port 8000
 */
function get_base_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Script path calculation
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    
    // Check if script is inside /admin/ or /ajax/
    $subDir = '';
    $parts = explode('/', trim($scriptName, '/'));
    if (count($parts) > 1) {
        // Collect directories up to the project root
        $rootParts = [];
        foreach ($parts as $part) {
            if ($part === 'admin' || $part === 'ajax') {
                break;
            }
            if (str_ends_with($part, '.php')) {
                break;
            }
            $rootParts[] = $part;
        }
        if (!empty($rootParts)) {
            $subDir = '/' . implode('/', $rootParts);
        }
    }
    
    return rtrim($protocol . $host . $subDir, '/');
}

/**
 * Generate or get current CSRF token
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden HTML input with the CSRF token
 */
function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify submitted CSRF token
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize output (XSS protection)
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Flash message helper - set
 */
function set_flash_message(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type'    => in_array($type, ['success', 'error', 'danger', 'warning', 'info']) ? ($type === 'danger' ? 'error' : $type) : 'info',
        'message' => $message
    ];
}

/**
 * Flash message helper - retrieve and clear
 */
function get_flash_messages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Render flash messages HTML
 */
function render_flash_messages(): string {
    $messages = get_flash_messages();
    if (empty($messages)) {
        return '';
    }

    $html = '<div class="flash-messages-container" id="flash-container">';
    foreach ($messages as $msg) {
        $type = htmlspecialchars($msg['type']);
        $text = htmlspecialchars($msg['message']);
        $icon = match($type) {
            'success' => 'fa-check-circle',
            'error'   => 'fa-exclamation-circle',
            'warning' => 'fa-exclamation-triangle',
            default   => 'fa-info-circle'
        };
        $html .= <<<HTML
        <div class="flash-alert flash-{$type}" role="alert">
            <i class="fas {$icon}"></i>
            <span>{$text}</span>
            <button type="button" class="flash-close" onclick="this.parentElement.remove();">&times;</button>
        </div>
HTML;
    }
    $html .= '</div>';
    return $html;
}

/* ----------------------------------------------------
 * User Authentication Helpers
 * ---------------------------------------------------- */

function is_user_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_user_logged_in()) {
        return null;
    }
    return [
        'id'     => $_SESSION['user_id'],
        'name'   => $_SESSION['user_name'] ?? 'Explorer',
        'email'  => $_SESSION['user_email'] ?? '',
        'avatar' => $_SESSION['user_avatar'] ?? 'default_avatar.png'
    ];
}

function login_user(array $user): void {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? 'default_avatar.png';
}

function logout_user(): void {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_avatar']);
}

function require_user_login(): void {
    if (!is_user_logged_in()) {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        set_flash_message('info', 'Please sign in to access your saved places and reviews.');
        header('Location: ' . get_base_url() . '/login.php?redirect=' . urlencode($currentUri));
        exit;
    }
}

/* ----------------------------------------------------
 * Admin Authentication Helpers
 * ---------------------------------------------------- */

function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

function current_admin(): ?array {
    if (!is_admin_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['admin_id'],
        'name'  => $_SESSION['admin_name'] ?? 'Admin',
        'email' => $_SESSION['admin_email'] ?? '',
        'role'  => $_SESSION['admin_role'] ?? 'Super Admin'
    ];
}

function login_admin(array $admin): void {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'] ?? 'Super Admin';
}

function logout_admin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role']);
}

function require_admin_login(): void {
    if (!is_admin_logged_in()) {
        set_flash_message('error', 'Administrator authentication required.');
        header('Location: ' . get_base_url() . '/admin/index.php');
        exit;
    }
}

/* ----------------------------------------------------
 * Display & Formatting Helpers
 * ---------------------------------------------------- */

/**
 * Render interactive or display star rating
 */
function render_stars(float $rating, int $max = 5): string {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = $max - $fullStars - ($halfStar ? 1 : 0);

    $html = '<div class="star-rating" title="' . number_format($rating, 1) . ' out of 5">';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star filled"></i>';
    }
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt filled"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    $html .= ' <span class="rating-number">' . number_format($rating, 1) . '</span>';
    $html .= '</div>';
    return $html;
}

/**
 * Convert string into clean URL slug
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Check if a destination is favorited by current user
 */
function is_favorited(int $destinationId, ?int $userId, PDO $pdo): bool {
    if (!$userId) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM favourites WHERE user_id = ? AND destination_id = ? LIMIT 1");
    $stmt->execute([$userId, $destinationId]);
    return (bool)$stmt->fetchColumn();
}

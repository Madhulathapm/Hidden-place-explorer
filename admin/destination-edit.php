<?php
/**
 * Edit Existing Destination
 * Hidden Places Explorer
 */
$pageTitle = "Edit Destination";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . $baseUrl . '/admin/destinations.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$id]);
$dest = $stmt->fetch();

if (!$dest) {
    set_flash_message('error', 'Destination not found.');
    header('Location: ' . $baseUrl . '/admin/destinations.php');
    exit;
}

$errors = [];
$title = $dest['title'];
$tagline = $dest['tagline'];
$category_id = $dest['category_id'];
$description = $dest['description'];
$location = $dest['location'];
$country = $dest['country'];
$difficulty = $dest['difficulty'];
$best_season = $dest['best_season'];
$estimated_duration = $dest['estimated_duration'];
$image_url = $dest['image_url'];
$gallery_raw = '';
if (!empty($dest['gallery'])) {
    $galArr = json_decode($dest['gallery'], true);
    if (is_array($galArr)) {
        $gallery_raw = implode("\n", $galArr);
    }
}
$map_embed_url = $dest['map_embed_url'];
$map_link = $dest['map_link'];
$latitude = $dest['latitude'];
$longitude = $dest['longitude'];
$travel_tips = $dest['travel_tips'];
$is_featured = (int)$dest['is_featured'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $tagline = trim($_POST['tagline'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $difficulty = trim($_POST['difficulty'] ?? 'Moderate');
        $best_season = trim($_POST['best_season'] ?? '');
        $estimated_duration = trim($_POST['estimated_duration'] ?? '1-2 Days');
        $image_url = trim($_POST['image_url'] ?? '');
        $gallery_raw = trim($_POST['gallery'] ?? '');
        $map_embed_url = trim($_POST['map_embed_url'] ?? '');
        $map_link = trim($_POST['map_link'] ?? '');
        $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
        $travel_tips = trim($_POST['travel_tips'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        if (empty($title) || empty($tagline) || empty($description) || empty($location) || empty($country) || empty($image_url)) {
            $errors[] = 'Please fill out all required fields marked with (*).';
        }

        if ($category_id <= 0) {
            $errors[] = 'Please select a valid category.';
        }

        $galleryJson = null;
        if (!empty($gallery_raw)) {
            $galleryItems = array_map('trim', preg_split('/[\r\n,]+/', $gallery_raw));
            $galleryItems = array_filter($galleryItems);
            $galleryJson = json_encode(array_values($galleryItems));
        }

        if (empty($map_embed_url)) {
            $map_embed_url = "https://maps.google.com/maps?q=" . urlencode($location . ' ' . $country) . "&t=&z=13&ie=UTF8&iwloc=&output=embed";
        }
        if (empty($map_link)) {
            $map_link = "https://maps.google.com/?q=" . urlencode($location . ' ' . $country);
        }

        if (empty($errors)) {
            $updateStmt = $pdo->prepare("
                UPDATE destinations SET
                    category_id = ?, title = ?, tagline = ?, description = ?, location = ?,
                    country = ?, difficulty = ?, best_season = ?, estimated_duration = ?,
                    image_url = ?, gallery = ?, map_embed_url = ?, map_link = ?,
                    latitude = ?, longitude = ?, travel_tips = ?, is_featured = ?
                WHERE id = ?
            ");

            $updateStmt->execute([
                $category_id, $title, $tagline, $description, $location,
                $country, $difficulty, $best_season, $estimated_duration,
                $image_url, $galleryJson, $map_embed_url, $map_link,
                $latitude, $longitude, $travel_tips, $is_featured,
                $id
            ]);

            set_flash_message('success', 'Destination "' . $title . '" updated successfully!');
            header('Location: ' . $baseUrl . '/admin/destinations.php');
            exit;
        }
    }
}
?>

<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?= $baseUrl ?>/admin/destinations.php" style="color: var(--admin-text-muted); font-size: 0.9rem;">
        &larr; Back to Destinations
    </a>
    <a href="<?= $baseUrl ?>/destination.php?id=<?= $id ?>" target="_blank" class="btn btn-outline-dark btn-sm" style="background:#ffffff;">
        <i class="fas fa-external-link-alt"></i> Preview Public Page
    </a>
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

<div class="admin-card admin-form-card">
    <form action="<?= $baseUrl ?>/admin/destination-edit.php?id=<?= $id ?>" method="POST">
        <?= csrf_field() ?>

        <h3 style="font-size: 1.25rem; color: var(--admin-primary); margin-bottom: 20px;">Edit Destination: <?= e($dest['title']) ?></h3>
        
        <div class="form-grid-2">
            <div class="form-group">
                <label for="title" class="form-label">Destination Title *</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= e($title) ?>" required>
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Category *</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="tagline" class="form-label">Tagline *</label>
            <input type="text" id="tagline" name="tagline" class="form-control" value="<?= e($tagline) ?>" required>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description *</label>
            <textarea id="description" name="description" class="form-control" rows="6" required><?= e($description) ?></textarea>
        </div>

        <h3 style="font-size: 1.25rem; color: var(--admin-primary); margin: 30px 0 20px;">Geography &amp; Travel Specifications</h3>

        <div class="form-grid-3">
            <div class="form-group">
                <label for="location" class="form-label">Location / Region *</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= e($location) ?>" required>
            </div>

            <div class="form-group">
                <label for="country" class="form-label">Country *</label>
                <input type="text" id="country" name="country" class="form-control" value="<?= e($country) ?>" required>
            </div>

            <div class="form-group">
                <label for="difficulty" class="form-label">Difficulty *</label>
                <select id="difficulty" name="difficulty" class="form-control">
                    <option value="Easy" <?= $difficulty === 'Easy' ? 'selected' : '' ?>>Easy</option>
                    <option value="Moderate" <?= $difficulty === 'Moderate' ? 'selected' : '' ?>>Moderate</option>
                    <option value="Challenging" <?= $difficulty === 'Challenging' ? 'selected' : '' ?>>Challenging</option>
                    <option value="Expert" <?= $difficulty === 'Expert' ? 'selected' : '' ?>>Expert</option>
                </select>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="best_season" class="form-label">Best Season to Visit *</label>
                <input type="text" id="best_season" name="best_season" class="form-control" value="<?= e($best_season) ?>" required>
            </div>

            <div class="form-group">
                <label for="estimated_duration" class="form-label">Estimated Trek Duration</label>
                <input type="text" id="estimated_duration" name="estimated_duration" class="form-control" value="<?= e($estimated_duration) ?>">
            </div>
        </div>

        <h3 style="font-size: 1.25rem; color: var(--admin-primary); margin: 30px 0 20px;">Media &amp; Maps</h3>

        <div class="form-group">
            <label for="image_url" class="form-label">Primary Hero Image URL *</label>
            <input type="url" id="image_url" name="image_url" class="form-control" value="<?= e($image_url) ?>" required>
            <img id="image_preview" src="<?= e($image_url) ?>" alt="Preview" style="display:block; max-height:180px; border-radius:8px; margin-top:10px; object-fit:cover;">
        </div>

        <div class="form-group">
            <label for="gallery" class="form-label">Additional Gallery URLs</label>
            <textarea id="gallery" name="gallery" class="form-control" rows="3"><?= e($gallery_raw) ?></textarea>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="map_embed_url" class="form-label">Google Maps Embed URL</label>
                <input type="text" id="map_embed_url" name="map_embed_url" class="form-control" value="<?= e($map_embed_url) ?>">
            </div>

            <div class="form-group">
                <label for="map_link" class="form-label">Direct Google Maps Link</label>
                <input type="url" id="map_link" name="map_link" class="form-control" value="<?= e($map_link) ?>">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="latitude" class="form-label">Latitude</label>
                <input type="text" id="latitude" name="latitude" class="form-control" value="<?= e($latitude) ?>">
            </div>

            <div class="form-group">
                <label for="longitude" class="form-label">Longitude</label>
                <input type="text" id="longitude" name="longitude" class="form-control" value="<?= e($longitude) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="travel_tips" class="form-label">Field Notes &amp; Tips</label>
            <textarea id="travel_tips" name="travel_tips" class="form-control" rows="3"><?= e($travel_tips) ?></textarea>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_featured" value="1" <?= $is_featured ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--admin-primary);">
                <strong style="color: var(--admin-primary);">Mark as Featured Destination</strong>
            </label>
        </div>

        <div style="margin-top: 32px; display: flex; gap: 14px;">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="<?= $baseUrl ?>/admin/destinations.php" class="btn btn-outline-dark btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

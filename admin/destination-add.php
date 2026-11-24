<?php
/**
 * Add New Destination
 * Hidden Places Explorer
 */
$pageTitle = "Add New Destination";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];
$title = '';
$tagline = '';
$category_id = '';
$description = '';
$location = '';
$country = '';
$difficulty = 'Moderate';
$best_season = '';
$estimated_duration = '1-2 Days';
$image_url = '';
$gallery = '';
$map_embed_url = '';
$map_link = '';
$latitude = '';
$longitude = '';
$travel_tips = '';
$is_featured = 0;

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

        // Validation
        if (empty($title) || empty($tagline) || empty($description) || empty($location) || empty($country) || empty($image_url)) {
            $errors[] = 'Please fill out all required fields marked with (*).';
        }

        if ($category_id <= 0) {
            $errors[] = 'Please select a valid category.';
        }

        // Process gallery into JSON array
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
            // Generate clean unique slug
            $slugBase = slugify($title);
            $slug = $slugBase;
            $counter = 1;
            while (true) {
                $checkSlug = $pdo->prepare("SELECT id FROM destinations WHERE slug = ?");
                $checkSlug->execute([$slug]);
                if (!$checkSlug->fetch()) {
                    break;
                }
                $slug = $slugBase . '-' . $counter++;
            }

            $stmt = $pdo->prepare("
                INSERT INTO destinations (
                    category_id, title, slug, tagline, description, location, country,
                    difficulty, best_season, estimated_duration, image_url, gallery,
                    map_embed_url, map_link, latitude, longitude, travel_tips, is_featured
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $category_id, $title, $slug, $tagline, $description, $location, $country,
                $difficulty, $best_season, $estimated_duration, $image_url, $galleryJson,
                $map_embed_url, $map_link, $latitude, $longitude, $travel_tips, $is_featured
            ]);

            set_flash_message('success', 'Destination "' . $title . '" has been published successfully!');
            header('Location: ' . $baseUrl . '/admin/destinations.php');
            exit;
        }
    }
}
?>

<div style="margin-bottom: 24px;">
    <a href="<?= $baseUrl ?>/admin/destinations.php" style="color: var(--admin-text-muted); font-size: 0.9rem;">
        &larr; Back to Destinations
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
    <form action="<?= $baseUrl ?>/admin/destination-add.php" method="POST">
        <?= csrf_field() ?>

        <h3 style="font-size: 1.25rem; color: var(--admin-primary); margin-bottom: 20px;">General Destination Information</h3>
        
        <div class="form-grid-2">
            <div class="form-group">
                <label for="title" class="form-label">Destination Title *</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= e($title) ?>" placeholder="e.g. Melissani Sunken Cave Lake" required>
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Category *</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Select Biome Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="tagline" class="form-label">Tagline (Brief One-Sentence Summary) *</label>
            <input type="text" id="tagline" name="tagline" class="form-control" value="<?= e($tagline) ?>" placeholder="e.g. A neon-blue subterranean lagoon hidden beneath a collapsed limestone vault" required>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Comprehensive Narrative Description *</label>
            <textarea id="description" name="description" class="form-control" rows="6" placeholder="Describe the geography, historical lore, sensory experience, and trekking approach..." required><?= e($description) ?></textarea>
        </div>

        <h3 style="font-size: 1.25rem; color: var(--admin-primary); margin: 30px 0 20px;">Geography &amp; Travel Specifications</h3>

        <div class="form-grid-3">
            <div class="form-group">
                <label for="location" class="form-label">Exact Location / Region *</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= e($location) ?>" placeholder="e.g. Dixam Plateau, Socotra" required>
            </div>

            <div class="form-group">
                <label for="country" class="form-label">Country *</label>
                <input type="text" id="country" name="country" class="form-control" value="<?= e($country) ?>" placeholder="e.g. Yemen" required>
            </div>

            <div class="form-group">
                <label for="difficulty" class="form-label">Terrain Difficulty *</label>
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
                <input type="text" id="best_season" name="best_season" class="form-control" value="<?= e($best_season) ?>" placeholder="e.g. October to April" required>
            </div>

            <div class="form-group">
                <label for="estimated_duration" class="form-label">Estimated Trek / Visit Duration</label>
                <input type="text" id="estimated_duration" name="estimated_duration" class="form-control" value="<?= e($estimated_duration) ?>" placeholder="e.g. 1-2 Days or Half Day">
            </div>
        </div>

        <h3 style="font-size: 1.25rem; color: var(--admin-primary); margin: 30px 0 20px;">Imagery &amp; Media</h3>

        <div class="form-group">
            <label for="image_url" class="form-label">Primary Hero Image URL *</label>
            <input type="url" id="image_url" name="image_url" class="form-control" value="<?= e($image_url) ?>" placeholder="https://images.unsplash.com/..." required>
            <div class="form-helper">High resolution landscape travel photograph URL.</div>
            <img id="image_preview" src="" alt="Preview" style="display:none; max-height:180px; border-radius:8px; margin-top:10px; object-fit:cover;">
        </div>

        <div class="form-group">
            <label for="gallery" class="form-label">Additional Gallery URLs (One per line or comma-separated)</label>
            <textarea id="gallery" name="gallery" class="form-control" rows="3" placeholder="https://images.unsplash.com/photo-1&#10;https://images.unsplash.com/photo-2"><?= e($gallery) ?></textarea>
        </div>

        <h3 style="font-size: 1.25rem; color: var(--admin-primary); margin: 30px 0 20px;">Map Integration &amp; Coordinates</h3>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="map_embed_url" class="form-label">Google Maps Embed URL</label>
                <input type="text" id="map_embed_url" name="map_embed_url" class="form-control" value="<?= e($map_embed_url) ?>" placeholder="https://www.google.com/maps/embed?pb=...">
                <div class="form-helper">Leave empty to auto-generate from Location &amp; Country.</div>
            </div>

            <div class="form-group">
                <label for="map_link" class="form-label">Direct Google Maps Link</label>
                <input type="url" id="map_link" name="map_link" class="form-control" value="<?= e($map_link) ?>" placeholder="https://maps.google.com/?q=...">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="latitude" class="form-label">Latitude Coordinate</label>
                <input type="text" id="latitude" name="latitude" class="form-control" value="<?= e($latitude) ?>" placeholder="e.g. 12.4634200">
            </div>

            <div class="form-group">
                <label for="longitude" class="form-label">Longitude Coordinate</label>
                <input type="text" id="longitude" name="longitude" class="form-control" value="<?= e($longitude) ?>" placeholder="e.g. 53.8237300">
            </div>
        </div>

        <div class="form-group">
            <label for="travel_tips" class="form-label">Essential Field &amp; Ecological Tips</label>
            <textarea id="travel_tips" name="travel_tips" class="form-control" rows="3" placeholder="Permit requirements, water supplies, trail warnings, respecting local community..."><?= e($travel_tips) ?></textarea>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_featured" value="1" <?= $is_featured ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--admin-primary);">
                <strong style="color: var(--admin-primary);">Mark as Featured Sanctuary (Shown on Home Page Hero Showcase)</strong>
            </label>
        </div>

        <div style="margin-top: 32px; display: flex; gap: 14px;">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-check"></i> Publish Destination
            </button>
            <a href="<?= $baseUrl ?>/admin/destinations.php" class="btn btn-outline-dark btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

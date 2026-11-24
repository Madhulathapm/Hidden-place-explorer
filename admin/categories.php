<?php
/**
 * Category Management
 * Hidden Places Explorer
 */
$pageTitle = "Category Management";
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = getDBConnection();
$errors = [];

// Handle Create Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-compass');
        $image_url = trim($_POST['image_url'] ?? '');

        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } else {
            $slug = slugify($name);
            $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? OR name = ?");
            $check->execute([$slug, $name]);
            if ($check->fetch()) {
                $errors[] = 'A category with this name or slug already exists.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name, slug, description, icon, image_url)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $description, $icon, $image_url]);
                set_flash_message('success', 'Category "' . $name . '" created successfully.');
                header('Location: categories.php');
                exit;
            }
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete']) && isset($_GET['token'])) {
    $delId = (int)$_GET['delete'];
    if (verify_csrf_token($_GET['token'])) {
        // Check if destinations belong to it
        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM destinations WHERE category_id = ?");
        $cntStmt->execute([$delId]);
        $destCount = (int)$cntStmt->fetchColumn();

        if ($destCount > 0) {
            set_flash_message('error', "Cannot delete category: it currently holds {$destCount} destinations. Move or delete destinations first.");
        } else {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
            set_flash_message('success', 'Category deleted successfully.');
        }
    } else {
        set_flash_message('error', 'Invalid security token.');
    }
    header('Location: categories.php');
    exit;
}

// Fetch categories with destination counts
$categories = $pdo->query("
    SELECT c.*, COUNT(d.id) AS destination_count
    FROM categories c
    LEFT JOIN destinations d ON c.id = d.category_id
    GROUP BY c.id
    ORDER BY c.id ASC
")->fetchAll();
?>

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

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
    <!-- Add Category Form Card -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="fas fa-folder-plus text-gold"></i> Create New Biome</h3>
        </div>
        <div style="padding: 24px;">
            <form action="categories.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="create_category" value="1">

                <div class="form-group">
                    <label for="name" class="form-label">Category Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Subterranean Caverns" required>
                </div>

                <div class="form-group">
                    <label for="icon" class="form-label">Font Awesome Icon Class</label>
                    <input type="text" id="icon" name="icon" class="form-control" value="fa-compass" placeholder="fa-mountain, fa-water, fa-tree...">
                </div>

                <div class="form-group">
                    <label for="image_url" class="form-label">Cover Image URL</label>
                    <input type="url" id="image_url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/...">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief explanation of this geographic category..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-plus"></i> Save Category
                </button>
            </form>
        </div>
    </div>

    <!-- Categories List Card -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="fas fa-layer-group text-gold"></i> Existing Categories (<?= count($categories) ?>)</h3>
        </div>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Icon</th>
                        <th>Name &amp; Slug</th>
                        <th>Destinations</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="4" class="text-center" style="padding: 24px;">No categories found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #eaf3ee; color: #166534; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas <?= e($cat['icon']) ?>"></i>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--admin-primary); font-size: 0.95rem;"><?= e($cat['name']) ?></strong><br>
                                    <code style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= e($cat['slug']) ?></code>
                                </td>
                                <td>
                                    <span class="badge-status" style="background:#e0f2fe; color:#0369a1;">
                                        <?= $cat['destination_count'] ?> Places
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btn-group" style="justify-content: flex-end;">
                                        <a href="<?= $baseUrl ?>/destinations.php?category=<?= $cat['id'] ?>" target="_blank" class="btn-icon" title="View Public Explore Filter">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <a href="categories.php?delete=<?= $cat['id'] ?>&token=<?= $csrfToken ?>" class="btn-icon btn-icon-danger" title="Delete Category" onclick="return confirmDelete('Delete this category?');">
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
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<?php
/**
 * Common Public Footer
 * Hidden Places Explorer
 */
$baseUrl = get_base_url();

// Fetch 4 top categories for footer quick links
$footerCategories = [];
try {
    $pdo = getDBConnection();
    $footerCategories = $pdo->query("SELECT id, name, slug FROM categories ORDER BY id ASC LIMIT 4")->fetchAll();
} catch (Exception $e) {
    // Graceful fallback
}
?>
</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand & Mission -->
            <div class="footer-brand">
                <a href="<?= $baseUrl ?>/index.php" class="brand-logo" style="margin-bottom: 12px;">
                    <i class="fas fa-compass"></i>
                    <span>Hidden Places</span>
                </a>
                <p>
                    Curating Earth's most breathtaking, untouched landscapes, subterranean caverns, and forgotten sanctuaries for curious and respectful travelers worldwide.
                </p>
                <div style="display: flex; gap: 14px; margin-top: 20px; font-size: 1.15rem;">
                    <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram" style="color: #92aba0;"><i class="fab fa-instagram"></i></a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" aria-label="Twitter" style="color: #92aba0;"><i class="fab fa-twitter"></i></a>
                    <a href="https://pinterest.com" target="_blank" rel="noopener" aria-label="Pinterest" style="color: #92aba0;"><i class="fab fa-pinterest"></i></a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube" style="color: #92aba0;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="footer-heading">Navigation</h4>
                <ul class="footer-links">
                    <li><a href="<?= $baseUrl ?>/index.php">Home Sanctuary</a></li>
                    <li><a href="<?= $baseUrl ?>/destinations.php">Explore All Gems</a></li>
                    <li><a href="<?= $baseUrl ?>/categories.php">Travel Categories</a></li>
                    <li><a href="<?= $baseUrl ?>/contact.php">Contact &amp; Inquiries</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="footer-heading">Secret Realms</h4>
                <ul class="footer-links">
                    <?php if (!empty($footerCategories)): ?>
                        <?php foreach ($footerCategories as $fCat): ?>
                            <li>
                                <a href="<?= $baseUrl ?>/destinations.php?category=<?= $fCat['id'] ?>">
                                    <?= e($fCat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?= $baseUrl ?>/destinations.php">All Destinations</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Community & Admin -->
            <div>
                <h4 class="footer-heading">Explorer Hub</h4>
                <ul class="footer-links">
                    <?php if (is_user_logged_in()): ?>
                        <li><a href="<?= $baseUrl ?>/favorites.php"><i class="fas fa-heart text-danger"></i> Saved Places</a></li>
                        <li><a href="<?= $baseUrl ?>/profile.php">Explorer Profile</a></li>
                        <li><a href="<?= $baseUrl ?>/logout.php">Sign Out</a></li>
                    <?php else: ?>
                        <li><a href="<?= $baseUrl ?>/login.php">Explorer Sign In</a></li>
                        <li><a href="<?= $baseUrl ?>/register.php">Create Account</a></li>
                    <?php endif; ?>
                    <li style="margin-top: 14px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.08);">
                        <a href="<?= $baseUrl ?>/admin/index.php" style="color: var(--color-gold); font-size: 0.85rem;">
                            <i class="fas fa-shield-alt"></i> Admin Dashboard
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <strong>Hidden Places Explorer</strong>. Crafted for wanderers and conservationists.
            </div>
            <div>
                Built with PHP, MySQL &amp; Vanilla JavaScript. Respect Nature &bull; Leave No Trace.
            </div>
        </div>
    </div>
</footer>

<!-- Main Frontend Scripts -->
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
</body>
</html>

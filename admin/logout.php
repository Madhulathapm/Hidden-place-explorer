<?php
/**
 * Admin Logout
 * Hidden Places Explorer
 */
require_once __DIR__ . '/../config/session.php';

logout_admin();
set_flash_message('info', 'Administrator signed out safely.');
header('Location: ' . get_base_url() . '/admin/index.php');
exit;

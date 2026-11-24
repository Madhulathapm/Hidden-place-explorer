<?php
/**
 * User Logout
 * Hidden Places Explorer
 */
require_once __DIR__ . '/config/session.php';

logout_user();
set_flash_message('info', 'You have been safely signed out. Safe travels!');
header('Location: ' . get_base_url() . '/index.php');
exit;

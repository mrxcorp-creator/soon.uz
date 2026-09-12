<?php
/**
 * User Logout
 */

require_once __DIR__ . '/../config/init.php';

// Clear user session
unset($_SESSION['user_id'], $_SESSION['user_name']);

// Regenerate session ID for security
session_regenerate_id(true);

redirect('/', 'Chiqish amalga oshirildi', 'success');

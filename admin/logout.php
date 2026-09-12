<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    logAdminAction($_SESSION['admin_id'], 'Admin logout', 'Successful logout');
}

session_destroy();
redirect('/admin/login.php', 'Chiqish amalga oshirildi', 'success');

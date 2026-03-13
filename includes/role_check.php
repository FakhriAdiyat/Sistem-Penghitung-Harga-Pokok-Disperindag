<?php
/**
 * Role gate for pages.
 *
 * Usage:
 * - Default (admin-only): require_once 'includes/role_check.php';
 * - Custom roles:
 *     $allowed_roles = ['admin', 'member'];
 *     require_once 'includes/role_check.php';
 */

if (!isset($_SESSION)) {
    // In case a page includes role_check before starting a session.
    session_start();
}

$allowed_roles = $allowed_roles ?? ['admin'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles, true)) {
    // All current callers are in project root, so this is correct.
    header("Location: dashboard.php");
    exit;
}


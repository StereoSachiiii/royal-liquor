<?php
/**
 * Logout Handler
 * Destroys session and redirects to home
 */
require_once dirname(__DIR__) . "/components/header.php";

// Destroy session
$session->logout();

// Redirect to home
header('Location: ' . getPageUrl('home'));
exit;

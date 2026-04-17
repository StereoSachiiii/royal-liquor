<?php
require_once __DIR__ . '/../../src/Core/bootstrap.php';
$session = \App\Core\Session::getInstance();

// Perform logout
$session->logout();

// Redirect to login page
header("Location: login.php");
exit;

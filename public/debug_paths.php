<?php
header('Content-Type: text/plain');
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "dirname(PHP_SELF): " . dirname($_SERVER['PHP_SELF']) . "\n";

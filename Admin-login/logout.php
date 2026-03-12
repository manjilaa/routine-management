<?php
/**
 * Admin Logout Page
 */

session_start();

// Unset only admin relevant sessions or just clear all for simplicity
session_unset();
session_destroy();

header("Location: index.php");
exit();

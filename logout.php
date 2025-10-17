<?php
session_start();
require_once 'includes/config.php';

// Destroy all session data
session_destroy();

// Redirect to login page
redirect('login.php');
?>

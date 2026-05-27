<?php
// Logout handler
session_start();

// Properly clean up session data before destroying
session_unset();
session_destroy();

// Redirect to home
header('Location: index.php');
exit();

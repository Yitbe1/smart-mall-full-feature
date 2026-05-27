<?php
// Logout handler
session_start();

// Regenerate and destroy to prevent session fixation
session_regenerate_id(true);
session_unset();
session_destroy();

// Redirect to home
header('Location: index.php');
exit();

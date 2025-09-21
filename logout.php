<?php
session_start();
session_unset();
session_destroy();

// Redirect back to index (login/sign up page)
header("Location: index.php");
exit;

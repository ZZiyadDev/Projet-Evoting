<?php
// File: includes/auth_session.php

// Start the session on every page that includes this file
session_start();

// Check if the user is logged in. 
// If they are not, redirect them to the login page.
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}
?>

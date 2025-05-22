<?php
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_session'])) {
        header("location:index.php");
        exit();
    }

    // Destroy session and redirect to login page
    session_unset();
    session_destroy();
    header('location:index.php');
    exit();
?>
<?php
    include("../dbcon.php");

    session_start();

    if (!isset($_SESSION['user_session'])) {
        header("location:../index.php");
        exit();
    }

    // Get ID from URL parameter
    $id = $_GET['id'];

    // Validate ID to prevent SQL injection
    if (!is_numeric($id)) {
        header("location:view.php");
        exit();
    }

    // Prepare delete query
    $delete_sql = "DELETE FROM stock WHERE id = '$id'";

    // Execute delete query
    $delete_query = mysqli_query($con, $delete_sql);

    if ($delete_query) {
        // Redirect back to view page with success message
        header("location:view.php?message=Record deleted successfully");
    } else {
        // Redirect back with error message
        header("location:view.php?error=Error deleting record");
    }
    exit();
?>
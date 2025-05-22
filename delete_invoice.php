<?php
	session_start();

	// Check if user is logged in
	if (!isset($_SESSION['user_session'])) {
		header("location:index.php");
		exit();
	}

	include("dbcon.php");

	// Get parameters from URL
	$product_id = $_GET['id'];
	$medicine_name = $_GET['name'];
	$expire_date = $_GET['expire_date'];
	$quantity = $_GET['quantity'];
	$invoice_number = $_GET['invoice_number'];

	// Update stock when medicine is deleted from sale
	// Restore quantities back to available stock
	$update_sql = "UPDATE stock 
				SET used_quantity = used_quantity - '$quantity', 
					remain_quantity = remain_quantity + '$quantity', 
					status = 'Available' 
				WHERE medicine_name = '$medicine_name' 
				AND expire_date = '$expire_date'";

	$update_query = mysqli_query($con, $update_sql);

	// Delete item from on_hold table (sales cart)
	$delete_sql = "DELETE FROM `on_hold` WHERE id = '$product_id'";
	$delete_query = mysqli_query($con, $delete_sql);

	// Redirect based on success or failure
	if ($delete_query) {
		header("location:home.php?invoice_number=$invoice_number");
		exit();
	} else {
		echo "Sorry, unable to delete the item.";
	}
?>
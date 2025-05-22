<?php
	session_start();

	// Check if user is logged in
	if (!isset($_SESSION['user_session'])) {
		header("location:index.php");
		exit();
	}

	include("dbcon.php");

	if (isset($_POST['submit'])) {
		// Get form data
		$invoice_number = $_GET['invoice_number'];
		$product = $_POST['product'];
		$expire_date = $_POST['expire_date'];
		$qty = $_POST['qty'];
		$date = $_POST['date'];

		// Select product details from stock
		$select_sql = "SELECT * FROM stock WHERE medicine_name = '$product' AND expire_date = '$expire_date'";
		$select_query = mysqli_query($con, $select_sql);

		while ($row = mysqli_fetch_array($select_query)) {
			$medicine_name = $row['medicine_name'];
			$category = $row['category'];
			$quantity = $row['quantity'];
			$sell_type = $row['sell_type'];
			$cost = $row['selling_price'];
			$profit = $row['profit_price'];
			$expire_date = $row['expire_date'];
		}

		// Update stock quantities
		$update_sql = "UPDATE stock 
					SET used_quantity = used_quantity + '$qty', 
						remain_quantity = remain_quantity - '$qty' 
					WHERE medicine_name = '$product' AND expire_date = '$expire_date'";
		$update_query = mysqli_query($con, $update_sql);

		// Check remaining quantity after update
		$select_sql = "SELECT * FROM stock WHERE medicine_name = '$product' AND expire_date = '$expire_date'";
		$select_query = mysqli_query($con, $select_sql);

		while ($row = mysqli_fetch_array($select_query)) {
			$quantity = $row['remain_quantity'];
		}

		echo "<h1>....LOADING</h1>";

		// Mark as unavailable if quantity is zero or less
		if ($quantity <= 0) {
			$update_quantity_sql = "UPDATE stock 
									SET status = 'Unavailable' 
									WHERE medicine_name = '$product' AND expire_date = '$expire_date'";
			$update_quantity_query = mysqli_query($con, $update_quantity_sql);
		}

		// Calculate amounts
		$amount = $qty * $cost;
		$profit_amt = $profit * $qty;

		// Insert into on_hold table
		$insert_sql = "INSERT INTO on_hold 
					VALUES('', '$invoice_number', '$medicine_name', '$category', '$expire_date', 
							'$qty', '$sell_type', '$cost', '$amount', '$profit_amt', '$date')";
		$insert_query = mysqli_query($con, $insert_sql);

		if ($insert_query) {
			header("location:home.php?invoice_number=$invoice_number");
			exit();
		}
	}
?>
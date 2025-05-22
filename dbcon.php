<?php
	$host = "localhost";
	$port = "3307";
	$user = "root";
	$password = "";
	$dbname = "medicinestockdb";

	// Turn on error reporting for debugging
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	try {
		// Using the port parameter in mysqli_connect
		$con = mysqli_connect($host, $user, $password, $dbname, $port);

		if (!$con) {
			die("Connection failed: " . mysqli_connect_error());
		}
	} catch (Exception $e) {
		die("Connection error: " . $e->getMessage());
	}
?>
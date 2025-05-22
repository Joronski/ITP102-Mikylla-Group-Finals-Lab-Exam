<?php
	session_start();

	if (!isset($_SESSION['user_session'])) {
		header("location:index.php");
		exit();
	} else {
		try {
			backup_tables('localhost', 'root', '', 'medicinestockdb');
		} catch (Exception $e) {
			echo "<div style='color:red; font-weight:bold; padding:10px;'>";
			echo "Database Error: " . htmlspecialchars($e->getMessage());
			echo "<br>Please check that your MySQL server is running and the database exists.";
			echo "</div>";
			echo "<a href='home.php'>Return to Home</a>";
		}
	}

	/**
	 * Backup the database or specific tables
	 * @param string $host Database host
	 * @param string $user Database user
	 * @param string $pass Database password
	 * @param string $name Database name
	 * @param mixed $tables Tables to backup ('*' for all tables or array of table names)
	 */
	function backup_tables($host, $user, $pass, $name, $tables = '*')
	{
		// Try connecting with port specification if needed
		$port = 3307; 
		$link = mysqli_connect($host, $user, $pass, $name, $port);
		
		if (!$link) {
			// If connection fails, try 127.0.0.1 instead of localhost
			$link = mysqli_connect('127.0.0.1', $user, $pass, $name, $port);
			
			if (!$link) {
				throw new Exception("Could not connect to MySQL: " . mysqli_connect_error());
			}
		}
		
		// Get all tables if '*' is specified
		if ($tables == '*') {
			$tables = array();
			$result = mysqli_query($link, 'SHOW TABLES');
			if (!$result) {
				throw new Exception("Error showing tables: " . mysqli_error($link));
			}
			while ($row = mysqli_fetch_row($result)) {
				$tables[] = $row[0];
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', $tables);
		}
		
		if (empty($tables)) {
			throw new Exception("No tables found in database.");
		}
		
		$return = '';
		
		// Cycle through each table
		foreach ($tables as $table) {
			$result = mysqli_query($link, 'SELECT * FROM ' . mysqli_real_escape_string($link, $table));
			if (!$result) {
				throw new Exception("Error selecting from table $table: " . mysqli_error($link));
			}
			
			$num_fields = mysqli_num_fields($result);
			
			$return .= 'DROP TABLE IF EXISTS `' . $table . '`;';
			$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . mysqli_real_escape_string($link, $table)));
			$return .= "\n\n" . $row2[1] . ";\n\n";

			for ($i = 0; $i < $num_fields; $i++) {
				while ($row = mysqli_fetch_row($result)) {
					$return .= 'INSERT INTO `' . $table . '` VALUES(';
					for ($j = 0; $j < $num_fields; $j++) {
						if (isset($row[$j])) {
							$row[$j] = addslashes($row[$j]);
							$row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
							$return .= '"' . $row[$j] . '"';
						} else {
							$return .= 'NULL';
						}
						if ($j < ($num_fields - 1)) {
							$return .= ',';
						}
					}
					$return .= ");\n";
				}
			}
			$return .= "\n\n\n";
		}
		
		// Save file
		$backup_dir = "backup";
		if (!file_exists($backup_dir)) {
			if (!mkdir($backup_dir, 0777, true)) {
				throw new Exception("Failed to create backup directory");
			}
		}
		
		$date = date("dMY-His");
		$invoice_number = $_GET['invoice_number'] ?? '';
		$backup_filename = $backup_dir . "/db-backup-" . $date . ".sql";
		
		$handle = fopen($backup_filename, 'w+');
		if (!$handle) {
			throw new Exception("Could not open file for writing. Check permissions.");
		}
		
		fwrite($handle, $return);
		fclose($handle);

		echo "
		<input type='hidden' value='{$invoice_number}' id='invoice_number'>
		<script type='text/javascript' src='js/jquery.js'></script>
		<script type='text/javascript'>
			if (window.confirm('Backup Successfully Created: " . htmlspecialchars($backup_filename) . "')) {
				var invoice_number = $('#invoice_number').val();
				window.location.href = 'home.php?invoice_number=' + invoice_number;
			}
		</script>";
	}
?>
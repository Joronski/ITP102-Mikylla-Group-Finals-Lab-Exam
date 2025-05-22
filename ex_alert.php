<?php
	session_start();

	// Check if user is logged in
	if (!isset($_SESSION['user_session'])) {
		header("location:index.php");
		exit();
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Expiry Alert - MHMS</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<script type="text/javascript">
			/**
			 * Search function for medicine names
			 * Filters the table based on medicine name input
			 */
			function med_name() {
				var input, filter, table, tr, td, i;
				input = document.getElementById("name_med");
				filter = input.value.toUpperCase();
				table = document.getElementById("table1");
				tr = table.getElementsByTagName("tr");

				// Loop through all table rows and hide those that don't match the search
				for (i = 0; i < tr.length; i++) {
					td = tr[i].getElementsByTagName("td")[0];
					if (td) {
						if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
							tr[i].style.display = "";
						} else {
							tr[i].style.display = "none";
						}
					}
				}
			}
		</script>
	</head>
	<body>
		<div class="expire" color="green">
			<!-- Page Header -->
			<font size="5">Medicine Going to Expire</font>
			<br>
			<hr>

			<!-- Search Input -->
			<input type="text"
				id="name_med"
				size="4"
				onkeyup="med_name()"
				placeholder="Search for Medicine names.."
				title="Type in a name">

			<!-- Scrollable Table Container -->
			<div style="overflow-x:auto; overflow-y: auto; height: 230px;">
				<table class="table table-bordered table-hover" id="table1">
					<thead>
						<tr style="background-color: #383838; color: #FFFFFF;">
							<th>Medicine</th>
							<th>Expiry</th>
							<th>Available</th>
							<th>Cost</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						<?php
						include("dbcon.php");

						// Calculate date 6 months from now
						$date = date('d-m-Y');
						$inc_date = date("Y-m-d", strtotime("+6 month", strtotime($date)));

						// Query for medicines expiring within 6 months
						$select_sql = "SELECT * FROM stock 
									WHERE expire_date <= '$inc_date' 
									AND status = 'Available' 
									ORDER BY expire_date ASC";

						$result = mysqli_query($con, $select_sql);

						// Display each medicine
						while ($row = mysqli_fetch_array($result)):
						?>
							<tr>
								<td><?php echo htmlspecialchars($row['medicine_name']); ?></td>
								<td>
									<font color="red">
										<?php echo htmlspecialchars($row['expire_date']); ?>
									</font>
								</td>
								<td>
									<?php
									echo htmlspecialchars($row['remain_quantity']) .
										"(" . htmlspecialchars($row['sell_type']) . ")";
									?>
								</td>
								<td><?php echo htmlspecialchars($row['actual_price']); ?></td>
								<td>
									<?php
									echo $row['actual_price'] * $row['remain_quantity'];
									?>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>
<?php
    error_reporting(1);
    include("dbcon.php");

    session_start();

    if (!isset($_SESSION['user_session'])) {
        header("location:index.php");
        exit;
    }

    // Get POST data
    $hid_qty = $_POST['hid_qty'];
    $upd_qty = $_POST['qty'];
    $med_id = $_POST['med_id'];
    $med_name = $_POST['med_name'];
    $med_cat = $_POST['med_cat'];
    $ex_date = $_POST['ex_date'];

    // Select stock information
    $select_sql = "SELECT * FROM stock 
                WHERE medicine_name = '$med_name' 
                AND category = '$med_cat' 
                AND expire_date = '$ex_date'";

    $result1 = mysqli_query($con, $select_sql);

    while ($row = mysqli_fetch_array($result1)) {
        $amount = $upd_qty * $row['selling_price'];
        $profit_amount = $upd_qty * $row['profit_price'];
        $quantity = $row['act_remain_quantity'];
    }

    echo $avai_qty;

    // Check if requested quantity is available
    if ($upd_qty > $quantity) {
        // Handle insufficient quantity case
        // This block is empty in the original code
    } else {
        // Update stock quantities
        $update_sql = "UPDATE stock 
                    SET used_quantity = (used_quantity - '$hid_qty') + '$upd_qty',
                        remain_quantity = (remain_quantity + '$hid_qty') - '$upd_qty'
                    WHERE medicine_name = '$med_name' 
                    AND category = '$med_cat' 
                    AND expire_date = '$ex_date'";

        $result = mysqli_query($con, $update_sql);

        // Update on_hold table
        $update_sql1 = "UPDATE on_hold 
                        SET qty = '$upd_qty',
                            amount = '$amount',
                            profit_amount = '$profit_amount'
                        WHERE id = '$med_id'";

        $result2 = mysqli_query($con, $update_sql1);

        // Get updated remaining quantity
        $select_sql1 = "SELECT remain_quantity FROM stock 
                        WHERE medicine_name = '$med_name' 
                        AND category = '$med_cat' 
                        AND expire_date = '$ex_date'";

        $result3 = mysqli_query($con, $select_sql1);

        while ($row = mysqli_fetch_array($result3)) {
            $remain_quantity = $row['remain_quantity'];
        }

        // Update status based on remaining quantity
        if ($remain_quantity <= 0) {
            // Update status to 'Unavailable' if quantity is zero or less
            $update_quantity_sql = "UPDATE stock 
                                SET status = 'Unavailable' 
                                WHERE medicine_name = '$med_name' 
                                AND expire_date = '$ex_date'";

            $update_quantity_query = mysqli_query($con, $update_quantity_sql);
        }

        if ($remain_quantity > 0) {
            // Update status to 'Available' if quantity is greater than zero
            $update_quantity_sql1 = "UPDATE stock 
                                    SET status = 'Available' 
                                    WHERE medicine_name = '$med_name' 
                                    AND expire_date = '$ex_date'";

            $update_quantity_query1 = mysqli_query($con, $update_quantity_sql1);
        }
    }
?>
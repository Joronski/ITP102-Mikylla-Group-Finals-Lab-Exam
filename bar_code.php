<?php
      include("dbcon.php");

      session_start();

      if (!isset($_SESSION['user_session'])) {
         header("location:index.php");
         exit();
      }

      $bar_code = mysqli_real_escape_string($con, $_POST['bar_code'] ?? '');

      $query = "SELECT * FROM stock 
               WHERE bar_code = '{$bar_code}' 
               AND status = 'Available'";

      $result = mysqli_query($con, $query);

      $data = array();

      while ($row = mysqli_fetch_array($result)) {
         $data[] = $row["medicine_name"] . "," .
            $row['expire_date'] . ",(" .
            $row['sell_type'] . ")";
      }

      echo json_encode($data);
?>
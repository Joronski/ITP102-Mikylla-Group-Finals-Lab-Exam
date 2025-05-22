<?php
   include("dbcon.php");

   session_start();

   if (!isset($_SESSION['user_session'])) {
      header("location:index.php");
      exit();
   }

   $drug_result = mysqli_real_escape_string($con, $_POST['medicine_name'] ?? '');
   $expire_date = $_POST['expire_date'] ?? '';

   $query = "SELECT remain_quantity 
            FROM stock 
            WHERE medicine_name = '{$drug_result}' 
            AND expire_date = '{$expire_date}' 
            AND status = 'Available'";

   $result = mysqli_query($con, $query);

   $data = array();

   if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_array($result)) {
         $data[] = $row["remain_quantity"];
      }
      
      echo json_encode($data);
   }
?>
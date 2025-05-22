<?php
require_once('vendor/excel_reader2.php');
require_once('vendor/SpreadsheetReader.php');
require_once('../dbcon.php');

session_start();

if (!isset($_SESSION['user_session'])) {
    header("location:../index.php");
    exit();
}

if (isset($_POST['submit'])) {
    @$invoice_number = $_GET['invoice_number'];

    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    if (in_array($_FILES["file"]["type"], $allowedFileType)) {
        $targetPath = 'upload_xls/' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
        
        $Reader = new SpreadsheetReader($targetPath);
        $sheetCount = count($Reader->sheets());

        for ($i = 0; $i < $sheetCount; $i++) {
            $Reader->ChangeSheet($i);

            foreach ($Reader as $Row) {
                // Initialize variables
                $medicine_name = isset($Row[0]) ? mysqli_real_escape_string($con, $Row[0]) : "";
                $category = isset($Row[1]) ? mysqli_real_escape_string($con, $Row[1]) : "";
                $quantity = isset($Row[2]) ? mysqli_real_escape_string($con, $Row[2]) : "";
                $used_quantity = isset($Row[3]) ? mysqli_real_escape_string($con, $Row[3]) : "";
                $remain_quantity = isset($Row[4]) ? mysqli_real_escape_string($con, $Row[4]) : "";
                $act_remain_quantity = isset($Row[5]) ? mysqli_real_escape_string($con, $Row[5]) : "";
                $register_date = isset($Row[6]) ? mysqli_real_escape_string($con, $Row[6]) : "";
                $expire_date = isset($Row[7]) ? mysqli_real_escape_string($con, $Row[7]) : "";
                $company = isset($Row[8]) ? mysqli_real_escape_string($con, $Row[8]) : "";
                $sell_type = isset($Row[9]) ? mysqli_real_escape_string($con, $Row[9]) : "";
                $actual_price = isset($Row[10]) ? mysqli_real_escape_string($con, $Row[10]) : "";
                $selling_price = isset($Row[11]) ? mysqli_real_escape_string($con, $Row[11]) : "";
                $profit_price = isset($Row[12]) ? mysqli_real_escape_string($con, $Row[12]) : "";
                $status = isset($Row[13]) ? mysqli_real_escape_string($con, $Row[13]) : "";

                // Check if at least one field has data
                if (!empty($medicine_name) || !empty($category) || !empty($quantity) || 
                    !empty($used_quantity) || !empty($remain_quantity) || !empty($act_remain_quantity) || 
                    !empty($register_date) || !empty($expire_date) || !empty($company) || 
                    !empty($sell_type) || !empty($actual_price) || !empty($selling_price) || 
                    !empty($profit_price) || !empty($status)) {

                    // Fixed SQL query (there were some errors in the original)
                    $query = "INSERT INTO stock (
                        medicine_name, category, quantity, used_quantity, remain_quantity, 
                        act_remain_quantity, register_date, expire_date, company, sell_type, 
                        actual_price, selling_price, profit_price, status
                    ) VALUES (
                        '$medicine_name', '$category', '$quantity', '$used_quantity', '$remain_quantity',
                        '$act_remain_quantity', '$register_date', '$expire_date', '$company', '$sell_type',
                        '$actual_price', '$selling_price', '$profit_price', '$status'
                    )";

                    $result = mysqli_query($con, $query);
                
                    if (!empty($result)) {
                        echo "Excel Data Imported into the Database";
                        header("location:view.php?invoice_number=$invoice_number");
                        exit();
                    } else {
                        echo "Problem in Importing Excel Data";
                    }
                }
            }
        }
    } else {
        $type = "error";
        $message = "Invalid File Type. Upload Excel File.";
    }
}
?>
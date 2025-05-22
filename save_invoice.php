<?php
    include "dbcon.php";
    require "fpdf.php";

    session_start();

    if (!isset($_SESSION['user_session'])) {
        header("location:index.php");
        exit;
    }

    class myPDF extends FPDF {
        function header() {
            $invoice_number = $_POST['invoice_number'];
            $date = $_POST['date'];

            $this->SetFont('Arial', 'B', 20);
            $this->Cell(276, 10, 'Simple Pharmacy Invoice', 0, 0, 'C');
            $this->Ln(20);
            $this->Cell(80, 40, 'Invoice Number:' . $invoice_number, 0, 0, 'C');
            $this->Ln();
            $this->Cell(50, -10, $date, 0, 0, 'C');
            $this->Ln(10);
        }

        function footer() {
            $this->Cell(276, 10, 'Thank You', 0, 0, 'C');
            $this->Ln(20);
        }

        function headerTable() {
            $this->SetFont('Times', 'B', 15);
            $this->Cell(40, 10, 'Product Name', 1, 0, 'C');
            $this->Cell(40, 10, 'Category', 1, 0, 'C');
            $this->Cell(40, 10, 'Qty', 1, 0, 'C');
            $this->Cell(50, 10, 'Price', 1, 0, 'C');
            $this->Cell(100, 10, 'Amount', 1, 0, 'C');
            $this->Ln();
        }

        function viewTable() {
            include "dbcon.php";

            $paid_amount = $_POST['paid_amount'];
            $invoice_number = $_POST['invoice_number'];

            $select_sql = "SELECT * FROM on_hold WHERE invoice_number = '$invoice_number'";
            $select_query = mysqli_query($con, $select_sql);

            while ($row = mysqli_fetch_array($select_query)) {
                $this->SetFont('Times', '', 12);
                $this->Cell(40, 10, $row['medicine_name'], 1, 0, 'C');
                $this->Cell(40, 10, $row['category'], 1, 0, 'C');
                $this->Cell(40, 10, $row['qty'] . "(" . $row['type'] . ")", 1, 0, 'C');
                $this->Cell(50, 10, $row['cost'], 1, 0, 'C');
                $this->Cell(100, 10, $row['amount'], 1, 0, 'C');
                $this->Ln();
            }

            // Calculate total amount
            $select_sql = "SELECT SUM(amount) FROM on_hold WHERE invoice_number = '$invoice_number'";
            $select_query = mysqli_query($con, $select_sql);

            while ($row = mysqli_fetch_array($select_query)) {
                $amount = $row['SUM(amount)'];

                // Display total
                $this->Cell(170, 10, 'Total', 1, 0, 'C');
                $this->Cell(100, 10, $amount, 1, 0, 'C');
                $this->Ln();

                // Display paid amount
                $this->SetFont('Times', 'B', 15);
                $this->Cell(170, 10, 'Paid Amount', 1, 0, 'C');
                $this->Cell(100, 10, $paid_amount, 1, 0, 'C');
                $this->Ln();

                // Display change amount
                $this->SetFont('Times', 'B', 20);
                $this->Cell(170, 10, 'Change Amount', 1, 0, 'C');
                $this->Cell(100, 10, $paid_amount - $amount, 1, 0, 'C');
                $this->Ln(20);
            }
        }

        function invoice_number() { 
            // Outputting a New Invoice Number
            $chars = "09302909209300923";
            srand((float)microtime() * 1000000);
            $i = 1;
            $pass = '';

            while ($i <= 7) {
                $num = rand() % 10;
                $tmp = substr($chars, $num, 1);
                $pass = $pass . $tmp;
                $i++;
            }
            return $pass;
        }
    }

    $date1 = date("YMd");

    // Create directories if they don't exist
    if (!file_exists("C:/invoices")) {
        mkdir("C:/invoices");
    }

    if (!file_exists("C:/invoices/$date1")) {
        mkdir("C:/invoices/$date1");
    }

    if (!file_exists("C:/invoices/all_invoices")) {
        mkdir("C:/invoices/all_invoices");
    }

    if (isset($_POST['submit'])) {
        $invoice_number = $_POST['invoice_number'];
        $date = $_POST['date'];
        $medicine_name = $_POST['medicine_name'];
        $medicines = implode(",", $medicine_name);
        $quantity = $_POST['qty'];
        $qty = implode(",", $quantity);
        $qty_type = $qty;
        $filename = "i-" . $invoice_number . ".pdf";

        // Generate PDF
        $pdf = new myPDF();
        $pdf->AddPage('L', 'A4', 0);
        $pdf->headerTable();
        $pdf->viewTable();
        $pdf->Output('C:/invoices/' . $date1 . '/' . $filename, 'F');
        $pdf->Output('C:/invoices/all_invoices/' . $filename, 'F');

        // Get data from on_hold table
        $select_on_hold = "SELECT * FROM on_hold WHERE invoice_number = '$invoice_number'";
        $select_on_hold_query = mysqli_query($con, $select_on_hold);
        $row = mysqli_fetch_array($select_on_hold_query);

        $med_name = $row['medicine_name'];
        $category = $row['category'];
        $expire_date = $row['expire_date'];

        // Get stock information
        $select_stock = "SELECT * FROM stock 
                        WHERE medicine_name = '$med_name' 
                        AND category = '$category' 
                        AND expire_date = '$expire_date'";
        $select_stock_query = mysqli_query($con, $select_stock);
        $row = mysqli_fetch_array($select_stock_query);
        $remain_quantity = $row['remain_quantity'];

        // Calculate totals from on_hold
        $select_sql = "SELECT invoice_number, SUM(amount), SUM(profit_amount) 
                      FROM on_hold 
                      WHERE invoice_number = '$invoice_number'";
        $select_query = mysqli_query($con, $select_sql);

        while ($row = mysqli_fetch_array($select_query)) {
            $on_hold_invoice = $row['invoice_number'];
            $total_amount = $row['SUM(amount)'];
            $total_profit = $row['SUM(profit_amount)'];
        }

        // Insert into sales table
        $insert_sql = "INSERT INTO sales 
                      VALUES('', '$invoice_number', '$medicines', '$qty_type', '$total_amount', '$total_profit', '$date')";
        $insert_query = mysqli_query($con, $insert_sql);

        if ($insert_query) {
            // Update stock with actual remaining quantity
            $update_stock = "UPDATE stock 
                            SET act_remain_quantity = '$remain_quantity' 
                            WHERE medicine_name = '$med_name' 
                            AND category = '$category' 
                            AND expire_date = '$expire_date'";

            $update_stock_query = mysqli_query($con, $update_stock);

            if ($update_stock_query) {
                echo "Success";
            } else {
                echo "Stock update failed";
            }
        } else {
            echo "Sales insert failed";
        }

        // Generate new invoice number and redirect
        $new_invoice_number = "CA-" . $pdf->invoice_number();
        header("location:home.php?invoice_number=$new_invoice_number");
        exit;
    }
?>
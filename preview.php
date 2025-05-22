<?php
    session_start();

    if (!isset($_SESSION['user_session'])) {
        header("location:index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Invoice Preview - Mikylla's Hospital Medicine Stock DBMS</title>
        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-responsive.css">
        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="css/tcal.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/tcal.js"></script>
        
        <script type="text/javascript">
            function Clickheretoprint() {
                var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
                disp_setting += "scrollbars=yes,width=700, height=400, left=100, top=25";
                var content_vlue = document.getElementById("printable_content").innerHTML;
                
                var docprint = window.open("", "", disp_setting);
                docprint.document.open();
                docprint.document.write('<html><head><title>Invoice - Mikylla\'s Hospital Medicine Stock DBMS</title>');
                docprint.document.write('<style type="text/css">');
                docprint.document.write('body { font-size:11px; font-family:arial; font-weight:normal; }');
                docprint.document.write('table { border-collapse: collapse; width: 100%; }');
                docprint.document.write('th, td { padding: 8px; border: 1px solid #ddd; }');
                docprint.document.write('.date-time { text-align: left; font-size: 12px; margin-bottom: 10px; }');
                docprint.document.write('</style>');
                docprint.document.write('</head><body onLoad="self.print()">');
                
                // Add current date and time at print time
                var now = new Date();
                var dateStr = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                var timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true });
                docprint.document.write('<div class="date-time">');
                docprint.document.write('Date: ' + dateStr + '<br>');
                docprint.document.write('Time: ' + timeStr);
                docprint.document.write('</div>');
                
                docprint.document.write(content_vlue);
                docprint.document.write('</body></html>');
                docprint.document.close();
                docprint.focus();
            }
        </script>
    </head>
    <body>
        <?php
            $invoice_number = $_GET['invoice_number'];
            $date = $_POST['date'];
            $paid_amount = $_POST['paid_amount'];
        ?>

        <div class="container">
            <a href="home.php?invoice_number=<?php echo $_GET['invoice_number']; ?>">
                <button class="btn btn-default">
                    <i class="icon-arrow-left"></i> Back to Sales
                </button>
            </a>

            <div id="content">
                <!-- This div contains both printable content and action buttons -->
                <div id="printable_content">
                    <center>
                        <div style="font:bold 25px 'Arial';">Mikylla's Hospital Medicine Stock DBMS</div>
                        <br>
                    </center>
                    <br><br>

                    <table class="table table-bordered table-hover" border="1" cellpadding="4" 
                        cellspacing="0" style="font-family: arial; font-size: 12px;text-align:left;" width="100%">
                        <tr>
                            <td colspan="5">
                                <strong><h3>Invoice Number: <?php echo $invoice_number; ?></h3></strong>
                                <?php echo $date; ?>
                            </td>
                        </tr>
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                include("dbcon.php");
                                
                                $select_sql = "SELECT * FROM on_hold WHERE invoice_number = '$invoice_number'";
                                $select_query = mysqli_query($con, $select_sql);
                                
                                while ($row = mysqli_fetch_array($select_query)):
                            ?>
                                <tr class="record">
                                    <td>
                                        <h4><?php echo $row['medicine_name']; ?></h4>
                                    </td>
                                    <td>
                                        <h5><?php echo $row['category']; ?></h5>
                                    </td>
                                    <td>
                                        <h5><?php echo $row['qty'] . " (" . $row['type'] . ")"; ?></h5>
                                    </td>
                                    <td>
                                        <h5><?php echo $row['cost']; ?></h5>
                                    </td>
                                    <td>
                                        <h5><?php echo $row['amount']; ?></h5>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <!-- Total Amount -->
                            <tr>
                                <td colspan="4" style="text-align:right;">
                                    <strong style="font-size: 12px;">Total: &nbsp;</strong>
                                </td>
                                <td colspan="1">
                                    <strong style="font-size: 12px;">
                                        <?php
                                            $select_sql = "SELECT sum(amount) FROM on_hold WHERE invoice_number = '$invoice_number'";
                                            $select_query = mysqli_query($con, $select_sql);
                                            
                                            while ($row = mysqli_fetch_array($select_query)) {
                                                $amount = $row['sum(amount)'];
                                                echo '<h5>' . $amount . '</h5>';
                                            }
                                        ?>
                                    </strong>
                                </td>
                            </tr>

                            <!-- Paid Amount -->
                            <tr>
                                <td colspan="4" style="text-align:right;">
                                    <strong style="font-size: 12px;">Paid Amount: &nbsp;</strong>
                                </td>
                                <td colspan="1">
                                    <strong style="font-size: 12px;">
                                        <h3>₱<?php echo $paid_amount; ?></h3>
                                    </strong>
                                </td>
                            </tr>

                            <!-- Change Amount -->
                            <tr>
                                <td colspan="4" style="text-align:right;">
                                    <strong style="font-size: 18px;">&nbsp;&nbsp;Change Amount: &nbsp;</strong>
                                </td>
                                <td colspan="1">
                                    <strong style="font-size: 12px;">
                                        <h3>₱<?php echo ($paid_amount - $amount); ?></h3>
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <br/>
                <form method="POST" action="save_invoice.php">
                    <?php
                        // Hidden form fields
                        $select_sql = "SELECT * FROM on_hold WHERE invoice_number = '$invoice_number'";
                        $select_query = mysqli_query($con, $select_sql);
                        
                        while ($row = mysqli_fetch_array($select_query)) {
                            echo '<input type="hidden" name="medicine_name[]" value="' . $row['medicine_name'] . '">';
                            echo '<input type="hidden" name="ex_date" value="' . $row['expire_date'] . '">';
                            echo '<input type="hidden" name="category" value="' . $row['category'] . '">';
                            echo '<input type="hidden" name="qty[]" value="' . $row['qty'] . '(' . $row['type'] . ')">';
                        }
                    ?>
                    <input type="hidden" name="paid_amount" value="<?php echo $paid_amount; ?>">
                    <input type="hidden" name="invoice_number" value="<?php echo $invoice_number; ?>">
                    <input type="hidden" name="date" value="<?php echo $date; ?>">
                    
                    <div class="button-container">
                        <input type="submit" name="submit" class="btn btn-success btn-large" 
                            value="Submit and Make new Sales">
                        <a href="javascript:Clickheretoprint()" class="btn btn-danger btn-md" style="float: right;">
                            <i class="icon icon-print"></i> Print
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
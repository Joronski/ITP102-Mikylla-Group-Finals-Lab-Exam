<?php
    session_start();

    if (!isset($_SESSION['user_session'])) {
        header("location:../index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>MHMS - View Products</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="../css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="../css/bootstrap-responsive.css">
        <link rel="stylesheet" type="text/css" href="../src/facebox.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <script type="text/javascript" src="../js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="../js/bootstrap.min.js"></script>
        <script type="text/javascript" src="../src/facebox.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Pop-up forms configuration
                $("a[id*=popup").facebox({
                    loadingImage: '../src/img/loading.gif',
                    closeImage: '../src/img/closelabel.png'
                });
            });
        </script>
    </head>
    <body style="height: 100%">
        <!-- Header Navigation -->
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container-fluid">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>

                    <a class="brand" href="#"><b>Mikylla's Hospital Medicine Stock DBMS</b></a>
                    
                    <div class="nav-collapse">
                        <ul class="nav pull-right">
                            <!-- Low Quantity Alert -->
                            <li>
                                <?php 
                                    include("../dbcon.php");

                                    $quantity = "5";
                                    $select_sql1 = "SELECT * FROM stock WHERE remain_quantity <= '$quantity' AND status='Available'";
                                    $result1 = mysqli_query($con, $select_sql1);
                                    $row2 = $result1->num_rows;

                                    if ($row2 == 0) {
                                        echo '<a href="#" class="notification label-inverse">
                                                <span class="icon-exclamation-sign icon-large"></span>
                                              </a>';
                                    } else {
                                        echo '<a href="../qty_alert.php" class="notification label-inverse" id="popup">
                                                <span class="icon-exclamation-sign icon-large"></span>
                                                <span class="badge">' . $row2 . '</span>
                                              </a>';
                                    }
                                ?> 
                            </li>

                            <!-- Expiry Alert -->
                            <li>
                                <?php
                                    $date = date('d-m-Y');    
                                    $inc_date = date("Y-m-d", strtotime("+6 month", strtotime($date))); 
                                    $select_sql = "SELECT * FROM stock WHERE expire_date <= '$inc_date' AND status='Available'";
                                    $result = mysqli_query($con, $select_sql); 
                                    $row1 = $result->num_rows;

                                    if ($row1 == 0) {
                                        echo '<a href="#" class="notification label-inverse">
                                                <span class="icon-bell icon-large"></span>
                                              </a>';
                                    } else {
                                        echo '<a href="../ex_alert.php" class="notification label-inverse" id="popup">
                                                <span class="icon-bell icon-large"></span>
                                                <span class="badge">' . $row1 . '</span>
                                              </a>';
                                    }
                                ?>
                            </li>

                            <!-- Navigation Links -->
                            <li>
                                <a href="../home.php?invoice_number=<?php echo $_GET['invoice_number']; ?>">
                                    <span class="icon-home"></span> Home
                                </a>
                            </li>
                            
                            <li>
                                <a href="../sales_report.php?invoice_number=<?php echo $_GET['invoice_number']; ?>">
                                    <span class="icon-bar-chart"></span> Sales Report
                                </a>
                            </li>
                            
                            <li>
                                <a href="../backup.php?invoice_number=<?php echo $_GET['invoice_number']; ?>">
                                    <span class="icon-folder-open"></span> Backup
                                </a>
                            </li>
                            
                            <li>
                                <a href="../logout.php" class="link">
                                    <font color='red'><span class="icon-off"></span></font> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Header -->

        <!-- Main Content -->
        <div class="container">
            <div class="row">
                <div class="contentheader">
                    <h1>Products</h1>
                </div>
                <br>
        
                <!-- Search Filters -->
                <input type="text" 
                      id="name_med1" 
                      size="4" 
                      onkeyup="med_name1()" 
                      placeholder="Filter using BarCode" 
                      title="Type BarCode">
                
                <input type="text" 
                      size="4" 
                      id="med_quantity" 
                      onkeyup="quanti()" 
                      placeholder="Filter using Medicine Name" 
                      title="Type Medicine Name">
                
                <input type="text" 
                      size="4" 
                      id="med_exp_date" 
                      onkeyup="exp_date()" 
                      placeholder="Filter using Registered Date" 
                      title="Type in registered date">
                
                <input type="text" 
                      size="4" 
                      id="med_status" 
                      onkeyup="stat_search()" 
                      placeholder="Filter using Profit Margin" 
                      title="Type in profit amount">
              
                <!-- Add New Medicine Button -->
                <a href="index.php?invoice_number=<?php echo $_GET['invoice_number']; ?>" id="popup">
                    <button class="btn btn-success btn-md" name="submit">
                        <span class="icon-plus-sign icon-large"></span> Add New Medicine
                    </button>
                </a>
            </div>
        </div>

        <!-- Total Medicines Count -->
        <?php
            include('../dbcon.php');
            $select_sql = "SELECT * FROM stock ORDER BY quantity";
            $select_query = mysqli_query($con, $select_sql);
            $row = mysqli_num_rows($select_query);
        ?>

        <div style="text-align:center;">
            Total Medicines: <font color="green" style="font:bold 22px 'Aleo';">[<?php echo $row; ?>]</font>
        </div>

        <!-- Products Table -->
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <form method="POST">
                        <div style="height: auto;">
                            <table id="table0" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr style="background-color: #383838; color: #FFFFFF;">
                                        <th width="3%">Code</th>
                                        <th width="3%">Medicine</th>
                                        <th width="1%">Category</th>
                                        <th width="5%">Registered Qty</th>
                                        <th width="1%">Sold Qty</th>
                                        <th width="1%">Remain Qty</th>
                                        <th width="1%">Registered</th>
                                        <th style="background-color: #c53f3f;" width="1%">Expiry</th>
                                        <th width="1%">Remark</th>     
                                        <th width="2%">Actual Price</th>
                                        <th width="2%">Selling Price</th>
                                        <th width="2%">Profit</th>
                                        <th width="3%">Status</th>
                                        <th width="5%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        include("../dbcon.php"); 
                                        $sql = "SELECT id, bar_code, medicine_name, category, quantity, used_quantity, 
                                                      remain_quantity, act_remain_quantity, register_date, expire_date, 
                                                      company, sell_type, actual_price, selling_price, profit_price, status 
                                                FROM stock 
                                                ORDER BY id DESC"; 
                                        $result = mysqli_query($con, $sql); 

                                        while ($row = mysqli_fetch_array($result)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $row['bar_code']; ?></td>
                                            <td><?php echo $row['medicine_name']; ?></td>
                                            <td><?php echo $row['category']; ?></td>
                                            <td>
                                                <?php echo $row['quantity'] . "&nbsp;&nbsp;(<strong><i>" . $row['sell_type'] . "</i></strong>)"; ?>
                                            </td>              
                                            <td><?php echo $row['used_quantity']; ?></td>
                                            <td><?php echo $row['remain_quantity']; ?></td>
                                            <td><?php echo date("d-m-Y", strtotime($row['register_date'])); ?></td>
                                            <td><?php echo date("d-m-Y", strtotime($row['expire_date'])); ?></td>
                                            <td><?php echo $row['company']; ?></td>
                                            <td><?php echo $row['actual_price']; ?></td>
                                            <td><?php echo $row['selling_price']; ?></td>
                                            <td><?php echo $row['profit_price']; ?></td>
                                            <td>
                                                <?php 
                                                    $status = $row['status'];
                                                    if ($status == 'Available') {
                                                        echo '<span class="label label-success">' . $status . '</span>';
                                                    } else {
                                                        echo '<span class="label label-danger">' . $status . '</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <a id="popup" href="update_view.php?id=<?php echo $row['id']; ?>&invoice_number=<?php echo $_GET['invoice_number']; ?>">
                                                    <button class="btn btn-info">
                                                        <span class="icon-edit"></span>
                                                    </button>
                                                </a>
                                                <button class="btn btn-danger delete" id="<?php echo $row['id']; ?>">
                                                    <span class="icon-trash"></span>&nbsp;
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </form> 
                </div>
            </div>
        </div>

        <!-- JavaScript Functions -->
        <script type="text/javascript">
            // Search for Medicine by BarCode
            function med_name1() {
                var input, filter, table, tr, td, i;
                input = document.getElementById("name_med1");
                filter = input.value.toUpperCase();
                table = document.getElementById("table0");
                tr = table.getElementsByTagName("tr");
                
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

            // Search for Medicine by Name
            function quanti() {
                var input, filter, table, tr, td, i;
                input = document.getElementById("med_quantity");
                filter = input.value.toUpperCase();
                table = document.getElementById("table0");
                tr = table.getElementsByTagName("tr");
                
                for (i = 0; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[1];
                    if (td) {
                        if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }       
                }
            }

            // Search by Registered Date
            function exp_date() {
                var input, filter, table, tr, td, i;
                input = document.getElementById("med_exp_date");
                filter = input.value.toUpperCase();
                table = document.getElementById("table0");
                tr = table.getElementsByTagName("tr");
                
                for (i = 0; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[6];
                    if (td) {
                        if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }       
                }
            }

            // Search by Profit
            function stat_search() {
                var input, filter, table, tr, td, i;
                input = document.getElementById("med_status");
                filter = input.value.toUpperCase();
                table = document.getElementById("table0");
                tr = table.getElementsByTagName("tr");
                
                for (i = 0; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[11];
                    if (td) {
                        if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }       
                }
            }

            // Delete confirmation
            $(".delete").click(function() {
                var element = $(this);
                var del_id = element.attr("id");
                var info = 'id=' + del_id;

                if (confirm("Delete This Product! Are You Sure?")) {
                    $.ajax({
                        type: "GET",
                        url: 'delete.php',
                        data: info,
                        success: function() {
                            location.reload(true);
                        },
                        error: function() {
                            alert("Error occurred while deleting");
                        }
                    });
                }
                return false;
            });
        </script>
    </body>
</html>
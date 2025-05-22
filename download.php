<?php
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_session'])) {
        header("location:index.php");
        exit();
    }

    // Get invoice number from URL
    $invoice_number = $_GET['invoice_number'];

    // Define the invoice directory with the correct subfolder
    $invoice_directory = "C:/invoices/all_invoices"; 
    $filename = "i-" . $invoice_number . ".pdf";
    $file_path = $invoice_directory . "/" . $filename;

    // Check if file exists
    if (file_exists($file_path)) {
        // Set proper content type for PDF
        header("Content-Type: application/pdf");
        header("Content-Length: " . filesize($file_path));
        header("Content-Disposition: attachment; filename=" . $filename);
        
        // Clear output buffers
        if(ob_get_level()) ob_end_clean();
        
        // Output file content and exit
        readfile($file_path);
        exit();
    } else {
        // More helpful error message
        echo "Invoice file not found. Looking for: " . $file_path;
        error_log("Failed to find invoice file: " . $file_path);
    }
?>
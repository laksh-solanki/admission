<?php
// ====================================================================
// Header Layout File
// This file initializes sessions, detects path depth to dynamically
// link CSS/JS assets, and renders the HTML <head> and styling links.
// ====================================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Compute the base path to correctly reference assets regardless of page depth
$base_path = "";
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if (in_array($current_dir, ['admin', 'staff', 'student'])) {
    $base_path = "../";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Admission Management System - College Admission Portal">
    <title><?php echo isset($page_title) ? $page_title . " - College Portal" : "Student Admission Management System"; ?></title>
    
    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for professional dashboard icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">


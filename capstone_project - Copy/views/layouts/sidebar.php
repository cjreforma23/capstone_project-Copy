<?php

require_once __DIR__ . '/../../app/Helpers/layout_helper.php';

// Ensure user is logged in
if (!isset($_SESSION['role'])) {
    echo "<p>Access Denied</p>";
    exit();
}

$role = $_SESSION['role'];
?>

<!-- Sidebar Container -->
<div class="sidebar" id="sidebar">
   
    
    <div class="list-group list-group-flush">
        <?php 
        if (function_exists('generateSidebar')) {
            generateSidebar($role);
        } else {
            echo "<div class='list-group-item'>Error: Sidebar function not found.</div>";
        }
        ?>
    </div>
</div>

<!-- Load Sidebar Styles -->
<link rel="stylesheet" href="/public/assets/css/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

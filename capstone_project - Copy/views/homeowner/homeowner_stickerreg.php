<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Homeowner') {
    header("Location: ../../public/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../public/assets/css/header.css">
    <link rel="stylesheet" href="../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include '../../public/assets/cdn.php'; ?><!-- CDN -->
<?php include '../layouts/header.php'; ?>   <!-- Header -->
<div id="wrapper">
    <?php include '../layouts/sidebar.php'; ?>   <!-- Sidebar -->

    <div class="content">
        <h1>Welcome, Homeowner!</h1>
        <p>This is your Sticker Registration.</p>
    </div>
</div>

<script src="../../public/assets/js/sidebar.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../public/login.php");
    exit();
}

// Redirect based on role
switch ($_SESSION['role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'staff':
        header("Location: staff/dashboard.php");
        break;
    case 'guard':
        header("Location: guard/dashboard.php");
        break;
    case 'homeowner':
        header("Location: homeowner/dashboard.php");
        break;
    case 'guest':
        header("Location: guest/home.php");
        break;
    default:
        header("Location: ../public/login.php");
}
exit();
?>

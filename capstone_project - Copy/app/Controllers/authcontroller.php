<?php
session_start();
require_once('../Helpers/database.php');

// Check if request is valid
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error_msg'] = "Invalid request!";
    header("Location: ../public/login.php");
    exit();
}

// Sanitize input
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Prepare SQL query
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['error_msg'] = "Database error: Unable to prepare statement.";
    header("Location: ../public/login.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    // Verify password
    if (password_verify($password, $user['password'])) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];

        // Redirect user based on role
        $redirect_paths = [
            'Admin' => '../../views/admin/admin_dashboard.php',
            'Staff' => '../../views/staff/staff_dashboard.php',
            'Guard' => '../../views/guard/guard_dashboard.php',
            'Homeowner' =>'../../views/homeowner/homeowner_dashboard.php',
            'Guest' => '../../views/guest/guest_dashboard.php'
        ];

        if (array_key_exists($user['role'], $redirect_paths)) {
            header("Location: " . $redirect_paths[$user['role']]);
        } else {
            $_SESSION['error_msg'] = "Invalid user role!";
            header("Location: ../../public/login.php");
        }
        exit();
    } else {
        $_SESSION['error_msg'] = "Incorrect password!";
        header("Location: ../../public/login.php");
        exit();
    }
} else {
    $_SESSION['error_msg'] = "Email not found!";
    header("Location:../../public/login.php");
    exit();
}

// Close statements and database connection

?>

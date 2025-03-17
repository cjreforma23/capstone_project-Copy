<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../public/login.php");
    exit();
}

require_once '../Helpers/database.php';
require_once '../Controllers/AdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createUser'])) {
    $adminController = new AdminController($conn);
    
    // Get form data
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $status = $_POST['status'] ?? '';
    $created_at = $_POST['created_at'] ?? '';
    $updated_at = $_POST['updated_at'] ?? '';
    $profile_picture = $_POST['profile_picture'] ?? '';



    // Handle profile picture upload
    $profilePicture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/profile_pictures/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        // Check if it's a valid image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
            $profilePicture = $fileName;
        } else {
            $_SESSION['error'] = "Invalid file type or upload failed";
            header("Location: ../../views/admin/admin_manageuser.php");
            exit();
        }
    }

    // Attempt to create user
    $result = $adminController->createUser(
        $firstName,
        $lastName,
        $email,
        $password,
        $role,
        $address,
        $phone,
        $gender,
        $profilePicture
    );

    if ($result) {
        $_SESSION['success'] = "User created successfully!";
    } else {
        $_SESSION['error'] = "Failed to create user. Email might already exist.";
    }
}

// Redirect back to manage users page
header("Location: ../../views/admin/admin_manageuser.php");
exit(); 
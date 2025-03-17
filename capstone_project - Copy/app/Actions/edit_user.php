<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../public/login.php");
    exit();
}

require_once '../Helpers/database.php';
require_once '../Controllers/AdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editUser'])) {
    $adminController = new AdminController($conn);
    
    $userId = $_POST['user_id'] ?? null;
    if (!$userId) {
        $_SESSION['error'] = "User ID is required";
        header("Location: ../../views/admin/admin_manageuser.php");
        exit();
    }

    // Get form data
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
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
        $uploadDir = '../../public/uploads/profile_picture/';
        
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
            
            // Delete old profile picture if it exists
            $oldUser = $adminController->getUserById($userId);
            if ($oldUser && !empty($oldUser['profile_picture'])) {
                $oldPicturePath = $uploadDir . $oldUser['profile_picture'];
                if (file_exists($oldPicturePath)) {
                    unlink($oldPicturePath);
                }
            }
        } else {
            $_SESSION['error'] = "Invalid file type or upload failed";
            header("Location: ../../views/admin/edit_user.php?id=" . $userId);
            exit();
        }
    }

    // Update user
    $adminController->updateUser(
        $userId,
        $firstName,
        $lastName,
        $email,
        $role,
        $address,
        $phone,
        $gender,
        $_FILES['profile_picture'] ?? null
    );
    

    if ($result) {
        $_SESSION['success'] = "User updated successfully!";
        header("Location: ../../views/admin/view_user.php?id=" . $userId);
    } else {
        $_SESSION['error'] = "Failed to update user";
        header("Location: ../../views/admin/edit_user.php?id=" . $userId);
    }
    exit();
}

header("Location: ../../views/admin/admin_manageuser.php");
exit();
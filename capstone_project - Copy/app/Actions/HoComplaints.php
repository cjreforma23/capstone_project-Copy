<?php
session_start();
require_once '../../app/Helpers/database.php'; // Ensure this file contains $conn (database connection)

if (!isset($_SESSION['id'])) {
    die("Error: User ID is missing. Please log in again.");
}

$user_id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['HoComplaints'])) {
    // Get form data
    $complaint_type = $_POST['complaint_type'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $status = "Pending"; // Default status

    // Validate required fields
    if (empty($complaint_type) || empty($subject) || empty($description) || empty($location)) {
        die("Error: All fields are required.");
    }

    // Ensure user_id exists in the database
    $checkUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $checkUser->bind_param("i", $user_id);
    $checkUser->execute();
    $userResult = $checkUser->get_result();

    if ($userResult->num_rows === 0) {
        die("Error: The user ID does not exist in the database.");
    }

    // Handle file upload (if any)
    $attachment = NULL;
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = realpath(__DIR__ . '/../../public/uploads/complaints/'); // Get absolute path

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }

        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['attachment']['name']); // Unique filename
        $destination = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($fileTmpPath, $destination)) {
            $attachment = $fileName; // Save filename in DB
        } else {
            die("Error: Failed to upload file.");
        }
    }

    // Insert complaint into the database
    $sql = "INSERT INTO complaints (user_id, complaint_type, subject, description, location, status, attachment, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $user_id, $complaint_type, $subject, $description, $location, $status, $attachment);
    
    if ($stmt->execute()) {
        header("Location: ../../views/homeowner/homeowner_complaints.php?success=Complaint submitted successfully");
        exit();
    } else {
        die("Error: Could not submit complaint. " . $conn->error);
    }
}

?>

<?php
require_once '../../app/Helpers/database.php';
session_start();



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $type = $_POST['complaint_type'];
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $attachment = $_FILES['attachment']['name'];

    if ($attachment) {
        move_uploaded_file($_FILES['attachment']['tmp_name'], "../uploads/$attachment");
    }

    $sql = "INSERT INTO complaints (user_id, complaint_type, subject, description, location, attachment) 
            VALUES (?, ?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$user_id, $type, $subject, $description, $location, $attachment])) {
        header("Location: ../../views/admin/admin_complaints.php?success=Complaint filed successfully.");
    } else {
        header("Location: ../../views/admin/admin_complaints.php?error=Failed to file complaint.");
    }
    exit;
}
?>

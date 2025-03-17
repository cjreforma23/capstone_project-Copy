<?php
require_once '../../app/Helpers/database.php';
session_start();



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complaint_id = $_POST['complaint_id'];
    $user_id = $_POST['user_id'];
    $comment = $_POST['comment'];
    $attachment = $_FILES['attachment']['name'];

    if ($attachment) {
        move_uploaded_file($_FILES['attachment']['tmp_name'], "../uploads/$attachment");
    }

    $sql = "INSERT INTO complaint_updates (complaint_id, user_id, comment, attachment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$complaint_id, $user_id, $comment, $attachment])) {
        header("Location: ../views/complaint_details.php?id=$complaint_id&success=Comment added.");
    } else {
        header("Location: ../views/complaint_details.php?id=$complaint_id&error=Failed to add comment.");
    }
    exit;
}
?>

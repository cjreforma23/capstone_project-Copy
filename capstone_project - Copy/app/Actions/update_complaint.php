<?php
require_once '../../app/Helpers/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $complaint_id = $_POST['complaint_id'];
    $complaint_type = $_POST['complaint_type'];
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $status = $_POST['status'];

    $sql = "UPDATE complaints SET complaint_type=?, subject=?, description=?, location=?, status=? WHERE complaint_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $complaint_type, $subject, $description, $location, $status, $complaint_id);

    if ($stmt->execute()) {
        header("Location: ../../views/admin/admin_complaints.php?success=Complaint updated");
    } else {
        header("Location: ../../views/admin/edit_complaints.php?id=$complaint_id&error=Error updating complaint");
    }

    $stmt->close();
}
?>

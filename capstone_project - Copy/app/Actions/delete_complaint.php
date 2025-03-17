<?php
require_once '../../app/Helpers/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["complaint_id"])) {
    $complaint_id = intval($_POST["complaint_id"]);

    $sql = "DELETE FROM complaints WHERE complaint_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $complaint_id);

    if ($stmt->execute()) {
        header("Location: ../../views/admin/admin_complaints.php?success=Complaint deleted successfully");
        exit();
    } else {
        header("Location: ../../views/admin/admin_complaints.php?error=Failed to delete complaint");
        exit();
    }
} else {
    header("Location: ../../views/admin/admin_complaints.php");
    exit();
}
?>

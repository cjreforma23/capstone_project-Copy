<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../../public/assets/cdn.php'; ?>
<?php include '../layouts/header.php'; ?>
<div id="wrapper">
    <?php include '../layouts/sidebar.php'; ?>
<?php
require_once '../../app/Helpers/database.php';


if (!isset($_GET['id'])) {
    die("Invalid request");
}

$complaint_id = intval($_GET['id']);

// Fetch complaint details
$sql = "SELECT * FROM complaints WHERE complaint_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();

if (!$complaint) {
    die("Complaint not found");
}
?>

<div class="container mt-4">
    <h2>Edit Complaint</h2>
    <form action="../../app/Actions/update_complaint.php" method="POST">
        <input type="hidden" name="complaint_id" value="<?= $complaint['complaint_id'] ?>">

        <label>Complaint Type:</label>
        <select name="complaint_type" class="form-control">
            <option value="Maintenance" <?= $complaint['complaint_type'] == 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
            <option value="Security" <?= $complaint['complaint_type'] == 'Security' ? 'selected' : '' ?>>Security</option>
            <option value="Noise" <?= $complaint['complaint_type'] == 'Noise' ? 'selected' : '' ?>>Noise</option>
            <option value="Violation" <?= $complaint['complaint_type'] == 'Violation' ? 'selected' : '' ?>>Violation</option>
            <option value="Others" <?= $complaint['complaint_type'] == 'Others' ? 'selected' : '' ?>>Others</option>
        </select>

        <label>Subject:</label>
        <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($complaint['subject']) ?>">

        <label>Description:</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($complaint['description']) ?></textarea>

        <label>Location:</label>
        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($complaint['location']) ?>">

        <label>Status:</label>
        <select name="status" class="form-control">
            <option value="pending" <?= $complaint['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="in-progress" <?= $complaint['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
            <option value="resolved" <?= $complaint['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
        </select>

        <button type="submit" name="update_complaint" class="btn btn-success mt-2">Update</button>
        <a href="admin_complaints.php" class="btn btn-secondary mt-2">Cancel</a>
    </form>
</div>

</body>
</html>
</body>
</html>

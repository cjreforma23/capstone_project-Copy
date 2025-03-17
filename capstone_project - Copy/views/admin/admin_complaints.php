<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../public/login.php");
    exit();
}

require_once '../../app/Helpers/database.php'; // Ensure this file properly sets up `$conn`

// Fetch complaints
$sql = "SELECT c.*, u.first_name, u.last_name FROM complaints c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);

$complaints = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Complaints</title>
    <link rel="stylesheet" href="../../public/assets/css/header.css">
    <link rel="stylesheet" href="../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<?php include '../layouts/header.php'; ?>
<div id="wrapper">
    <?php include '../layouts/sidebar.php'; ?>

    <div class="container mt-4">
        <h1>Complaints</h1>

        <!-- Complaint Form -->
        <form action="../../app/Actions/create_complaints.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="1">
            <label>Complaint Type:</label>
            <select name="complaint_type" class="form-control">
                <option value="Maintenance">Maintenance</option>
                <option value="Security">Security</option>
                <option value="Noise">Noise</option>
                <option value="Violation">Violation</option>
                <option value="Others">Others</option>
            </select>
            <label>Subject:</label>
            <input type="text" name="subject" class="form-control">
            <label>Description:</label>
            <textarea name="description" class="form-control"></textarea>
            <label>Location:</label>
            <input type="text" name="location" class="form-control">
            <label>Attachment:</label>
            <input type="file" name="attachment" class="form-control">
            <button type="submit" name="create_complaint" class="btn btn-primary mt-2">Submit</button>
        </form>

        <!-- Complaints Table -->
            <?php
                    $sql = "SELECT c.*, u.first_name, u.last_name 
                    FROM complaints c
                    JOIN users u ON c.user_id = u.id
                    ORDER BY c.created_at DESC";

            $result = $conn->query($sql);

            $complaints = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $complaints[] = $row;
                }
            }
            ?>

            <h3>Complaints Submitted</h3>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Attachment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($complaints)): ?>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?= htmlspecialchars($complaint['complaint_id']) ?></td>
                            <td><?= htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']) ?></td>
                            <td><?= htmlspecialchars($complaint['complaint_type']) ?></td>
                            <td><?= htmlspecialchars($complaint['subject']) ?></td>
                            <td><?= htmlspecialchars($complaint['description']) ?></td>
                            <td><?= htmlspecialchars($complaint['location']) ?></td>
                            <td>
                                <?php if (!empty($complaint['attachment'])): ?>
                                    <a href="../../public/uploads/complaints/ $complaint['attachment'] ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                                    <td><?= htmlspecialchars($complaint['status']) ?></td>
                            <td>
                                <!-- Edit Button -->
                                <a href="edit_complaints.php?id=<?= $complaint['complaint_id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>

                                <!-- Delete Button -->
                                <form action="../../backend/complaints/delete_complaint.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="complaint_id" value="<?= $complaint['complaint_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">🗑 Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No complaints found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>

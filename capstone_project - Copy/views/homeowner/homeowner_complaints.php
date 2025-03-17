<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Homeowner') {
    header("Location: ../../public/login.php");
    exit();
    
}

require_once '../../app/Helpers/database.php';

// Get the logged-in user's ID
$user_id = $_SESSION['id'];

// Fetch complaints only for the logged-in user
$sql = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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
    <title>My Complaints</title>
    <link rel="stylesheet" href="../../public/assets/css/header.css">
    <link rel="stylesheet" href="../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include '../layouts/header.php'; ?>
<div id="wrapper">
    <?php include '../layouts/sidebar.php'; ?>

    <div class="container mt-4">
        <h1>My Complaints</h1>

        <!-- Complaint Form -->
        <form action="../../app/Actions/HoComplaints.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
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
            <button type="submit" name="HoComplaints" class="btn btn-primary mt-2">Submit</button>
        </form>

        

        <!-- Complaints Table -->
        <h2 class="mt-4">My Complaints History</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Attachment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($complaints)): ?>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?= htmlspecialchars($complaint['user_id']) ?></td>
                            <td><?= htmlspecialchars($complaint['complaint_type']) ?></td>
                            <td><?= htmlspecialchars($complaint['subject']) ?></td>
                            <td><?= htmlspecialchars($complaint['description']) ?></td>
                            <td><?= htmlspecialchars($complaint['location']) ?></td>
                            <td><?= htmlspecialchars($complaint['status']) ?></td>
                            <td>
                                <?php if (!empty($complaint['attachment'])): ?>
                                    <a href="../../public/uploads/complaints/<?= $complaint['attachment'] ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="../../views/homeowner/edit_complaint.php?id=<?= $complaint['complaint_id'] ?>" class="btn btn-warning btn-sm">
                                    ✏️ Edit
                                </a>
                                <form action="../../app/Actions/delete_complaint.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="complaint_id" value="<?= $complaint['complaint_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this complaint?')">
                                        🗑 Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No complaints found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>
</body>
</html>

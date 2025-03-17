<?php
session_start();
require_once '../../app/Helpers/database.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../public/login.php");
    exit();
}

// Fetch reservations
$sql = "SELECT r.*, u.first_name, u.last_name 
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
$reservations = $result->fetch_all(MYSQLI_ASSOC);

// Fetch reserved amenities for each reservation
$amenities = [];
$sql = "SELECT ra.reservation_id, a.amenities, ra.price, ra.reservation_date, ra.time_slot
        FROM reserved_amenities ra
        JOIN amenities a ON ra.amenity_id = a.id";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $amenities[$row['reservation_id']][] = $row;
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $reservation_id = $_POST['reservation_id'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
    $stmt->bind_param("si", $new_status, $reservation_id);
    $stmt->execute();

    header("Location: admin_reservations.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>All Reservations</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Reservation ID</th>
                <th>User</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Address</th>
                <th>Amenities</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= $reservation['reservation_id'] ?></td>
                    <td><?= htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']) ?></td>
                    <td><?= htmlspecialchars($reservation['contact']) ?></td>
                    <td><?= htmlspecialchars($reservation['email']) ?></td>
                    <td><?= htmlspecialchars($reservation['address']) ?></td>
                    <td>
                        <ul>
                            <?php foreach ($amenities[$reservation['reservation_id']] as $amenity): ?>
                                <li><?= htmlspecialchars($amenity['amenities']) ?> - ₱<?= number_format($amenity['price'], 2) ?> (<?= htmlspecialchars($amenity['reservation_date']) ?>, <?= htmlspecialchars($amenity['time_slot']) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>₱<?= number_format($reservation['total_amount'], 2) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="reservation_id" value="<?= $reservation['reservation_id'] ?>">
                            <select name="status" class="form-control">
                                <option value="Pending" <?= $reservation['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved" <?= $reservation['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $reservation['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="Completed" <?= $reservation['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary mt-2">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

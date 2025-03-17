<?php
session_start();
require_once '../../app/Helpers/database.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../public/login.php");
    exit();
}

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: admin_reservation.php");
    exit();
}

$total_price = 0;

// Calculate total price
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'];
}

// Handle reservation submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_reservation'])) {
    $full_name = $_POST['full_name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    if (empty($full_name) || empty($contact) || empty($email) || empty($address)) {
        $error_message = "All fields are required!";
    } else {
        // Insert reservation into database
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, total_amount, status, created_at) VALUES (?, ?, 'Pending', NOW())");
        $stmt->bind_param("id", $user_id, $total_price);
        $stmt->execute();
        $reservation_id = $stmt->insert_id;

        // Insert each reserved amenity
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $conn->prepare("INSERT INTO reserved_amenities (reservation_id, amenity_id, price, reservation_date, time_slot) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iidss", $reservation_id, $item['id'], $item['price'], $item['date'], $item['time']);
            $stmt->execute();
        }

        // Clear cart
        unset($_SESSION['cart']);

        // Redirect to success page
        header("Location: reservation_success.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Checkout</h2>

    <!-- Display Error Message -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <h3>Your Reservation</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Amenity</th>
                <th>Type</th>
                <th>Price</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['type']) ?></td>
                    <td>₱<?= number_format($item['price'], 2) ?></td>
                    <td><?= htmlspecialchars($item['date']) ?></td>
                    <td><?= htmlspecialchars($item['time']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Total Price: <strong>₱<?= number_format($total_price, 2) ?></strong></h3>

    <h3>Enter Homeowner or Guest Details</h3>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" required>
        </div>

        <button type="submit" name="confirm_reservation" class="btn btn-success">Confirm Reservation</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

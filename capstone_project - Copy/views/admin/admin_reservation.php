<?php
session_start();
require_once '../../app/Helpers/database.php';
require_once '../../app/Helpers/discount_res.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../public/login.php");
    exit();
}

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch amenities
$sql = "SELECT * FROM amenities";
$result = $conn->query($sql);
$amenities = $result->fetch_all(MYSQLI_ASSOC);

// Handle adding to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $amenity_id = $_POST['amenity_id'];
    $amenity_name = $_POST['amenity_name'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $reservation_date = $_POST['reservation_date'];
    $start_time = $_POST['start_time'];

    // Convert to end time (1-hour slots)
    $end_time = date("H:i", strtotime($start_time) + 3600);

    // Apply discount for homeowners
    $discounted_price = applyDiscount($price, $user_role);

    $_SESSION['cart'][] = [
        'id' => $amenity_id,
        'name' => $amenity_name,
        'price' => $discounted_price,
        'type' => $type,
        'date' => $reservation_date,
        'time' => $start_time . ' - ' . $end_time
    ];

    header("Location: admin_reservation.php");
    exit();
}

// Handle removing from cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_from_cart'])) {
    $index = $_POST['remove_index'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: admin_reservation.php");
    exit();
}

// Calculate total price
$total_price = array_sum(array_column($_SESSION['cart'], 'price'));
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
<div class="container mt-4">
    <h1>Reserve Amenities</h1>

    <!-- Amenities Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Amenity</th>
                <th>Type</th>
                <th>Price</th>
                <th>Reserve</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($amenities as $amenity): ?>
                <tr>
                    <td><?= htmlspecialchars($amenity['amenities']) ?></td>
                    <td><?= htmlspecialchars($amenity['type']) ?></td>
                    <td>₱<?= number_format(applyDiscount($amenity['price'], $user_role), 2) ?></td>
                    <td>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addToCartModal<?= $amenity['id'] ?>">Add to Cart</button>

                        <!-- Modal -->
                        <div class="modal fade" id="addToCartModal<?= $amenity['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reserve <?= htmlspecialchars($amenity['amenities']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="amenity_id" value="<?= $amenity['id'] ?>">
                                            <input type="hidden" name="amenity_name" value="<?= $amenity['amenities'] ?>">
                                            <input type="hidden" name="price" value="<?= $amenity['price'] ?>">
                                            <input type="hidden" name="type" value="<?= $amenity['type'] ?>">

                                            <label>Reservation Date:</label>
                                            <input type="date" name="reservation_date" required class="form-control">

                                            <label>Start Time:</label>
                                            <select name="start_time" class="form-control" required>
                                                <?php for ($hour = 7; $hour <= 21; $hour++): ?>
                                                    <option value="<?= sprintf("%02d:00", $hour) ?>"><?= date("h:i A", strtotime("$hour:00")) ?> - <?= date("h:i A", strtotime("$hour:00 +1 hour")) ?></option>
                                                <?php endfor; ?>
                                            </select>

                                            <button type="submit" name="add_to_cart" class="btn btn-primary mt-3">Confirm</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End of Modal -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Cart Table -->
    <h2>Your Cart</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Amenity</th>
                <th>Type</th>
                <th>Price</th>
                <th>Date</th>
                <th>Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['type']) ?></td>
                    <td>₱<?= number_format($item['price'], 2) ?></td>
                    <td><?= htmlspecialchars($item['date']) ?></td>
                    <td><?= htmlspecialchars($item['time']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="remove_index" value="<?= $index ?>">
                            <button type="submit" name="remove_from_cart" class="btn btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Total Price: ₱<?= number_format($total_price, 2) ?></h3>

    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

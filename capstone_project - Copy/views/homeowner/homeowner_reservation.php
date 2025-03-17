<?php
session_start();
require_once '../../app/Helpers/database.php';
require_once '../../app/Helpers/discount_res.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Homeowner') {
    header("Location: ../../public/login.php");
    exit();
}

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];
$user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$user_email = $_SESSION['email'];
$user_phone = $_SESSION['phone'];

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

    header("Location: homeowner_reservation.php");
    exit();
}

// Handle checkout submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkout'])) {
    $payment_mode = $_POST['payment_mode'];
    $payment_type = $_POST['payment_type'];
    $participants = $_POST['participants'];

    $gcash_proof = "";
    $gcash_ref = "";
    if ($payment_mode === "GCash") {
        $gcash_proof = $_FILES['gcash_proof']['name'];
        $gcash_ref = $_POST['gcash_ref'];

        move_uploaded_file($_FILES['gcash_proof']['tmp_name'], "../../uploads/$gcash_proof");
    }

    // Save reservation (Insert this into the database)
    $_SESSION['cart'] = []; // Clear cart after checkout
    header("Location: homeowner_reservation.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homeowner Reservation</title>
    <link rel="stylesheet" href="../../public/assets/css/header.css">
    <link rel="stylesheet" href="../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Reserve Amenities</h1>

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
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Your Cart</h2>
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

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkoutModal">Proceed to Checkout</button>

    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <label>Name:</label>
                        <input type="text" class="form-control" value="<?= $user_name ?>" disabled>

                        <label>Payment Mode:</label>
                        <select name="payment_mode" class="form-control" id="payment_mode" required>
                            <option value="Cash">Cash</option>
                            <option value="GCash">GCash</option>
                        </select>

                        <label>Payment Type:</label>
                        <select name="payment_type" class="form-control">
                            <option value="Full">Full Payment</option>
                            <option value="Downpayment">Downpayment</option>
                        </select>

                        <div id="gcash_fields" style="display: none;">
                            <label>GCash Reference Number:</label>
                            <input type="text" name="gcash_ref" class="form-control">

                            <label>Upload Proof of Payment:</label>
                            <input type="file" name="gcash_proof" class="form-control">
                        </div>

                        <label>List of Participants:</label>
                        <textarea name="participants" class="form-control" placeholder="Enter names separated by commas" required></textarea>

                        <button type="submit" name="checkout" class="btn btn-success mt-3">Confirm Reservation</button>
                    </form>
                </div>
            </div>
        </div>
    </div> 
</div>

<script>
document.getElementById("payment_mode").addEventListener("change", function() {
    var gcashFields = document.getElementById("gcash_fields");
    if (this.value === "GCash") {
        gcashFields.style.display = "block";
    } else {
        gcashFields.style.display = "none";
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

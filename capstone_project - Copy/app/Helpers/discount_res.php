<?php
session_start();

// Apply 20% discount if user is a Homeowner
function applyDiscount($price, $role) {
    return ($role == "Homeowner") ? $price * 0.8 : $price;
}
?>

<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../public/login.php");
    exit();
}

require_once '../../app/Helpers/database.php';
require_once '../../app/Controllers/AdminController.php';

$adminController = new AdminController($conn);
$userId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$userId) {
    header("Location: admin_manageuser.php");
    exit();
}

$user = $adminController->getUserById($userId);

if (!$user) {
    header("Location: admin_manageuser.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Details</title>
    <link rel="stylesheet" href="../../public/assets/css/header.css">
    <link rel="stylesheet" href="../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include '../layouts/header.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div id="wrapper">
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>User Details</h2>
                <a href="admin_manageuser.php" class="btn btn-secondary">Back to Users</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="../../public/uploads/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>" 
                                 class="rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '-') ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['status']) ?>" readonly>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '-') ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['gender'] ?? '-') ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Created</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['created_at'] ?? '-') ?>" readonly>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-warning" onclick="window.location.href='edit_user.php?id=<?= $user['id'] ?>'">
                            <i class="fas fa-edit"></i> Edit User
                        </button>
                        <button class="btn btn-danger" onclick="confirmDelete(<?= $user['id'] ?>)">
                            <i class="fas fa-trash"></i> Delete User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 
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
    <title>Edit User</title>
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
                <h2>Edit User</h2>
                <a href="view_user.php?id=<?= $userId ?>" class="btn btn-secondary">Back to User Details</a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="../../app/Actions/edit_user.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?= $userId ?>">
                        
                        <div class="text-center mb-4">
                            <div class="profile-pic-wrapper">
                                <div class="profile-pic">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="../../public/uploads/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>" 
                                             id="preview" class="rounded-circle" 
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="../../public/assets/images/default-avatar.png" 
                                             id="preview" class="rounded-circle" 
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2">
                                    <label class="btn btn-outline-primary btn-sm">
                                        Change Photo
                                        <input type="file" name="profile_picture" accept="image/*" 
                                               onchange="previewImage(this)" style="display: none;">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role" required>
                                    <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="Staff" <?= $user['role'] === 'Staff' ? 'selected' : '' ?>>Staff</option>
                                    <option value="Homeowner" <?= $user['role'] === 'Homeowner' ? 'selected' : '' ?>>Homeowner</option>
                                    <option value="Guard" <?= $user['role'] === 'Guard' ? 'selected' : '' ?>>Guard</option>
                                    <option value="Guest" <?= $user['role'] === 'Guest' ? 'selected' : '' ?>>Guest</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender" required>
                                    <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" 
                                       value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="editUser" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="view_user.php?id=<?= $userId ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>
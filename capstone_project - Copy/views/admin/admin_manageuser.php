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
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include '../layouts/header.php'; ?>   <!-- Header -->
<?php include '../layouts/sidebar.php'; ?>   <!-- Sidebar -->

<div id="wrapper">
    <div id="content">
        <div class="container-fluid">
            <h2 class="mb-4">Manage Users</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php
require_once '../../app/Helpers/database.php';
require_once '../../app/Controllers/AdminController.php';

$adminController = new AdminController($conn);

// Get search parameters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// Get filtered users
$users = $adminController->searchUsers($searchTerm, $roleFilter);
?>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
                            <select class="form-select" style="width: auto;" name="role">
                                <option value="">All Roles</option>
                                <option value="Admin" <?= $roleFilter === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="Staff" <?= $roleFilter === 'Staff' ? 'selected' : '' ?>>Staff</option>
                                <option value="Homeowner" <?= $roleFilter === 'Homeowner' ? 'selected' : '' ?>>Homeowner</option>
                                <option value="Guard" <?= $roleFilter === 'Guard' ? 'selected' : '' ?>>Guard</option>
                                <option value="Guest" <?= $roleFilter === 'Guest' ? 'selected' : '' ?>>Guest</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                        <div>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">+ Add</button>
                            <button class="btn btn-danger" onclick="window.location.href='archive_user.php'">Archives</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Email</th>
                                    <th>Contact Number</th>
                                    <th>Role</th>
                                    <th>Status</th>                                   
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users->fetch_assoc()) : ?>
                                    <tr style="cursor: pointer;" onclick="window.location.href='view_user.php?id=<?= $user['id'] ?>'">
                                        <td><?= $user['id'] ?></td>
                                        <td><?= $user['last_name'] ?></td>
                                        <td><?= $user['first_name'] ?></td>
                                        <td><?= $user['email'] ?></td>
                                        <td><?= $user['phone'] ?? '-' ?></td>
                                        <td><?= $user['role'] ?></td>
                                        <td>
                                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $user['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../app/Actions/add_user.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="profile-pic-wrapper">
                            <div class="profile-pic">
                                <img src="../../public/assets/images/default-avatar.png" id="preview" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="mt-2">
                                <label class="btn btn-outline-primary btn-sm">
                                    Choose Photo
                                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(this)" style="display: none;">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="firstName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="lastName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                            <option value="Homeowner">Homeowner</option>
                            <option value="Guard">Guard</option>
                            <option value="Guest">Guest</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="createUser" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-3x text-secondary"></i>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" id="view-first-name" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="view-last-name" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="view-email" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" class="form-control" id="view-phone" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" id="view-role" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" id="view-address" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <input type="text" class="form-control" id="view-gender" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Date Created</label>
                    <input type="text" class="form-control" id="view-date-created" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewUser(user) {
    // Update the modal fields with user data
    document.getElementById('view-first-name').value = user.first_name;
    document.getElementById('view-last-name').value = user.last_name;
    document.getElementById('view-email').value = user.email;
    document.getElementById('view-phone').value = user.phone || '-';
    document.getElementById('view-role').value = user.role;
    document.getElementById('view-address').value = user.address || '-';
    document.getElementById('view-gender').value = user.gender || '-';
    document.getElementById('view-date-created').value = user.created_at || '-';
}

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

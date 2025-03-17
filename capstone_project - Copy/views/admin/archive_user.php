<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../public/login.php");
    exit();
}
?>

<?php include '../layouts/header.php'; ?>   <!-- Header -->
<?php include '../layouts/sidebar.php'; ?>   <!-- Sidebar -->
<?php include '../../app/Helpers/database.php'; ?>   <!-- Sidebar -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="../../public/assets/css/header.css">
    <link rel="stylesheet" href="../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head> 
<body>
<div id="wrapper">
    <div id="content">
        <div class="container-fluid py-4" style="margin-left: 250px; width: calc(100% - 250px);">
            <!-- Header Section -->
            <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-danger">Archived Users</h1>
        </div>
        <div class="col-auto">
            <a href="admin_manageuser.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>
    </div>

    <!-- Search Box -->
    <?php
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
                    </div>
                </div>
            </div>


    <!-- Archives Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Role</th>
                            <th>Archived Date</th>
                            <th>Reason</th>
                            <th>Archived By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $archive_sql = "SELECT a.*, u.first_name as archived_by_name, u.last_name as archived_by_lastname 
                                      FROM archived_users a 
                                      LEFT JOIN users u ON a.archived_by = u.id 
                                      ORDER BY a.archived_at DESC";
                        $archive_result = mysqli_query($conn, $archive_sql);

                        if ($archive_result && mysqli_num_rows($archive_result) > 0) {
                            while ($archive = mysqli_fetch_assoc($archive_result)) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($archive['first_name'] . ' ' . $archive['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($archive['email']); ?></td>
                                    <td><?php echo htmlspecialchars($archive['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($archive['role']); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($archive['archived_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($archive['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($archive['archived_by_name'] . ' ' . $archive['archived_by_lastname']); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-4'>No archived users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleFilter = document.getElementById('roleFilter');
    const searchInput = document.getElementById('searchArchive');
    const tableRows = document.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value;

        tableRows.forEach(row => {
            const roleCell = row.querySelector('td:nth-child(4)').textContent;
            const rowText = row.textContent.toLowerCase();
            const matchesSearch = rowText.includes(searchTerm);
            const matchesRole = !selectedRole || roleCell === selectedRole;

            row.style.display = matchesSearch && matchesRole ? '' : 'none';
        });
    }

    roleFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);
});
</script>

<style>
body {
    overflow-x: hidden;
}

@media (max-width: 768px) {
    .container-fluid {
        margin-left: 0;
        width: 100%;
    }
}

.table th {
    white-space: nowrap;
}

.form-select:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

.text-danger {
    color: #dc3545 !important;
}
</style> 
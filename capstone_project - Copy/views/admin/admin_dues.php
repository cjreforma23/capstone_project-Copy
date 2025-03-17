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
<div id="wrapper">
    <?php include '../layouts/sidebar.php'; ?>   <!-- Sidebar -->

<?php
require_once '../../app/Helpers/database.php';
require_once '../../app/Controllers/AdminController.php';

// Initialize variables
$error_msg = '';
$success_msg = '';
$month = isset($_GET['month']) ? $_GET['month'] : date('n');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Process Generate Monthly Dues
if(isset($_POST['generate_dues'])) {
    try {
        $month = $_POST['generate_month'];
        $year = $_POST['generate_year'];

        // Check if dues already exist for this month/year
        $check_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM monthly_dues WHERE month = ? AND year = ?");
        mysqli_stmt_bind_param($check_stmt, "ii", $month, $year);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $count = mysqli_fetch_assoc($result)['count'];

        if($count > 0) {
            throw new Exception("Dues already exist for selected month and year");
        }

        // Get all active homeowners
        $result = mysqli_query($conn, "SELECT id FROM users WHERE role = 'Homeowner'");
        $homeowners = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Calculate total dues amount from dues_inclusions
        $dues_query = "SELECT SUM(amount) as total FROM dues_inclusions";
        $dues_result = mysqli_query($conn, $dues_query);
        $dues_row = mysqli_fetch_assoc($dues_result);
        $totalDues = $dues_row['total'];

        // Generate dues for each homeowner
        mysqli_begin_transaction($conn);
        
        foreach ($homeowners as $homeowner) {
            $stmt = mysqli_prepare($conn, "INSERT INTO monthly_dues 
                (homeowner_id, month, year, total_amount, status, created_at) 
                VALUES (?, ?, ?, ?, 'unpaid', NOW())");
            
            mysqli_stmt_bind_param($stmt, "iiid", 
                $homeowner['id'],
                $month,
                $year,
                $totalDues
            );
            mysqli_stmt_execute($stmt);
        }

        mysqli_commit($conn);
        $success_msg = "Monthly dues generated successfully!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = "Failed to generate dues: " . $e->getMessage();
    }
}

// Get existing dues data
try {
    $query = "SELECT 
              u.id as homeowner_id,
              CONCAT(u.first_name, ' ', u.last_name) as homeowner_name,
              u.status as user_status,
              GROUP_CONCAT(CONCAT(md.month, '/', md.year) ORDER BY md.year, md.month) as payment_periods,
              SUM(md.total_amount) as total_amount,
              SUM(COALESCE(md.paid_amount, 0)) as total_paid_amount,
              SUM(md.total_amount - COALESCE(md.paid_amount, 0)) as total_balance,
              GROUP_CONCAT(md.status) as payment_statuses,
              (SELECT SUM(amount) FROM dues_inclusions) as monthly_dues_amount
              FROM users u
              LEFT JOIN monthly_dues md ON u.id = md.homeowner_id
              WHERE u.role = 'Homeowner' AND u.status = 'active'
              GROUP BY u.id, u.first_name, u.last_name, u.status";
    
    if (!empty($status)) {
        // Add HAVING clause for status filtering
        $query .= " HAVING MIN(md.status) = ?";
    }
    
    $stmt = mysqli_prepare($conn, $query);
    
    if (!empty($status)) {
        mysqli_stmt_bind_param($stmt, "s", $status);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dues = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // Calculate summary
    $summary = [
        'total_due' => 0,
        'total_paid' => 0,
        'total_unpaid' => 0
    ];

    foreach ($dues as $due) {
        $summary['total_due'] += (float)$due['total_amount'];
        $summary['total_paid'] += (float)$due['total_paid_amount'];
        $summary['total_unpaid'] += (float)$due['total_balance'];
    }

} catch (Exception $e) {
    $error_msg = "Failed to load dues: " . $e->getMessage();
    $dues = [];
    $summary = [
        'total_due' => 0,
        'total_paid' => 0,
        'total_unpaid' => 0
    ];
}
?>

<style>
.alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.5s ease-in-out;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Auto-hide the alert after 5 seconds */
.alert.fade-out {
    animation: fadeOut 0.5s ease-in-out forwards;
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
</style>

<!-- Now start HTML output -->


<!-- HTML Section -->
<div class="container-fluid px-4">
    <?php if ($success_msg): ?>
        <div class="alert alert-success" id="successAlert" role="alert">
            <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger" id="errorAlert" role="alert">
            <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-2">
            <!-- Sidebar space -->
        </div>

        <div class="col-md-10 p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Monthly Dues Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateDuesModal">
                    <i class="fas fa-plus"></i> Generate Monthly Dues
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6>Total Expected Collection</h6>
                            <h3>₱<?php echo number_format($summary['total_due'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Total Collected</h6>
                            <h3>₱<?php echo number_format($summary['total_paid'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6>Outstanding Balance</h6>
                            <h3>₱<?php echo number_format($summary['total_unpaid'], 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m == $month) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-select">
                                <?php 
                                $currentYear = date('Y');
                                for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++): 
                                ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="paid" <?php echo ($status == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="unpaid" <?php echo ($status == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="partial" <?php echo ($status == 'partial') ? 'selected' : ''; ?>>Partial</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dues Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Homeowner</th>
                                    <th>Payment Periods</th>
                                    <th class="text-end">Total Due</th>
                                    <th class="text-end">Total Paid</th>
                                    <th class="text-end">Total Balance</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($dues)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No records found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($dues as $due): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($due['homeowner_name']); ?></td>
                                            <td>
                                                <?php 
                                                $periods = explode(',', $due['payment_periods']);
                                                echo $periods ? implode(', ', $periods) : 'No payments recorded';
                                                ?>
                                            </td>
                                            <td class="text-end">₱<?php echo number_format($due['total_amount'], 2); ?></td>
                                            <td class="text-end">₱<?php echo number_format($due['total_paid_amount'], 2); ?></td>
                                            <td class="text-end">₱<?php echo number_format($due['total_balance'], 2); ?></td>
                                            <td class="text-center">
                                                <?php
                                                $statuses = explode(',', $due['payment_statuses']);
                                                $status = 'unpaid';
                                                if (!empty($statuses)) {
                                                    if (in_array('pending', $statuses)) {
                                                        $status = 'pending';
                                                    } elseif ($due['total_balance'] == 0) {
                                                        $status = 'paid';
                                                    } elseif ($due['total_paid_amount'] > 0) {
                                                        $status = 'partial';
                                                    }
                                                }
                                                ?>
                                                <span class="badge rounded-pill bg-<?php 
                                                    echo $status == 'paid' ? 'success' : 
                                                        ($status == 'pending' ? 'warning' : 
                                                        ($status == 'partial' ? 'info' : 'danger')); 
                                                ?>">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="get_dues_details.php?homeowner_id=<?php echo $due['homeowner_id']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Dues Modal -->
<div class="modal fade" id="generateDuesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Monthly Dues</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <select name="generate_month" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m == date('n')) ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select name="generate_year" class="form-select" required>
                            <?php 
                            $currentYear = date('Y');
                            for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++): 
                            ?>
                                <option value="<?php echo $y; ?>" <?php echo ($y == $currentYear) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="generate_dues" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update the View Dues Modal -->
<div class="modal fade" id="viewDuesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Monthly Dues Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6>Homeowner: <span id="homeownerName"></span></h6>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Amount Due</h6>
                                <h4 id="totalAmount">₱0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Total Paid</h6>
                                <h4 id="paidAmount">₱0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6>Total Balance</h6>
                                <h4 id="balanceAmount">₱0.00</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="monthly-dues-details">
                    <h6 class="mb-3">Monthly Dues History</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th class="text-end">Amount Due</th>
                                    <th class="text-end">Amount Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                    <th>Payment Details</th>
                                </tr>
                            </thead>
                            <tbody id="monthlyDuesDetails">
                                <!-- Monthly dues details will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Upload Form -->
                <div class="mt-4">
                    <h6 class="mb-3">Upload Payment</h6>
                    <form id="paymentUploadForm" method="POST" action="process_payment.php" enctype="multipart/form-data">
                        <input type="hidden" id="duesId" name="dues_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="number" class="form-control" name="amount" placeholder="Payment Amount" required>
                            </div>
                            <div class="col-md-6">
                                <input type="file" class="form-control" name="payment_proof" accept="image/*" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Submit Payment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewDuesModal = document.getElementById('viewDuesModal');
    
    viewDuesModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const homeownerId = button.getAttribute('data-homeowner-id');
        
        fetch(`get_dues_details.php?homeowner_id=${homeownerId}`)
            .then(response => response.json())
            .then(data => {
                // Update summary information
                document.getElementById('homeownerName').textContent = data.summary.homeowner_name;
                document.getElementById('totalAmount').textContent = '₱' + parseFloat(data.summary.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('paidAmount').textContent = '₱' + parseFloat(data.summary.total_paid_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('balanceAmount').textContent = '₱' + parseFloat(data.summary.balance_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

                // Update monthly dues details
                const monthlyDuesDetails = document.getElementById('monthlyDuesDetails');
                monthlyDuesDetails.innerHTML = '';

                data.dues_details.forEach(due => {
                    const row = document.createElement('tr');
                    const balance = parseFloat(due.total_amount) - parseFloat(due.paid_amount || 0);
                    const status = balance === 0 ? 'paid' : (due.paid_amount > 0 ? 'partial' : 'unpaid');
                    
                    row.innerHTML = `
                        <td>${due.due_period}</td>
                        <td class="text-end">₱${parseFloat(due.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">₱${parseFloat(due.paid_amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">₱${balance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>
                            <span class="badge rounded-pill bg-${status === 'paid' ? 'success' : (status === 'partial' ? 'info' : 'danger')}">
                                ${status.toUpperCase()}
                            </span>
                        </td>
                        <td>
                            ${due.payment_date ? `
                                <small>
                                    Paid on: ${new Date(due.payment_date).toLocaleDateString()}<br>
                                    ${due.proof_of_payment ? `<a href="${due.proof_of_payment}" target="_blank">View Receipt</a>` : ''}
                                </small>
                            ` : '-'}
                        </td>
                    `;
                    monthlyDuesDetails.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load dues details');
            });
    });
    
    // Handle form submission
    document.getElementById('paymentUploadForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'process_payment.php', true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText === 'success') {
                    alert('Payment uploaded successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + xhr.responseText);
                }
            }
        };
        
        xhr.send(formData);
    };

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>








<script src="../../public/assets/js/script.js"></script>
</body>
</html>

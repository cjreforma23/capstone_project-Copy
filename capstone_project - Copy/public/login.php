<?php include '../app/Helpers/database.php';?>
<?php session_start();?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Village East Log in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/login.css">
    
  </head>
<body>
    <div class="overlay"></div>
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="login-card shadow">
                    <h1 class="text-center mb-4">Village East</h1>
                    <p class="text-center mb-4">Log in to your account</p>

                    <?php 
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    if (isset($_SESSION['error_msg'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                            echo htmlspecialchars($_SESSION['error_msg']); 
                            unset($_SESSION['error_msg']); // Clear the message after displaying
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="../app/Controllers/authcontroller.php" method="POST">
                        <input type="hidden" name="debug" value="1">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-person me-2"></i>Email
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Login</button>

                        <div class="d-flex flex-column gap-2 text-center">
                            <a href="#forgotPassword" class="text-white text-decoration-none" data-bs-toggle="modal">
                                Forgot Password?
                            </a>
                            <a href="#signup" class="text-white text-decoration-none" data-bs-toggle="modal">
                                Don't have an account? Sign up
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal fade" id="signup" tabindex="-1" aria-labelledby="signupLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signupLabel">Sign Up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="signupfunc.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender" onchange="toggleCustomGender()">
                                    <option value="" selected disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Custom">Others</option>
                                </select>
                            </div>

                            <div class="col-12" id="customGenderField" style="display:none;">
                                <label class="form-label">Specify Gender</label>
                                <input type="text" class="form-control" name="custom_gender">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="phone_num" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <button type="submit" name="submit" class="btn btn-success w-100 mt-4">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPassword" tabindex="-1" aria-labelledby="forgotPasswordLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordLabel">Forgot Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgot-password-form" action="#" method="POST">
                        <div class="mb-3">
                            <label for="forgot-email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="forgot-email" name="email" placeholder="Enter your email" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Reset Password</button>
                    </form>
                    <div class="mt-3 otp-boxes" id="otp-boxes">
                        <h5 class="text-center">Enter OTP</h5>
                        <form id="otp-form" action="#" method="POST">
                            <div class="otp-container">
                                <input type="text" class="otp-input" maxlength="1">
                                <input type="text" class="otp-input" maxlength="1">
                                <input type="text" class="otp-input" maxlength="1">
                                <input type="text" class="otp-input" maxlength="1">
                                <input type="text" class="otp-input" maxlength="1">
                                <input type="text" class="otp-input" maxlength="1">
                            </div>
                            <button type="submit" class="btn btn-success w-100 mt-3">Verify OTP</button>
                        </form>
                    </div>
                    <div class="mt-3 change-password-box" id="change-password-box">
                        <h5 class="text-center">Change Password</h5>
                        <form action="#" method="POST">
                            <div class="mb-3">
                                <label for="new-password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new-password" name="new-password" placeholder="Enter new password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Confirm new password" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

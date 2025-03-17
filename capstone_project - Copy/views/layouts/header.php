<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        
        <a class="navbar-brand" href="#">
            <img src="../../public/assets/imgs/logo.png" alt="Village East Admin">
            <div class="brand-text">
                <span class="title">Village East</span>
                <span class="subtitle">Executive Portal</span>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item notification-badge">
                    <a class="nav-link" href="#"><i class="fas fa-bell fa-lg"></i></a>
                </li>
                <li class="nav-item dropdown ms-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg me-2"></i>
                        <span><?php echo $_SESSION['role']; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../../../public/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
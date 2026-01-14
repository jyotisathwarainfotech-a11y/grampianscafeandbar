<?php
// Determine active page
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- HEADER START -->
<div class="container-fluid bg-dark px-0">

    <!-- TOP BAR -->
    <div class="container-fluid border-bottom border-secondary">
        <div class="row py-2 align-items-center px-4">
            <div class="col-lg-6 text-center text-lg-start">
                <i class="fa fa-envelope text-primary me-2"></i>
                <span class="text-secondary">manger.grampianscafe1@gmail.com</span>
            </div>
            <div class="col-lg-6 text-center text-lg-end">
                <i class="fa fa-phone-alt text-primary me-2"></i>
                <span class="text-secondary">+61 457 810 507</span>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4 px-lg-5 py-3" style="background:#111;">
        
        <!-- LOGO -->
        <a href="index.php" class="navbar-brand">
            <img src="img/logo.jpeg" alt="Logo" height="80">
        </a>

        <a href="reservation.php"
            class="btn btn-primary rounded-pill px-3 py-2 ms-auto me-2 d-lg-none">
                RESERVATION NOW
        </a>

        <!-- TOGGLER -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- MENU -->
        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <div class="navbar-nav mx-auto px-6">
                <a href="index.php" class="nav-item nav-link mx-4 <?php echo $currentPage === 'index' ? 'active' : ''; ?>">Home</a>
                <a href="about.php" class="nav-item nav-link mx-4 <?php echo $currentPage === 'about' ? 'active' : ''; ?>">About</a>
                <a href="menu.php" class="nav-item nav-link mx-4 <?php echo $currentPage === 'menu' ? 'active' : ''; ?>">Menu</a>
                <a href="contact.php" class="nav-item nav-link mx-4 <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">Contact</a>
            </div>

            <!-- BUTTON -->
            <div class="d-none d-lg-block ms-3">
                <a href="reservation.php" class="btn btn-primary rounded-pill px-4">
                    RESERVATION NOW
                </a>
            </div>
        </div>
    </nav>
</div>
<!-- HEADER END -->

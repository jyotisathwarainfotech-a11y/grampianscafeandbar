<?php
// Set page title
$pageTitle = "Home - Grampians Cafe & Bar";
require 'includes/head.php';
require 'includes/navbar.php';
?>

    <!-- Hero Start -->
    <div class="container-fluid p-5 mb-5 bg-dark text-secondary position-relative">
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index: 1;">
            <img src="img/menu1.png" class="img-fluid h-100 w-100" style="object-fit: cover; filter: blur(5px); opacity: 0.7;">
        </div>
        <div class="container wow fadeIn" data-wow-delay="0.1s" style="position: relative; z-index: 2;">
            <h1 class="display-1 text-white text-center mb-0">Welcome to Grampians Cafe & Bar</h1>
        </div>
    </div>
    <!-- Hero End -->

    <!-- Your page content goes here -->

<?php require 'includes/footer.php'; ?>

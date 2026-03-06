<?php
require_once __DIR__ . '/functions.php';
// Also include db config early so it's available everywhere header is included
require_once __DIR__ . '/../config/database.php';

// Calculate cart item count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuneHub: Digital Musical Instrument Store</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/motion.css?v=' . time()); ?>">
    <!-- CSRF Token Meta Tag for AJAX -->
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <script>const BASE_URL = '<?php echo base_url(); ?>';</script>

    <!-- Emergency Preloader Failsafe -->
    <script>
        window.addEventListener("load", function() {
            var loader = document.getElementById("tunehub-preloader");
            if (loader) {
                loader.classList.add("fade-out");
                setTimeout(function() {
                    loader.style.display = "none";
                }, 800);
            }
        });

        // Timeout backup in case 'load' gets entirely dropped by aggressive caching
        setTimeout(function() {
            var loader = document.getElementById("tunehub-preloader");
            if (loader && loader.style.display !== "none") {
                loader.classList.add("fade-out");
                setTimeout(function() { loader.style.display = "none"; }, 800);
            }
        }, 1500);
    </script>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Preloader Mask -->
    <div id="tunehub-preloader">
        <div class="equalizer">
            <div class="equalizer-bar"></div>
            <div class="equalizer-bar"></div>
            <div class="equalizer-bar"></div>
            <div class="equalizer-bar"></div>
            <div class="equalizer-bar"></div>
        </div>
    </div>

    <!-- Canvas Particle Visualizer -->
    <canvas id="bg-visualizer"></canvas>

    <a href="#main-content" class="visually-hidden-focusable">Skip to main content</a>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--primary-color);" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo base_url('index.php'); ?>">
                <i class="bi bi-music-note-beamed me-2"></i> TuneHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('about.php'); ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('modules/products/index.php'); ?>">Catalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('modules/tutorials/index.php'); ?>">Tutorials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('contact.php'); ?>">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('faq.php'); ?>">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-label="Shopping Cart" href="<?php echo base_url('modules/cart/index.php'); ?>">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="badge rounded-pill bg-secondary-custom ms-1" id="cart-count"><?php echo $cart_count; ?></span>
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'): ?>
                            <li class="nav-item ms-lg-3">
                                <a class="nav-link btn btn-sm btn-outline-light" href="<?php echo base_url('modules/admin/index.php'); ?>">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarUserMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserMenu">
                                <li><a class="dropdown-item" href="<?php echo base_url('modules/user/profile.php'); ?>">My Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo base_url('modules/user/wishlist.php'); ?>">My Wishlist</a></li>
                                <li><a class="dropdown-item" href="<?php echo base_url('modules/orders/index.php'); ?>">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo base_url('modules/user/logout.php'); ?>">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a class="nav-link" href="<?php echo base_url('modules/user/login.php'); ?>">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-secondary btn-sm ms-2" href="<?php echo base_url('modules/user/register.php'); ?>">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main id="main-content" class="container my-5 flex-grow-1">

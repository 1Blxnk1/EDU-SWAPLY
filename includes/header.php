<?php
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Swaply' : 'Swaply - Township Marketplace'; ?></title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-brand">
            <a href="/index.php" style="display: flex; align-items: center; gap: 10px;">
                <img src="/assets/images/logo.png" alt="Swaply" style="height: 64px; width: auto;">
                <span style="font-size: 1.6rem; font-weight: bold;">Swaply</span>
            </a>
        </div>
        
        <div class="nav-search">
            <form action="/products.php" method="GET">
                <input type="text" name="search" placeholder="Search products..."
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="search-btn" aria-label="Search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>
        </div>

        <button class="nav-toggle" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <div class="nav-links">
            <a href="/index.php">Home</a>
            <a href="/products.php">Shop</a>
            
            <!-- Language Switcher -->
            <div class="lang-switcher" style="position: relative;">
                <button class="lang-btn" onclick="this.nextElementSibling.classList.toggle('show')">
                    [<?php echo $LANGUAGES[getCurrentLanguage()]['code']; ?>] 
                    <?php echo $LANGUAGES[getCurrentLanguage()]['name']; ?>
                    v
                </button>
                <div class="lang-dropdown">
                    <?php foreach ($LANGUAGES as $code => $info): 
                        if ($code !== getCurrentLanguage()):
                    ?>
                    <a href="?lang=<?php echo $code; ?>">
                        [<?php echo $info['code']; ?>] <?php echo $info['name']; ?>
                    </a>
                    <?php endif; endforeach; ?>
                </div>
            </div>

            <?php if (isLoggedIn()): ?>
                <?php if (hasRole('admin')): ?>
                    <a href="/admin/index.php">Admin</a>
                <?php endif; ?>
                <?php if (hasRole('seller')): ?>
                    <a href="/seller_dashboard.php">My Store</a>
                <?php endif; ?>
                
                <a href="/cart.php" class="cart-link">
                    <?php echo __('nav_cart'); ?> (<?php echo getCartCount(); ?>)
                </a>
                <a href="/profile.php"><?php echo htmlspecialchars($_SESSION['full_name']); ?></a>
                <a href="/logout.php" class="btn-logout"><?php echo __('nav_logout'); ?></a>
            <?php else: ?>
                <a href="/login.php"><?php echo __('nav_login'); ?></a>
                <a href="/register.php" class="btn-register"><?php echo __('nav_register'); ?></a>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- ID Verification Warning Banner -->
    <?php if (isLoggedIn() && !isIdVerified() && !hasRole('admin')): ?>
    <div class="bg-warning" style="padding: 12px 20px; text-align: center; font-size: 0.95rem;">
        <strong>Verification Required:</strong> Your account is pending ID verification.
        You can browse products, but buying and selling are disabled until verified.
        <a href="/verification_pending.php" style="margin-left: 10px; font-weight: bold;">Check Status -&gt;</a>
    </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="container">
        
<?php if (isset($_SESSION['message'])): ?>
    <?php echo showMessage($_SESSION['message'], $_SESSION['message_type'] ?? 'success'); ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<?php
$pageTitle = 'Verification Pending';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = $conn->query("SELECT id_verified, id_number FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
if ($user && $user['id_verified']) {
    $_SESSION['id_verified'] = 1;
    $_SESSION['message'] = 'Your ID has been verified! You now have full access.';
    $_SESSION['message_type'] = 'success';
    redirect('index.php');
}
?>

<div style="max-width: 600px; margin: 40px auto; text-align: center;">
    <h1><?php echo __('verify_pending_title'); ?></h1>
    <p style="color: var(--gray); margin: 15px 0; line-height: 1.7;">
        Your account is registered and your email is confirmed. <br>
        <?php echo __('id_hint'); ?>
    </p>

    <div class="card" style="padding: 25px; margin: 25px 0; text-align: left;">
        <h3 style="margin-bottom: 15px;">Your Verification Status</h3>
        <div style="display: grid; gap: 12px;">
            <div class="vstep bg-success text-success">
                <span>Email Confirmed</span>
                <span class="badge badge-success">Complete</span>
            </div>
            <div class="vstep bg-success text-success">
                <span>Profile Created</span>
                <span class="badge badge-success">Complete</span>
            </div>
            <div class="vstep bg-warning text-warning">
                <span><?php echo __('verify_id_submitted'); ?>: <?php echo substr(htmlspecialchars($user['id_number'] ?? ''), 0, 6) . '****' . substr(htmlspecialchars($user['id_number'] ?? ''), 10); ?></span>
                <span class="badge badge-warning"><?php echo __('verify_pending_review'); ?></span>
            </div>
            <div class="vstep bg-gray">
                <span>Full Access (Buy & Sell)</span>
                <span class="badge badge-danger">Blocked</span>
            </div>
        </div>
    </div>

    <div class="card bg-light-blue" style="padding: 20px; margin: 20px 0; border: 1px solid var(--primary);">
        <h4 style="margin-bottom: 10px;">What you can do while waiting:</h4>
        <ul style="text-align: left; color: var(--dark); line-height: 2;">
            <li>Browse all products on the marketplace</li>
            <li>Search and filter products by category</li>
            <li>View seller ratings and reviews</li>
            <li>Read product descriptions and prices</li>
        </ul>
        <p style="margin-top: 10px; font-size: 0.9rem; color: var(--gray);">
            Verification usually takes less than 24 hours. Refresh this page to check your status.
        </p>
    </div>

    <div style="margin-top: 20px;">
        <a href="index.php" class="btn btn-outline arrow-left"><?php echo __('continue_browsing'); ?></a>
        <a href="verification_pending.php" class="btn btn-primary" style="margin-left: 10px;"><?php echo __('refresh_status'); ?></a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

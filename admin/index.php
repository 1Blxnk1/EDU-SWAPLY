<?php
$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';

if (!isLoggedIn() || !hasRole('admin')) {
    $_SESSION['message'] = 'Access denied. Admin only.';
    $_SESSION['message_type'] = 'error';
    redirect('../index.php');
}

$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin'")->fetch_assoc()['c'];
$totalSellers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'seller'")->fetch_assoc()['c'];
$totalBuyers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'buyer'")->fetch_assoc()['c'];
$totalProducts = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$totalOrders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT SUM(final_total) as total FROM orders WHERE status IN ('paid','shipped','delivered')")->fetch_assoc()['total'] ?? 0;
$pendingIdVerifications = $conn->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin' AND id_verified = 0")->fetch_assoc()['c'];
$pendingEmailVerifications = $conn->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin' AND email_verified = 0")->fetch_assoc()['c'];
$openDisputes = $conn->query("SELECT COUNT(*) as c FROM disputes WHERE status = 'open'")->fetch_assoc()['c'] ?? 0;

$recentOrders = $conn->query("SELECT o.*, u.full_name as buyer_name
    FROM orders o
    JOIN users u ON o.buyer_id = u.user_id
    ORDER BY o.created_at DESC
    LIMIT 8");

$recentUsers = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 6");

$flaggedSellers = $conn->query("SELECT u.user_id, u.full_name, u.email, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count
    FROM users u
    JOIN reviews r ON u.user_id = r.seller_id
    WHERE u.role = 'seller'
    GROUP BY u.user_id
    HAVING avg_rating < 3 AND review_count >= 5");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1><?php echo __('admin_dashboard'); ?></h1>
    <span class="badge badge-info">Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
</div>

<?php if ($pendingEmailVerifications > 0 || $pendingIdVerifications > 0): ?>
<div class="bg-warning" style="padding: 15px 20px; margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="text-warning">
            <strong>Action Required:</strong>
            <?php if ($pendingEmailVerifications > 0): ?>
                <?php echo $pendingEmailVerifications; ?> email verification(s) pending.
            <?php endif; ?>
            <?php if ($pendingIdVerifications > 0): ?>
                <?php echo $pendingIdVerifications; ?> ID verification(s) pending.
            <?php endif; ?>
        </div>
        <a href="users.php" class="btn btn-sm btn-primary">Review Users -&gt;</a>
    </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 30px;">
    <a href="products.php" class="card" style="padding: 20px; text-align: center; color: var(--dark);">
        <strong>Products</strong>
    </a>
    <a href="orders.php" class="card" style="padding: 20px; text-align: center; color: var(--dark);">
        <strong>Orders</strong>
    </a>
    <a href="users.php" class="card" style="padding: 20px; text-align: center; color: var(--dark);">
        <strong>Users</strong>
    </a>
    <a href="disputes.php" class="card" style="padding: 20px; text-align: center; color: var(--dark);">
        <strong>Disputes</strong>
    </a>
    <a href="../index.php" class="card" style="padding: 20px; text-align: center; color: var(--dark);">
        <strong>View Site</strong>
    </a>
</div>

<div class="card" style="padding: 20px; margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px;">Overview</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px 24px;">
        <div><strong><?php echo $totalUsers; ?></strong> Total Users</div>
        <div><strong><?php echo $totalSellers; ?></strong> Sellers</div>
        <div><strong><?php echo $totalBuyers; ?></strong> Buyers</div>
        <div><strong><?php echo $totalProducts; ?></strong> Products</div>
        <div><strong><?php echo $totalOrders; ?></strong> Orders</div>
        <div><strong><?php echo formatPrice($totalRevenue); ?></strong> Revenue</div>
        <div class="text-warning"><strong><?php echo $pendingEmailVerifications; ?></strong> Pending Email Verify</div>
        <div class="text-warning"><strong><?php echo $pendingIdVerifications; ?></strong> Pending ID Verify</div>
        <div class="<?php echo $openDisputes > 0 ? 'text-danger' : ''; ?>"><strong><?php echo $openDisputes; ?></strong> Open Disputes</div>
    </div>
</div>

<?php if ($flaggedSellers->num_rows > 0): ?>
<div style="margin: 30px 0;">
    <h2 style="color: var(--danger); margin-bottom: 15px;">Flagged Sellers (Low Rating)</h2>
    <div class="card" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Seller</th>
                    <th>Email</th>
                    <th>Avg Rating</th>
                    <th>Reviews</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($seller = $flaggedSellers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($seller['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($seller['email']); ?></td>
                    <td style="color: var(--danger); font-weight: bold;">
                        <?php echo number_format($seller['avg_rating'], 1); ?>/5
                    </td>
                    <td><?php echo $seller['review_count']; ?></td>
                    <td><span class="badge badge-danger">Needs Review</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">

    <div>
        <h2 style="margin-bottom: 15px;">Recent Orders</h2>
        <div style="display: grid; gap: 10px;">
            <?php while ($order = $recentOrders->fetch_assoc()):
                switch ($order['status']) {
                    case 'paid':
                    case 'delivered':
                        $statusBadge = 'badge-success';
                        break;
                    case 'pending':
                        $statusBadge = 'badge-warning';
                        break;
                    case 'shipped':
                        $statusBadge = 'badge-info';
                        break;
                    case 'cancelled':
                        $statusBadge = 'badge-danger';
                        break;
                    default:
                        $statusBadge = 'badge-warning';
                }
            ?>
            <div class="card" style="padding: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <strong>Order #<?php echo $order['order_id']; ?></strong>
                    <span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <small style="color: var(--gray);">
                    <?php echo htmlspecialchars($order['buyer_name']); ?> |
                    <?php echo formatPrice($order['final_total']); ?> |
                    <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                </small>
            </div>
            <?php endwhile; ?>
        </div>
        <div style="text-align: center; margin-top: 10px;">
            <a href="orders.php" class="btn btn-sm btn-outline">View All Orders</a>
        </div>
    </div>

    <div>
        <h2 style="margin-bottom: 15px;">New Users</h2>
        <div style="display: grid; gap: 10px;">
            <?php while ($user = $recentUsers->fetch_assoc()): ?>
            <div class="card" style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                    <span class="badge <?php echo $user['role'] === 'seller' ? 'badge-info' : 'badge-success'; ?>" style="margin-left: 8px;">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <br>
                    <small style="color: var(--gray);"><?php echo htmlspecialchars($user['email']); ?></small>
                </div>
                <small style="color: var(--gray);">
                    <?php echo date('d M', strtotime($user['created_at'])); ?>
                </small>
            </div>
            <?php endwhile; ?>
        </div>
        <div style="text-align: center; margin-top: 10px;">
            <a href="users.php" class="btn btn-sm btn-outline">View All Users</a>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>

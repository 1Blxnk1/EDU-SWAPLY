<?php
$pageTitle = 'Manage Orders';
require_once '../includes/header.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

$orders = $conn->query("SELECT o.*, u.full_name as buyer_name
    FROM orders o
    JOIN users u ON o.buyer_id = u.user_id
    ORDER BY o.created_at DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>Manage Orders</h1>
    <a href="index.php" class="btn btn-outline btn-sm arrow-left">Back to Dashboard</a>
</div>

<div class="card" style="overflow-x: auto;">
    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Buyer</th>
                <th>Total</th>
                <th>Discount</th>
                <th>Final</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $orders->fetch_assoc()):
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
            <tr>
                <td><a href="order.php?id=<?php echo $order['order_id']; ?>">#<?php echo $order['order_id']; ?></a></td>
                <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                <td><?php echo formatPrice($order['total']); ?></td>
                <td><?php echo $order['discount'] > 0 ? '-' . formatPrice($order['discount']) : '-'; ?></td>
                <td><strong><?php echo formatPrice($order['final_total']); ?></strong></td>
                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

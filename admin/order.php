<?php
$pageTitle = 'Order Details';
require_once '../includes/header.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id < 1) {
    redirect('orders.php');
}

// Status update from the dropdown on this page
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    $status = sanitize($_POST['status']);
    $allowed = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param('si', $status, $order_id);
        $stmt->execute();
        $_SESSION['message'] = 'Order status updated';
        $_SESSION['message_type'] = 'success';
    }
    redirect('order.php?id=' . $order_id);
}

$orderQ = $conn->prepare("SELECT o.*, u.full_name, u.email, u.phone
    FROM orders o JOIN users u ON o.buyer_id = u.user_id
    WHERE o.order_id = ?");
$orderQ->bind_param('i', $order_id);
$orderQ->execute();
$orderRes = $orderQ->get_result();
if ($orderRes->num_rows === 0) {
    $_SESSION['message'] = 'Order not found';
    $_SESSION['message_type'] = 'error';
    redirect('orders.php');
}
$order = $orderRes->fetch_assoc();

$itemsQ = $conn->prepare("SELECT oi.quantity, oi.price_at_time,
    p.name as product_name, p.seller_id, s.full_name as seller_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN users s ON p.seller_id = s.user_id
    WHERE oi.order_id = ?");
$itemsQ->bind_param('i', $order_id);
$itemsQ->execute();
$items = $itemsQ->get_result();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>Order #<?php echo $order['order_id']; ?></h1>
    <a href="orders.php" class="btn btn-outline btn-sm arrow-left">Back to Orders</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 12px;">Buyer</h3>
        <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
        <p><?php echo htmlspecialchars($order['email']); ?></p>
        <p><?php echo htmlspecialchars($order['phone']); ?></p>
        <h4 style="margin-top: 15px; margin-bottom: 6px;">Shipping address</h4>
        <p style="white-space: pre-line;"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 12px;">Summary</h3>
        <p>Placed: <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></p>
        <p>Payment: <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <p>Subtotal: <?php echo formatPrice($order['total']); ?></p>
        <?php if ($order['discount'] > 0): ?>
            <p>Discount: -<?php echo formatPrice($order['discount']); ?></p>
        <?php endif; ?>
        <p style="font-size: 1.1rem;"><strong>Total: <?php echo formatPrice($order['final_total']); ?></strong></p>

        <form method="POST" action="" style="margin-top: 15px; display: flex; gap: 8px;">
            <select name="status">
                <?php foreach (['pending','paid','shipped','delivered','cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update status</button>
        </form>
    </div>

</div>

<h2 style="margin-bottom: 12px;">Items</h2>
<div class="card" style="overflow-x: auto;">
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Seller</th>
                <th>Unit price</th>
                <th>Qty</th>
                <th>Line total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo htmlspecialchars($item['seller_name']); ?></td>
                <td><?php echo formatPrice($item['price_at_time']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo formatPrice($item['price_at_time'] * $item['quantity']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';

// cart page - learnt this pattern in week 8
// initially tried $_SESSION for cart but got confusing with quantities
// switched to database table approach

if (!isLoggedIn()) {
    $_SESSION['message'] = 'Please login to view your cart';
    $_SESSION['message_type'] = 'error';
    redirect('login.php');
}

requireIdVerification();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity']) && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);

        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
            $stmt->bind_param('iii', $quantity, $cart_id, $user_id);
            $stmt->execute();
        }
        redirect('cart.php');
    }

    if (isset($_POST['remove_item']) && isset($_POST['cart_id'])) {
        $cart_id = intval($_POST['cart_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param('ii', $cart_id, $user_id);
        $stmt->execute();
        redirect('cart.php');
    }
}

$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.stock, p.description 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cartItems = $stmt->get_result();

$subtotal = 0;
$itemCount = 0;
$cartData = [];

while ($item = $cartItems->fetch_assoc()) {
    $itemTotal = $item['price'] * $item['quantity'];
    $subtotal += $itemTotal;
    $itemCount += $item['quantity'];
    $item['item_total'] = $itemTotal;
    $cartData[] = $item;
}

$discount = 0;
if (isLoggedIn()) {
    $checkFirst = $conn->query("SELECT first_order FROM users WHERE user_id = $user_id");
    $firstOrder = $checkFirst->fetch_assoc()['first_order'] ?? 0;
    if ($firstOrder && $subtotal > 0) {
        $discount = min(50, $subtotal);
    }
}

$total = $subtotal - $discount;
?>

<h1><?php echo __('cart_title'); ?></h1>

<?php if (empty($cartData)): ?>

<div class="text-center card" style="padding: 60px 20px;">
    <h2><?php echo __('cart_empty'); ?></h2>
    <p style="color: var(--gray); margin: 15px 0;"><?php echo __('cart_browse'); ?></p>
    <a href="products.php" class="btn btn-primary mt-20"><?php echo __('cart_continue'); ?></a>
</div>

<?php else: ?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">

    <div>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartData as $item): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        <br><small style="color: var(--gray);">
                            <?php echo htmlspecialchars(truncate($item['description'], 50)); ?>
                        </small>
                    </td>
                    <td><?php echo formatPrice($item['price']); ?></td>
                    <td>
                        <form method="POST" action="" style="display: flex; align-items: center; gap: 8px;">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                   min="1" max="<?php echo $item['stock']; ?>" style="width: 60px; text-align: center;">
                            <button type="submit" name="update_quantity" class="btn btn-sm btn-outline">Update</button>
                        </form>
                    </td>
                    <td><strong><?php echo formatPrice($item['item_total']); ?></strong></td>
                    <td>
                        <form method="POST" action="" onsubmit="return confirm('Remove this item?');">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" name="remove_item" class="btn btn-sm btn-danger">&times;</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="products.php" class="btn btn-outline arrow-left">Continue Shopping</a>
    </div>

    <div class="card" style="padding: 25px; height: fit-content;">
        <h3 style="margin-bottom: 20px;"><?php echo __('order_summary'); ?></h3>

        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
            <span><?php echo __('subtotal'); ?> (<?php echo $itemCount; ?> items)</span>
            <span><?php echo formatPrice($subtotal); ?></span>
        </div>

        <?php if ($discount > 0): ?>
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--secondary);">
            <span><?php echo __('discount_first'); ?></span>
            <span>-<?php echo formatPrice($discount); ?></span>
        </div>
        <div class="bg-success" style="padding: 10px; margin-bottom: 15px; font-size: 0.85rem;">
            <?php echo __('discount_welcome'); ?>
        </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
            <span><?php echo __('shipping'); ?></span>
            <span class="badge badge-success"><?php echo __('free'); ?></span>
        </div>

        <hr style="margin: 15px 0; border: none; border-top: 1px solid #eee;">

        <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; margin-bottom: 20px;">
            <span><?php echo __('total'); ?></span>
            <span><?php echo formatPrice($total); ?></span>
        </div>

        <a href="checkout.php" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 14px;">
            <?php echo __('checkout_btn'); ?>
        </a>

        <p style="text-align: center; margin-top: 15px; font-size: 0.85rem; color: var(--gray);">
            Secure checkout powered by Swaply
        </p>
    </div>

</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

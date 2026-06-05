<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    $_SESSION['message'] = 'Please login to checkout';
    $_SESSION['message_type'] = 'error';
    redirect('login.php');
}

requireIdVerification();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cartItems = $stmt->get_result();

if ($cartItems->num_rows === 0) {
    $_SESSION['message'] = 'Your cart is empty';
    $_SESSION['message_type'] = 'error';
    redirect('cart.php');
}

$subtotal = 0;
$cartData = [];
while ($item = $cartItems->fetch_assoc()) {
    $itemTotal = $item['price'] * $item['quantity'];
    $subtotal += $itemTotal;
    $item['item_total'] = $itemTotal;
    $cartData[] = $item;
}

$discount = 0;
$checkFirst = $conn->query("SELECT first_order FROM users WHERE user_id = $user_id");
$firstOrder = $checkFirst->fetch_assoc()['first_order'] ?? 0;
if ($firstOrder && $subtotal > 0) {
    $discount = min(50, $subtotal);
}

$total = $subtotal - $discount;

// Luhn checksum - used to validate the card number client + server side
function luhnCheck($num) {
    $num = preg_replace('/\D/', '', $num);
    if (strlen($num) < 13 || strlen($num) > 19) return false;
    $sum = 0;
    $alt = false;
    for ($i = strlen($num) - 1; $i >= 0; $i--) {
        $n = intval($num[$i]);
        if ($alt) { $n *= 2; if ($n > 9) $n -= 9; }
        $sum += $n;
        $alt = !$alt;
    }
    return ($sum % 10) === 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $shipping_address = sanitize($_POST['shipping_address'] ?? '');
    $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    $card_name = sanitize($_POST['card_name'] ?? '');
    $card_expiry = sanitize($_POST['card_expiry'] ?? '');
    $card_cvv = preg_replace('/\D/', '', $_POST['card_cvv'] ?? '');
    $errors = [];

    if (strlen($shipping_address) < 10) {
        $errors[] = 'Please enter a complete shipping address (min 10 characters)';
    }
    if (!luhnCheck($card_number)) {
        $errors[] = 'Card number is invalid';
    }
    if (strlen($card_name) < 2) {
        $errors[] = 'Enter the cardholder name';
    }
    if (!preg_match('#^(0[1-9]|1[0-2])/(\d{2})$#', $card_expiry, $m)) {
        $errors[] = 'Expiry must be MM/YY';
    } else {
        // reject past expiry
        $expYear = 2000 + intval($m[2]);
        $expMonth = intval($m[1]);
        $now = getdate();
        if ($expYear < $now['year'] || ($expYear === $now['year'] && $expMonth < $now['mon'])) {
            $errors[] = 'Card has expired';
        }
    }
    if (!preg_match('/^\d{3,4}$/', $card_cvv)) {
        $errors[] = 'CVV must be 3 or 4 digits';
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            $txnRef = 'TXN-' . strtoupper(uniqid());
            $orderStmt = $conn->prepare("INSERT INTO orders (buyer_id, total, discount, final_total, status, shipping_address, payment_method, transaction_ref) VALUES (?, ?, ?, ?, 'paid', ?, 'card', ?)");
            $orderStmt->bind_param('ddddss', $user_id, $subtotal, $discount, $total, $shipping_address, $txnRef);
            $orderStmt->execute();
            $order_id = $conn->insert_id;

            foreach ($cartData as $item) {
                $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
                $itemStmt->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $itemStmt->execute();

                $newStock = $item['stock'] - $item['quantity'];
                $newStatus = $newStock <= 0 ? 'sold_out' : 'active';
                $conn->query("UPDATE products SET stock = $newStock, status = '$newStatus' WHERE product_id = {$item['product_id']}");
            }

            $conn->query("DELETE FROM cart WHERE user_id = $user_id");

            if ($discount > 0) {
                $conn->query("UPDATE users SET first_order = 0 WHERE user_id = $user_id");
            }

            $conn->commit();

            $_SESSION['message'] = 'Order placed successfully! Order #' . $order_id . ' — Reference ' . $txnRef;
            $_SESSION['message_type'] = 'success';
            redirect('profile.php');

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Order failed. Please try again.';
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo showMessage($error, 'error');
        }
    }
}
?>

<h1><?php echo __('checkout_title'); ?></h1>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">

    <div>
        <div class="card" style="padding: 25px;">
            <h3 style="margin-bottom: 20px;"><?php echo __('shipping_info'); ?></h3>

            <form method="POST" action="" id="checkoutForm" novalidate>

                <div class="form-group">
                    <label for="shipping_address"><?php echo __('delivery_address'); ?> *</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required
                              placeholder="<?php echo __('address_placeholder'); ?>"></textarea>
                </div>

                <h4 style="margin-top: 20px; margin-bottom: 10px;"><?php echo __('payment_method'); ?></h4>
                <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 12px;">
                    Validated by Swaply Secure (demo). No real card will be charged.
                </p>

                <div class="form-group">
                    <label for="card_number">Card number *</label>
                    <input type="text" id="card_number" name="card_number" inputmode="numeric"
                           placeholder="4242 4242 4242 4242" maxlength="19" required>
                </div>

                <div class="form-group">
                    <label for="card_name">Cardholder name *</label>
                    <input type="text" id="card_name" name="card_name" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="card_expiry">Expiry (MM/YY) *</label>
                        <input type="text" id="card_expiry" name="card_expiry"
                               placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label for="card_cvv">CVV *</label>
                        <input type="text" id="card_cvv" name="card_cvv" inputmode="numeric"
                               maxlength="4" required>
                    </div>
                </div>

                <button type="submit" name="place_order" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 14px; margin-top: 10px;">
                    Pay <?php echo formatPrice($total); ?>
                </button>

            </form>
        </div>

        <a href="cart.php" class="btn btn-outline arrow-left mt-20">Back to Cart</a>
    </div>

    <div class="card" style="padding: 25px; height: fit-content;">
        <h3 style="margin-bottom: 20px;"><?php echo __('order_summary'); ?></h3>

        <?php foreach ($cartData as $item): ?>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
            <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
            <span><?php echo formatPrice($item['item_total']); ?></span>
        </div>
        <?php endforeach; ?>

        <hr style="margin: 15px 0; border: none; border-top: 1px solid #eee;">

        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span><?php echo __('subtotal'); ?></span>
            <span><?php echo formatPrice($subtotal); ?></span>
        </div>

        <?php if ($discount > 0): ?>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--secondary);">
            <span><?php echo __('discount_first'); ?></span>
            <span>-<?php echo formatPrice($discount); ?></span>
        </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span><?php echo __('shipping'); ?></span>
            <span class="badge badge-success"><?php echo __('free'); ?></span>
        </div>

        <hr style="margin: 15px 0; border: none; border-top: 1px solid #eee;">

        <div style="display: flex: justify-content: space-between; font-size: 1.3rem; font-weight: bold;">
            <span><?php echo __('total'); ?></span>
            <span><?php echo formatPrice($total); ?></span>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>

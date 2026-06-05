<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

$orders = $conn->query("SELECT * FROM orders WHERE buyer_id = $user_id ORDER BY created_at DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_dispute'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $reason = sanitize($_POST['reason'] ?? '');

    // make sure the order belongs to this buyer
    $check = $conn->prepare("SELECT 1 FROM orders WHERE order_id = ? AND buyer_id = ? LIMIT 1");
    $check->bind_param('ii', $order_id, $user_id);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        $_SESSION['message'] = 'Order not found';
        $_SESSION['message_type'] = 'error';
    } else if (strlen($reason) < 10) {
        $_SESSION['message'] = 'Please describe the issue (at least 10 characters)';
        $_SESSION['message_type'] = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO disputes (order_id, buyer_id, reason) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $order_id, $user_id, $reason);
        $stmt->execute();
        $_SESSION['message'] = 'Your dispute has been filed. An admin will respond shortly.';
        $_SESSION['message_type'] = 'success';
    }
    redirect('profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $seller_id = intval($_POST['seller_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = sanitize($_POST['comment'] ?? '');

    $errors = [];

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Please select a rating between 1 and 5';
    }
    if (strlen($comment) < 5) {
        $errors[] = 'Comment must be at least 5 characters';
    }

    $check = $conn->query("SELECT review_id FROM reviews WHERE order_id = $order_id AND buyer_id = $user_id");
    if ($check->num_rows > 0) {
        $errors[] = 'You have already reviewed this order';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO reviews (order_id, seller_id, buyer_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiis', $order_id, $seller_id, $user_id, $rating, $comment);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Review submitted successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to submit review';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        foreach ($errors as $error) {
            $_SESSION['message'] = $error;
            $_SESSION['message_type'] = 'error';
        }
    }
    redirect('profile.php');
}
?>

<h1><?php echo __('my_profile'); ?></h1>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">

    <div>
        <div class="card" style="padding: 25px; text-align: center;">
            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <p style="color: var(--gray); font-size: 0.9rem;">
                <?php echo htmlspecialchars($user['email']); ?><br>
                <?php echo htmlspecialchars($user['phone']); ?>
            </p>
            <span class="badge badge-info" style="margin-top: 10px; display: inline-block;">
                <?php echo ucfirst($user['role']); ?>
            </span>

            <?php if ($user['id_verified']): ?>
            <div style="margin-top: 10px;">
                <span class="badge badge-success"><?php echo __('id_verified'); ?></span>
            </div>
            <?php else: ?>
            <div style="margin-top: 10px;">
                <span class="badge badge-danger"><?php echo __('id_pending'); ?></span>
                <br><a href="verification_pending.php" style="font-size: 0.85rem;"><?php echo __('check_status'); ?> -&gt;</a>
            </div>
            <?php endif; ?>

            <?php if ($user['first_order']): ?>
            <div class="bg-warning" style="margin-top: 15px; padding: 10px; font-size: 0.85rem;">
                R50 first order discount available!
            </div>
            <?php endif; ?>
        </div>

        <?php if (hasRole('seller')): ?>
        <a href="seller_dashboard.php" class="btn btn-primary" style="width: 100%; margin-top: 15px;">
            <?php echo __('go_to_store'); ?>
        </a>
        <?php endif; ?>
    </div>

    <div>
        <h2 style="margin-bottom: 20px;"><?php echo __('my_orders'); ?></h2>

        <?php if ($orders->num_rows === 0): ?>
            <div class="card" style="padding: 40px; text-align: center;">
                <p style="color: var(--gray);"><?php echo __('no_orders'); ?> <a href="products.php"><?php echo __('start_shopping'); ?></a></p>
            </div>
        <?php else: ?>
            <?php while ($order = $orders->fetch_assoc()):
                $items = $conn->query("SELECT oi.*, p.name, p.seller_id, u.full_name as seller_name
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.product_id
                    JOIN users u ON p.seller_id = u.user_id
                    WHERE oi.order_id = {$order['order_id']}");

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

                // simple text tracker: dim past + future, bold current
                $flow = ['pending', 'paid', 'shipped', 'delivered'];
                $currIdx = array_search($order['status'], $flow);

                $reviewed = $conn->query("SELECT review_id FROM reviews WHERE order_id = {$order['order_id']} AND buyer_id = $user_id");
                $canReview = ($order['status'] === 'paid' || $order['status'] === 'delivered') && $reviewed->num_rows === 0;

                $firstItem = $items->fetch_assoc();
            ?>
            <div class="card" style="padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <strong>Order #<?php echo $order['order_id']; ?></strong>
                        <span style="color: var(--gray); margin-left: 10px; font-size: 0.85rem;">
                            <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                        </span>
                    </div>
                    <span class="badge <?php echo $statusBadge; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>

                <?php if ($order['status'] !== 'cancelled'): ?>
                <div style="margin-bottom: 15px; font-size: 0.9rem;">
                    <?php foreach ($flow as $i => $step): ?>
                        <span style="<?php echo ($currIdx !== false && $i <= $currIdx) ? 'color: var(--primary-dark); font-weight: bold;' : 'color: var(--gray);'; ?>">
                            <?php echo ucfirst($step); ?>
                        </span>
                        <?php if ($i < count($flow) - 1): ?>
                            <span style="color: var(--gray); margin: 0 6px;">&middot;</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div style="margin-bottom: 15px;">
                    <strong><?php echo htmlspecialchars($firstItem['name']); ?></strong>
                    <?php if ($items->num_rows > 0): ?>
                        <span style="color: var(--gray);">and more...</span>
                    <?php endif; ?>
                    <br>
                    <small style="color: var(--gray);">Sold by <?php echo htmlspecialchars($firstItem['seller_name']); ?></small>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <?php if ($order['discount'] > 0): ?>
                        <span style="text-decoration: line-through; color: var(--gray); margin-right: 8px;">
                            <?php echo formatPrice($order['total']); ?>
                        </span>
                        <?php endif; ?>
                        <strong style="font-size: 1.1rem;"><?php echo formatPrice($order['final_total']); ?></strong>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <?php if ($canReview): ?>
                        <button onclick="document.getElementById('reviewForm<?php echo $order['order_id']; ?>').style.display='block'"
                                class="btn btn-sm btn-outline">
                            <?php echo __('write_review'); ?>
                        </button>
                        <?php elseif ($reviewed->num_rows > 0): ?>
                        <span style="color: var(--secondary); font-size: 0.85rem;"><?php echo __('reviewed'); ?></span>
                        <?php endif; ?>
                        <?php if ($order['status'] !== 'cancelled'): ?>
                        <button onclick="document.getElementById('disputeForm<?php echo $order['order_id']; ?>').style.display='block'"
                                class="btn btn-sm btn-outline">
                            Report a problem
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($order['status'] !== 'cancelled'): ?>
                <div id="disputeForm<?php echo $order['order_id']; ?>" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <div class="form-group">
                            <label>What went wrong?</label>
                            <textarea name="reason" rows="3" required minlength="10" placeholder="Describe the issue with this order"></textarea>
                        </div>
                        <button type="submit" name="file_dispute" class="btn btn-sm btn-primary">Submit dispute</button>
                        <button type="button" onclick="document.getElementById('disputeForm<?php echo $order['order_id']; ?>').style.display='none'" class="btn btn-sm btn-outline">Cancel</button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($canReview): ?>
                <div id="reviewForm<?php echo $order['order_id']; ?>" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <input type="hidden" name="seller_id" value="<?php echo $firstItem['seller_id']; ?>">

                        <div class="form-group">
                            <label><?php echo __('your_rating'); ?></label>
                            <div class="rating-input">
                                <input type="radio" id="star5_<?php echo $order['order_id']; ?>" name="rating" value="5">
                                <label for="star5_<?php echo $order['order_id']; ?>">*</label>
                                <input type="radio" id="star4_<?php echo $order['order_id']; ?>" name="rating" value="4">
                                <label for="star4_<?php echo $order['order_id']; ?>">*</label>
                                <input type="radio" id="star3_<?php echo $order['order_id']; ?>" name="rating" value="3">
                                <label for="star3_<?php echo $order['order_id']; ?>">*</label>
                                <input type="radio" id="star2_<?php echo $order['order_id']; ?>" name="rating" value="2">
                                <label for="star2_<?php echo $order['order_id']; ?>">*</label>
                                <input type="radio" id="star1_<?php echo $order['order_id']; ?>" name="rating" value="1">
                                <label for="star1_<?php echo $order['order_id']; ?>">*</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comment"><?php echo __('your_review'); ?></label>
                            <textarea id="comment" name="comment" rows="3" placeholder="Share your experience with this seller..."></textarea>
                        </div>

                        <button type="submit" name="submit_review" class="btn btn-primary btn-sm"><?php echo __('submit_review'); ?></button>
                        <button type="button" onclick="document.getElementById('reviewForm<?php echo $order['order_id']; ?>').style.display='none'"
                                class="btn btn-outline btn-sm" style="margin-left: 8px;"><?php echo __('cancel'); ?></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>

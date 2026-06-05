<?php
require_once 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'Invalid product';
    $_SESSION['message_type'] = 'error';
    redirect('products.php');
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT p.*, u.full_name as seller_name, c.name as category_name 
    FROM products p 
    JOIN users u ON p.seller_id = u.user_id 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.product_id = ? AND p.status = 'active'");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Product not found';
    $_SESSION['message_type'] = 'error';
    redirect('products.php');
}

$product = $result->fetch_assoc();
$pageTitle = $product['name'];

$sellerRating = getSellerRating($product['seller_id']);

$reviews = $conn->query("SELECT r.*, u.full_name as buyer_name 
    FROM reviews r 
    JOIN users u ON r.buyer_id = u.user_id 
    WHERE r.seller_id = {$product['seller_id']} 
    ORDER BY r.created_at DESC 
    LIMIT 5");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $_SESSION['message'] = 'Please login to add items to your cart';
        $_SESSION['message_type'] = 'error';
        redirect('login.php');
    }

    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    if ($quantity < 1) $quantity = 1;
    if ($quantity > $product['stock']) $quantity = $product['stock'];

    $user_id = $_SESSION['user_id'];

    // C2C rule: one seller per cart at a time. Buyer must finish or
    // empty the current cart before adding from a different seller.
    $other = $conn->prepare("SELECT 1 FROM cart c JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ? AND p.seller_id != ? LIMIT 1");
    $other->bind_param('ii', $user_id, $product['seller_id']);
    $other->execute();
    if ($other->get_result()->num_rows > 0) {
        $_SESSION['message'] = __('cart_one_seller_only');
        $_SESSION['message_type'] = 'error';
        redirect('product.php?id=' . $product_id);
    }

    $check = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $check->bind_param('ii', $user_id, $product_id);
    $check->execute();
    $cartResult = $check->get_result();

    if ($cartResult->num_rows > 0) {
        $cartItem = $cartResult->fetch_assoc();
        $newQty = $cartItem['quantity'] + $quantity;
        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $update->bind_param('ii', $newQty, $cartItem['cart_id']);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->bind_param('iii', $user_id, $product_id, $quantity);
        $insert->execute();
    }

    $_SESSION['message'] = 'Product added to cart!';
    $_SESSION['message_type'] = 'success';
    redirect('product.php?id=' . $product_id);
}
?>

<div class="product-detail">

    <div>
        <div style="border-radius: var(--radius); height: 400px; overflow: hidden;">
            <img src="/assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    </div>

    <div class="product-info">
        <span class="badge badge-info"><?php echo htmlspecialchars($product['category_name'] ?? 'General'); ?></span>

        <h1><?php echo htmlspecialchars($product['name']); ?></h1>

        <div class="seller">
            Sold by <strong><?php echo htmlspecialchars($product['seller_name']); ?></strong>
            <span style="margin-left: 10px;">
                <?php echo renderStars($sellerRating['average']); ?>
                <small>(<?php echo $sellerRating['total']; ?> reviews)</small>
            </span>
        </div>

        <div class="price"><?php echo formatPrice($product['price']); ?></div>

        <?php if ($product['stock'] > 0): ?>
            <span class="badge badge-success">In Stock (<?php echo $product['stock']; ?> available)</span>
        <?php else: ?>
            <span class="badge badge-danger">Out of Stock</span>
        <?php endif; ?>

        <p style="margin: 20px 0; line-height: 1.8;">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </p>

        <?php if ($product['stock'] > 0): ?>
        <form method="POST" action="">
            <div class="form-group" style="max-width: 150px;">
                <label>Quantity</label>
                <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
            </div>
            <button type="submit" name="add_to_cart" class="btn btn-primary" style="font-size: 1.1rem; padding: 12px 30px;">
                Add to Cart
            </button>
            <a href="cart.php" class="btn btn-outline" style="margin-left: 10px;">View Cart</a>
        </form>
        <?php endif; ?>

        <div style="margin-top: 30px; padding: 20px; background: var(--light); border-radius: var(--radius);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; font-size: 0.9rem;">
                <div class="trust-badge">Secure Payment</div>
                <div class="trust-badge">Verified Seller</div>
                <div class="trust-badge">Buyer Protection</div>
                <div class="trust-badge">Quality Guaranteed</div>
            </div>
        </div>
    </div>

</div>

<section style="margin-top: 40px;">
    <h2>Seller Reviews</h2>

    <?php if ($reviews->num_rows > 0): ?>
        <div style="display: grid; gap: 15px; margin-top: 20px;">
            <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="card" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong><?php echo htmlspecialchars($review['buyer_name']); ?></strong>
                    <span style="color: var(--gray); font-size: 0.85rem;">
                        <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                    </span>
                </div>
                <div style="margin-bottom: 8px;">
                    <?php echo renderStars($review['rating']); ?>
                </div>
                <p style="color: var(--dark);"><?php echo htmlspecialchars($review['comment']); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--gray); margin-top: 15px;">No reviews yet. Be the first to buy and review!</p>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>

<?php
$pageTitle = 'Seller Dashboard';
require_once 'includes/header.php';

// Only sellers and admins allowed
if (!isLoggedIn() || (!hasRole('seller') && !hasRole('admin'))) {
    $_SESSION['message'] = 'Access denied. Seller account required.';
    $_SESSION['message_type'] = 'error';
    redirect('index.php');
}

// Enforce ID verification for selling
requireIdVerification();

$user_id = $_SESSION['user_id'];

// ---- Process Product Actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add product
    if (isset($_POST['add_product'])) {
        $name = sanitize($_POST['name'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        // Clamp to column ranges. price is DECIMAL(10,2) → max ~99,999,999.99;
        // stock is INT → cap at 99999 (anything more is unrealistic for a township seller).
        $price = max(0, min(99999999.99, floatval($_POST['price'] ?? 0)));
        $stock = max(0, min(99999, intval($_POST['stock'] ?? 0)));

        // image upload - keep default if nothing uploaded
        $imagePath = 'logo.png';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = mime_content_type($_FILES['image']['tmp_name']);

            if (!isset($allowed[$mime])) {
                $_SESSION['message'] = 'Image must be JPG, PNG or WebP';
                $_SESSION['message_type'] = 'error';
                redirect('seller_dashboard.php');
            }
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $_SESSION['message'] = 'Image must be under 2MB';
                $_SESSION['message_type'] = 'error';
                redirect('seller_dashboard.php');
            }

            // Always store as .jpg after compression
            $filename = 'products/' . uniqid('p_', true) . '.jpg';
            $dest = __DIR__ . '/assets/images/' . $filename;
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0775, true);
            }

            // Resize to max 800px and re-save as JPEG q=75. Keeps page weight
            // down so we don't break Goal 2.
            $src = null;
            switch ($mime) {
                case 'image/jpeg': $src = @imagecreatefromjpeg($_FILES['image']['tmp_name']); break;
                case 'image/png':  $src = @imagecreatefrompng($_FILES['image']['tmp_name']); break;
                case 'image/webp': $src = @imagecreatefromwebp($_FILES['image']['tmp_name']); break;
            }
            if ($src) {
                $w = imagesx($src); $h = imagesy($src);
                $max = 800;
                if ($w > $max || $h > $max) {
                    if ($w >= $h) { $nw = $max; $nh = (int)($h * $max / $w); }
                    else          { $nh = $max; $nw = (int)($w * $max / $h); }
                } else { $nw = $w; $nh = $h; }
                $dst = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                if (imagejpeg($dst, $dest, 75)) {
                    $imagePath = $filename;
                }
                imagedestroy($src); imagedestroy($dst);
            } else if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                // GD unavailable or unreadable file - fall back to the raw upload
                $imagePath = $filename;
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (seller_id, category_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissdis', $user_id, $category_id, $name, $description, $price, $stock, $imagePath);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Product added successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to add product';
            $_SESSION['message_type'] = 'error';
        }
        redirect('seller_dashboard.php');
    }
    
    // Update order status (only on orders containing this seller's products)
    if (isset($_POST['update_order_status']) && isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
        $status = sanitize($_POST['status'] ?? '');
        $allowed = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
        if (in_array($status, $allowed)) {
            // confirm the order has at least one item from this seller
            $owns = $conn->prepare("SELECT 1 FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = ? AND p.seller_id = ? LIMIT 1");
            $owns->bind_param('ii', $order_id, $user_id);
            $owns->execute();
            if ($owns->get_result()->num_rows > 0) {
                $upd = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
                $upd->bind_param('si', $status, $order_id);
                $upd->execute();
                $_SESSION['message'] = 'Order status updated';
                $_SESSION['message_type'] = 'success';
            }
        }
        redirect('seller_dashboard.php');
    }

    // Delete product
    if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ? AND seller_id = ?");
        $stmt->bind_param('ii', $product_id, $user_id);
        $stmt->execute();
        $_SESSION['message'] = 'Product deleted';
        $_SESSION['message_type'] = 'success';
        redirect('seller_dashboard.php');
    }
}

// ---- Fetch Seller Stats ----

// Total revenue
$revenueResult = $conn->query("SELECT SUM(oi.price_at_time * oi.quantity) as total 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    JOIN orders o ON oi.order_id = o.order_id 
    WHERE p.seller_id = $user_id AND o.status IN ('paid', 'shipped', 'delivered')");
$totalRevenue = $revenueResult->fetch_assoc()['total'] ?? 0;

// Total orders
$ordersResult = $conn->query("SELECT COUNT(DISTINCT o.order_id) as total 
    FROM orders o 
    JOIN order_items oi ON o.order_id = oi.order_id 
    JOIN products p ON oi.product_id = p.product_id 
    WHERE p.seller_id = $user_id");
$totalOrders = $ordersResult->fetch_assoc()['total'] ?? 0;

// Active products
$productsResult = $conn->query("SELECT COUNT(*) as total FROM products WHERE seller_id = $user_id AND status = 'active'");
$activeProducts = $productsResult->fetch_assoc()['total'] ?? 0;

// Rating
$rating = getSellerRating($user_id);

// Fetch my products
$myProducts = $conn->query("SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.seller_id = $user_id 
    ORDER BY p.created_at DESC");

// Fetch my orders
$myOrders = $conn->query("SELECT o.*, oi.quantity, oi.price_at_time, p.name as product_name, u.full_name as buyer_name 
    FROM orders o 
    JOIN order_items oi ON o.order_id = oi.order_id 
    JOIN products p ON oi.product_id = p.product_id 
    JOIN users u ON o.buyer_id = u.user_id 
    WHERE p.seller_id = $user_id 
    ORDER BY o.created_at DESC 
    LIMIT 10");

// Fetch my reviews
$myReviews = $conn->query("SELECT r.*, u.full_name as buyer_name 
    FROM reviews r 
    JOIN users u ON r.buyer_id = u.user_id 
    WHERE r.seller_id = $user_id 
    ORDER BY r.created_at DESC 
    LIMIT 5");

// Categories for product form
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<h1><?php echo __('sd_title'); ?></h1>

<div class="card" style="padding: 20px; margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px;"><?php echo __('sd_overview'); ?></h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px 24px;">
        <div><strong><?php echo formatPrice($totalRevenue); ?></strong> <?php echo __('sd_total_revenue'); ?></div>
        <div><strong><?php echo $totalOrders; ?></strong> <?php echo __('sd_orders_received'); ?></div>
        <div><strong><?php echo $activeProducts; ?></strong> <?php echo __('sd_active_products'); ?></div>
        <div><strong><?php echo $rating['average']; ?>/5</strong> <?php echo __('sd_rating'); ?> (<?php echo $rating['total']; ?>)</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">

    <!-- Products Management -->
    <div>
        <div class="flex justify-between items-center" style="margin-bottom: 20px;">
            <h2><?php echo __('sd_my_products'); ?></h2>
            <button onclick="document.getElementById('addProductForm').style.display='block'" class="btn btn-primary btn-sm">
                + <?php echo __('sd_add_product'); ?>
            </button>
        </div>

        <!-- Add Product Form (Hidden) -->
        <div id="addProductForm" style="display: none; margin-bottom: 20px;" class="card" style="padding: 20px;">
            <h4 style="margin-bottom: 15px;"><?php echo __('sd_add_product'); ?></h4>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label><?php echo __('sd_product_name'); ?></label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label><?php echo __('sd_product_image'); ?></label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                    <small style="color: var(--gray);"><?php echo __('sd_image_hint'); ?></small>
                </div>
                <div class="form-group">
                    <label><?php echo __('sd_category'); ?></label>
                    <select name="category_id" required>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo __('sd_description'); ?></label>
                    <textarea name="description" rows="3" required></textarea>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label><?php echo __('sd_price'); ?> (R)</label>
                        <input type="number" name="price" step="0.01" min="0" max="99999999.99" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo __('sd_stock'); ?></label>
                        <input type="number" name="stock" min="0" max="99999" required>
                    </div>
                </div>
                <button type="submit" name="add_product" class="btn btn-primary btn-sm"><?php echo __('sd_add_product'); ?></button>
                <button type="button" onclick="document.getElementById('addProductForm').style.display='none'"
                        class="btn btn-outline btn-sm" style="margin-left: 8px;"><?php echo __('cancel'); ?></button>
            </form>
        </div>

        <!-- Products List -->
        <?php if ($myProducts->num_rows === 0): ?>
            <p style="color: var(--gray);"><?php echo __('sd_no_products'); ?></p>
        <?php else: ?>
            <div style="display: grid; gap: 10px;">
                <?php while ($product = $myProducts->fetch_assoc()): 
                    switch ($product['status']) {
                        case 'active':
                            $statusBadge = 'badge-success';
                            break;
                        case 'sold_out':
                            $statusBadge = 'badge-danger';
                            break;
                        default:
                            $statusBadge = 'badge-warning';
                    }
                ?>
                <div class="card" style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                        <span class="badge <?php echo $statusBadge; ?>" style="margin-left: 8px;">
                            <?php echo ucfirst($product['status']); ?>
                        </span>
                        <br>
                        <small style="color: var(--gray);">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'General'); ?> |
                            <?php echo __('sd_stock'); ?>: <?php echo $product['stock']; ?> |
                            <?php echo formatPrice($product['price']); ?>
                        </small>
                    </div>
                    <form method="POST" action="" onsubmit="return confirm('<?php echo __('sd_confirm_delete'); ?>');" style="margin: 0;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <button type="submit" name="delete_product" class="btn btn-sm btn-danger">&times;</button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Orders & Reviews -->
    <div>
        <h2 style="margin-bottom: 20px;"><?php echo __('sd_recent_orders'); ?></h2>

        <?php if ($myOrders->num_rows === 0): ?>
            <p style="color: var(--gray);"><?php echo __('sd_no_orders'); ?></p>
        <?php else: ?>
            <div style="display: grid; gap: 10px; margin-bottom: 30px;">
                <?php while ($order = $myOrders->fetch_assoc()): 
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
                        default:
                            $statusBadge = 'badge-warning';
                    }
                ?>
                <div class="card" style="padding: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <strong><?php echo __('sd_order_no'); ?><?php echo $order['order_id']; ?></strong>
                        <span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                    <p style="font-size: 0.9rem; margin-bottom: 5px;">
                        <?php echo htmlspecialchars($order['product_name']); ?> x<?php echo $order['quantity']; ?>
                    </p>
                    <small style="color: var(--gray);">
                        <?php echo __('sd_buyer'); ?>: <?php echo htmlspecialchars($order['buyer_name']); ?> |
                        <?php echo formatPrice($order['price_at_time'] * $order['quantity']); ?> |
                        <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                    </small>
                    <form method="POST" action="" style="margin-top: 8px; display: flex; gap: 6px;">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <select name="status">
                            <?php foreach (['pending','paid','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_order_status" class="btn btn-sm btn-primary">Update</button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <h2 style="margin-bottom: 20px;"><?php echo __('sd_recent_reviews'); ?></h2>

        <?php if ($myReviews->num_rows === 0): ?>
            <p style="color: var(--gray);"><?php echo __('sd_no_reviews'); ?></p>
        <?php else: ?>
            <div style="display: grid; gap: 10px;">
                <?php while ($review = $myReviews->fetch_assoc()): ?>
                <div class="card" style="padding: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span><?php echo renderStars($review['rating']); ?></span>
                        <small style="color: var(--gray);"><?php echo date('d M Y', strtotime($review['created_at'])); ?></small>
                    </div>
                    <p style="font-size: 0.9rem;"><?php echo htmlspecialchars($review['comment']); ?></p>
                    <small style="color: var(--gray);">- <?php echo htmlspecialchars($review['buyer_name']); ?></small>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>

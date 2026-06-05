<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

$featured = $conn->query("SELECT p.*, u.full_name as seller_name 
    FROM products p 
    JOIN users u ON p.seller_id = u.user_id 
    WHERE p.status = 'active' 
    ORDER BY p.created_at DESC 
    LIMIT 6");

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

$productCount = $conn->query("SELECT COUNT(*) as c FROM products WHERE status = 'active'")->fetch_assoc()['c'];
$sellerCount = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'seller'")->fetch_assoc()['c'];
?>

<section class="hero">
    <h1><?php echo __('hero_title'); ?></h1>
    <p><?php echo __('hero_subtitle'); ?></p>
    <div style="margin-top: 20px;">
        <a href="products.php" class="btn btn-primary" style="margin-right: 10px;"><?php echo __('hero_btn_shop'); ?></a>
        <a href="register.php?type=seller" class="btn btn-outline"><?php echo __('hero_btn_sell'); ?></a>
    </div>
    <p style="margin-top: 15px; font-size: 0.95rem; opacity: 0.8;">
        <?php echo __('hero_stats', ['products' => $productCount, 'sellers' => $sellerCount]); ?>
    </p>
</section>

<section style="margin-bottom: 30px;">
    <h2 style="margin-bottom: 20px; text-align: center;"><?php echo __('cat_browse'); ?></h2>
    <div class="categories">
        <?php while ($cat = $categories->fetch_assoc()): ?>
        <a href="/products.php?category=<?php echo $cat['category_id']; ?>" class="category-card">
            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
        </a>
        <?php endwhile; ?>
    </div>
</section>

<section>
    <div class="flex justify-between items-center" style="margin-bottom: 20px;">
        <h2><?php echo __('fp_title'); ?></h2>
        <a href="products.php" class="btn btn-sm btn-outline"><?php echo __('fp_view_all'); ?></a>
    </div>

    <div class="grid">
        <?php while ($product = $featured->fetch_assoc()):
            $rating = getSellerRating($product['seller_id']);
        ?>
        <div class="card">
            <div class="card-img" style="height: 200px; overflow: hidden;">
                <img src="/assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="card-body">
                <div class="card-title"><?php echo htmlspecialchars($product['name']); ?></div>
                <div class="card-text">
                    <?php echo htmlspecialchars(truncate($product['description'], 80)); ?>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-size: 0.85rem; color: var(--gray);">
                        <?php echo __('by_seller', ['name' => htmlspecialchars($product['seller_name'])]); ?>
                    </span>
                    <span style="font-size: 0.85rem;">
                        <?php echo renderStars($rating['average']); ?>
                        <small>(<?php echo $rating['total']; ?>)</small>
                    </span>
                </div>
                <div class="card-price"><?php echo formatPrice($product['price']); ?></div>
                <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary" style="width: 100%;"><?php echo __('btn_view_product'); ?></a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<section style="margin-top: 40px;">
    <h2 style="margin-bottom: 15px;"><?php echo __('hiw_title'); ?></h2>
    <ol style="padding-left: 20px; line-height: 1.8;">
        <li><strong><?php echo __('hiw_step1_title'); ?>.</strong> <?php echo __('hiw_step1_desc'); ?></li>
        <li><strong><?php echo __('hiw_step2_title'); ?>.</strong> <?php echo __('hiw_step2_desc'); ?></li>
        <li><strong><?php echo __('hiw_step3_title'); ?>.</strong> <?php echo __('hiw_step3_desc'); ?></li>
    </ol>
</section>

<?php require_once 'includes/footer.php'; ?>

<?php
$pageTitle = 'Shop';
require_once 'includes/header.php';

$where = ["p.status = 'active'"];
$params = [];
$types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $category_id = intval($_GET['category']);
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $where[] = "p.price >= ?";
    $params[] = floatval($_GET['min_price']);
    $types .= 'd';
}
if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $where[] = "p.price <= ?";
    $params[] = floatval($_GET['max_price']);
    $types .= 'd';
}

$whereClause = implode(' AND ', $where);

$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
switch ($sort) {
    case 'price_low':
        $orderBy = 'p.price ASC';
        break;
    case 'price_high':
        $orderBy = 'p.price DESC';
        break;
    case 'name':
        $orderBy = 'p.name ASC';
        break;
    default:
        $orderBy = 'p.created_at DESC';
}

$sql = "SELECT p.*, u.full_name as seller_name, c.name as category_name 
    FROM products p 
    JOIN users u ON p.seller_id = u.user_id 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE $whereClause 
    ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<div class="flex justify-between items-center" style="margin-bottom: 20px;">
    <h1><?php echo __('prod_browse'); ?></h1>
    <span style="color: var(--gray);"><?php echo $products->num_rows; ?> <?php echo __('prod_found'); ?></span>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">

    <aside class="card" style="padding: 20px; height: fit-content;">
        <h3 style="margin-bottom: 15px;"><?php echo __('prod_filters'); ?></h3>

        <form method="GET" action="">

            <div class="form-group">
                <label><?php echo __('sd_category'); ?></label>
                <select name="category">
                    <option value=""><?php echo __('prod_all_categories'); ?></option>
                    <?php
                    $categories->data_seek(0);
                    while ($cat = $categories->fetch_assoc()):
                    ?>
                    <option value="<?php echo $cat['category_id']; ?>"
                        <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label><?php echo __('prod_price_range'); ?></label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="number" name="min_price" placeholder="<?php echo __('prod_min'); ?>" style="width: 80px;"
                           value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                    <span>-</span>
                    <input type="number" name="max_price" placeholder="<?php echo __('prod_max'); ?>" style="width: 80px;"
                           value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label><?php echo __('prod_sort_by'); ?></label>
                <select name="sort">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>><?php echo __('prod_sort_newest'); ?></option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>><?php echo __('prod_sort_price_low'); ?></option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>><?php echo __('prod_sort_price_high'); ?></option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>><?php echo __('prod_sort_name'); ?></option>
                </select>
            </div>

            <?php if (isset($_GET['search'])): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
            <?php endif; ?>

            <button type="submit" class="btn btn-primary" style="width: 100%;"><?php echo __('prod_apply'); ?></button>
            <a href="/products.php" class="btn btn-outline btn-sm" style="width: 100%; margin-top: 8px; display: block; text-align: center;"><?php echo __('prod_clear'); ?></a>

        </form>
    </aside>

    <div>
        <?php if ($products->num_rows === 0): ?>
            <div class="text-center card" style="padding: 60px 20px; color: var(--gray);">
                <h3><?php echo __('prod_none'); ?></h3>
                <p><?php echo __('prod_none_hint'); ?></p>
                <a href="/products.php" class="btn btn-outline mt-20"><?php echo __('prod_view_all'); ?></a>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php while ($product = $products->fetch_assoc()):
                    $rating = getSellerRating($product['seller_id']);
                ?>
                <div class="card">
                    <div class="card-img" style="height: 200px; overflow: hidden;">
                        <img src="/assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="card-body">
                        <span class="badge badge-info" style="margin-bottom: 8px; display: inline-block;">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'General'); ?>
                        </span>
                        <div class="card-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="card-text">
                            <?php echo htmlspecialchars(truncate($product['description'], 60)); ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.85rem; color: var(--gray);">
                                <?php echo __('prod_by'); ?> <?php echo htmlspecialchars($product['seller_name']); ?>
                            </span>
                            <span style="font-size: 0.8rem;">
                                <?php echo renderStars($rating['average']); ?>
                            </span>
                        </div>
                        <div class="card-price"><?php echo formatPrice($product['price']); ?></div>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge badge-success" style="margin-bottom: 10px; display: inline-block;">
                                <?php echo $product['stock']; ?> <?php echo __('prod_in_stock'); ?>
                            </span>
                        <?php else: ?>
                            <span class="badge badge-danger" style="margin-bottom: 10px; display: inline-block;"><?php echo __('prod_sold_out'); ?></span>
                        <?php endif; ?>
                        <a href="/product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary" style="width: 100%;"><?php echo __('prod_view'); ?></a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>

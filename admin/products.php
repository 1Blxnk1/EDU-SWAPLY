<?php
$pageTitle = 'Manage Products';
require_once '../includes/header.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE product_id = $id");
    $_SESSION['message'] = 'Product deleted';
    $_SESSION['message_type'] = 'success';
    redirect('products.php');
}

if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE products SET status = IF(status='active', 'inactive', 'active') WHERE product_id = $id");
    redirect('products.php');
}

$products = $conn->query("SELECT p.*, u.full_name as seller_name, c.name as category_name
    FROM products p
    JOIN users u ON p.seller_id = u.user_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>Manage Products</h1>
    <a href="index.php" class="btn btn-outline btn-sm arrow-left">Back to Dashboard</a>
</div>

<div class="card" style="overflow-x: auto;">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Category</th>
                <th>Seller</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $products->fetch_assoc()):
                switch ($p['status']) {
                    case 'active':
                        $statusBadge = 'badge-success';
                        break;
                    case 'inactive':
                        $statusBadge = 'badge-warning';
                        break;
                    case 'sold_out':
                        $statusBadge = 'badge-danger';
                        break;
                    default:
                        $statusBadge = 'badge-info';
                }
            ?>
            <tr>
                <td>#<?php echo $p['product_id']; ?></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($p['seller_name']); ?></td>
                <td><?php echo formatPrice($p['price']); ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                <td>
                    <a href="?toggle=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline">
                        <?php echo $p['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                    </a>
                    <a href="?delete=<?php echo $p['product_id']; ?>"
                       onclick="return confirm('Delete this product?')"
                       class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

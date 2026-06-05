<?php
$pageTitle = 'Manage Users';
require_once '../includes/header.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if (isset($_GET['toggle_role']) && is_numeric($_GET['toggle_role'])) {
    $id = intval($_GET['toggle_role']);
    $user = $conn->query("SELECT role FROM users WHERE user_id = $id")->fetch_assoc();
    if ($user) {
        $newRole = $user['role'] === 'seller' ? 'buyer' : 'seller';
        $conn->query("UPDATE users SET role = '$newRole' WHERE user_id = $id");
    }
    redirect('users.php');
}

if (isset($_GET['verify_id']) && is_numeric($_GET['verify_id'])) {
    $id = intval($_GET['verify_id']);
    $user = $conn->query("SELECT id_verified FROM users WHERE user_id = $id");
    if ($user && $user->num_rows > 0) {
        $row = $user->fetch_assoc();
        $newStatus = $row['id_verified'] ? 0 : 1;
        $conn->query("UPDATE users SET id_verified = $newStatus WHERE user_id = $id");
        $_SESSION['message'] = 'ID verification status updated';
        $_SESSION['message_type'] = 'success';
    }
    redirect('users.php');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE user_id = $id AND role != 'admin'");
    $_SESSION['message'] = 'User deleted';
    $_SESSION['message_type'] = 'success';
    redirect('users.php');
}

$users = $conn->query("SELECT u.*,
    (SELECT COUNT(*) FROM products WHERE seller_id = u.user_id) as product_count,
    (SELECT COUNT(*) FROM orders WHERE buyer_id = u.user_id) as order_count
    FROM users u
    WHERE u.role != 'admin'
    ORDER BY u.created_at DESC");

$flagged = $conn->query("SELECT u.user_id, u.full_name, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count
    FROM users u
    JOIN reviews r ON u.user_id = r.seller_id
    WHERE u.role = 'seller'
    GROUP BY u.user_id
    HAVING avg_rating < 3 AND review_count >= 5");

$flaggedIds = [];
while ($f = $flagged->fetch_assoc()) {
    $flaggedIds[] = $f['user_id'];
}

?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>Manage Users</h1>
    <a href="index.php" class="btn btn-outline btn-sm arrow-left">Back to Dashboard</a>
</div>

<div class="card" style="overflow-x: auto;">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Email Status</th>
                <th>ID Status</th>
                <th>ID Number</th>
                <th>Products</th>
                <th>Orders</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()):
                $isFlagged = in_array($user['user_id'], $flaggedIds);
                $roleBadge = $user['role'] === 'seller' ? 'badge-info' : 'badge-success';
            ?>
            <tr <?php echo $isFlagged ? 'class="bg-danger" style="background: #fff5f5;"' : ''; ?>>
                <td>#<?php echo $user['user_id']; ?></td>
                <td>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                    <?php if ($isFlagged): ?>
                        <span style="color: var(--danger); font-weight: bold;">!</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><span class="badge <?php echo $roleBadge; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                <td>
                    <?php if ($user['email_verified']): ?>
                        <span class="badge badge-success" title="Email Verified">Verified</span>
                    <?php else: ?>
                        <span class="badge badge-danger" title="Email Pending">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($user['id_verified']): ?>
                        <span class="badge badge-success" title="ID Verified">Verified</span>
                    <?php else: ?>
                        <span class="badge badge-danger" title="ID Pending">Pending</span>
                    <?php endif; ?>
                </td>
                <td><code style="font-size: 0.85rem;"><?php echo htmlspecialchars($user['id_number'] ?? 'N/A'); ?></code></td>
                <td><?php echo $user['product_count']; ?></td>
                <td><?php echo $user['order_count']; ?></td>
                <td>
                    <a href="?verify_id=<?php echo $user['user_id']; ?>"
                       class="btn btn-sm <?php echo $user['id_verified'] ? 'btn-danger' : 'btn-secondary'; ?>"
                       onclick="return confirm('<?php echo $user['id_verified'] ? 'Revoke' : 'Approve'; ?> ID verification for <?php echo htmlspecialchars($user['full_name']); ?>?')">
                        <?php echo $user['id_verified'] ? 'Revoke ID' : 'Verify ID'; ?>
                    </a>
                    <a href="?toggle_role=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline">
                        Make <?php echo $user['role'] === 'seller' ? 'Buyer' : 'Seller'; ?>
                    </a>
                    <a href="?delete=<?php echo $user['user_id']; ?>"
                       onclick="return confirm('Delete this user?')"
                       class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

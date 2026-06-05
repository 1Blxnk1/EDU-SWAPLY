<?php
$pageTitle = 'Disputes';
require_once '../includes/header.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if (isset($_POST['resolve_dispute']) && isset($_POST['dispute_id'])) {
    $dispute_id = intval($_POST['dispute_id']);
    $response = sanitize($_POST['admin_response'] ?? '');
    if (strlen($response) < 5) {
        $_SESSION['message'] = 'Write a short response before resolving';
        $_SESSION['message_type'] = 'error';
    } else {
        $stmt = $conn->prepare("UPDATE disputes SET status='resolved', admin_response=?, resolved_at=NOW() WHERE dispute_id = ?");
        $stmt->bind_param('si', $response, $dispute_id);
        $stmt->execute();
        $_SESSION['message'] = 'Dispute resolved';
        $_SESSION['message_type'] = 'success';
    }
    redirect('disputes.php');
}

$disputes = $conn->query("SELECT d.*, u.full_name as buyer_name, u.email as buyer_email
    FROM disputes d
    JOIN users u ON d.buyer_id = u.user_id
    ORDER BY (d.status = 'open') DESC, d.created_at ASC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>Disputes</h1>
    <a href="index.php" class="btn btn-outline btn-sm arrow-left">Back to Dashboard</a>
</div>

<?php if ($disputes->num_rows === 0): ?>
    <p style="color: var(--gray);">No disputes filed yet.</p>
<?php else: ?>
<div style="display: grid; gap: 15px;">
    <?php while ($d = $disputes->fetch_assoc()): ?>
    <div class="card" style="padding: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <strong>Order <a href="order.php?id=<?php echo $d['order_id']; ?>">#<?php echo $d['order_id']; ?></a></strong>
                <span style="color: var(--gray); margin-left: 8px; font-size: 0.85rem;">
                    by <?php echo htmlspecialchars($d['buyer_name']); ?> (<?php echo htmlspecialchars($d['buyer_email']); ?>)
                </span>
            </div>
            <span class="badge <?php echo $d['status'] === 'open' ? 'badge-warning' : 'badge-success'; ?>">
                <?php echo ucfirst($d['status']); ?>
            </span>
        </div>
        <p style="margin-bottom: 8px;"><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($d['reason'])); ?></p>
        <small style="color: var(--gray);">Filed <?php echo date('d M Y H:i', strtotime($d['created_at'])); ?></small>

        <?php if ($d['status'] === 'resolved'): ?>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
                <p><strong>Admin response:</strong> <?php echo nl2br(htmlspecialchars($d['admin_response'])); ?></p>
                <small style="color: var(--gray);">Resolved <?php echo date('d M Y H:i', strtotime($d['resolved_at'])); ?></small>
            </div>
        <?php else: ?>
            <form method="POST" action="" style="margin-top: 12px;">
                <input type="hidden" name="dispute_id" value="<?php echo $d['dispute_id']; ?>">
                <div class="form-group">
                    <label>Response to buyer</label>
                    <textarea name="admin_response" rows="2" required minlength="5"></textarea>
                </div>
                <button type="submit" name="resolve_dispute" class="btn btn-sm btn-primary">Resolve</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>

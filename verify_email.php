<?php
$pageTitle = 'Verify Email';
require_once 'includes/header.php';

// NOTE: I tried to set up SMTP on XAMPP for 2 days but couldn't get it working
// The email sending would fail silently. So I built this simulated inbox
// to demonstrate the email verification concept instead.
// In production this would connect to a real mail server.

if (!isset($_SESSION['verify_user_id']) || !isset($_SESSION['verify_email'])) {
    redirect('register.php');
}

$user_id = $_SESSION['verify_user_id'];
$email = $_SESSION['verify_email'];
$code = $_SESSION['verify_code'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['resend'])) {
        $new_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("UPDATE users SET email_code = ? WHERE user_id = ?");
        $stmt->bind_param('si', $new_code, $user_id);
        $stmt->execute();

        $_SESSION['verify_code'] = $new_code;
        $code = $new_code;

        $_SESSION['message'] = 'A new verification code has been generated. Check your inbox below.';
        $_SESSION['message_type'] = 'success';
        redirect('verify_email.php');
    }

    if (isset($_POST['verify']) && isset($_POST['verification_code'])) {
        $entered_code = sanitize($_POST['verification_code']);

        $stmt = $conn->prepare("SELECT email_code FROM users WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $db_code = $result->fetch_assoc()['email_code'];

            if ($entered_code === $db_code) {
                $conn->query("UPDATE users SET email_verified = 1, email_code = NULL WHERE user_id = $user_id");

                unset($_SESSION['verify_user_id']);
                unset($_SESSION['verify_email']);
                unset($_SESSION['verify_code']);

                $_SESSION['message'] = 'Email verified successfully! You can now log in.';
                $_SESSION['message_type'] = 'success';
                redirect('login.php');
            } else {
                $error = 'Invalid verification code. Please try again.';
            }
        }
    }
}
?>

<div style="max-width: 500px; margin: 40px auto;">

    <div class="auth-container">
        <div style="text-align: center; margin-bottom: 25px;">
            <h2><?php echo __('email_verify_title'); ?></h2>
            <p style="color: var(--gray); margin-top: 10px;">
                <?php echo __('email_verify_desc'); ?>
            </p>
        </div>

        <?php if (isset($error)): ?>
            <?php echo showMessage($error, 'error'); ?>
        <?php endif; ?>

        <div style="border: 1px solid var(--gray); padding: 15px; margin-bottom: 25px;">
            <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 10px;">
                Simulated inbox for <?php echo htmlspecialchars($email); ?>
            </p>
            <p style="margin-bottom: 8px;">
                Welcome to Swaply. Your verification code is:
            </p>
            <p style="font-size: 1.3rem; font-weight: bold; color: var(--primary); margin-bottom: 8px;">
                <?php echo $code; ?>
            </p>
            <p style="font-size: 0.85rem; color: var(--gray);">
                This code expires in 30 minutes.
            </p>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="verification_code"><?php echo __('verification_code'); ?></label>
                <input type="text" id="verification_code" name="verification_code"
                       placeholder="000000" maxlength="6" required
                       style="text-align: center; font-size: 1.2rem;">
            </div>

            <button type="submit" name="verify" class="btn btn-primary" style="width: 100%;">
                <?php echo __('btn_verify'); ?>
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <form method="POST" action="" style="display: inline;">
                <button type="submit" name="resend" class="btn btn-outline btn-sm" style="border: none; color: var(--primary); text-decoration: underline; cursor: pointer;">
                    <?php echo __('resend_code'); ?>
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <small style="color: var(--gray);">
                Wrong email? <a href="register.php">Register again</a> or
                <a href="login.php">try logging in</a> if already verified.
            </small>
        </div>

    </div>

</div>

<?php require_once 'includes/footer.php'; ?>

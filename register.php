<?php
$pageTitle = 'Register';
require_once 'includes/header.php';

// Process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $id_number = sanitize($_POST['id_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'buyer');
    
    $errors = [];
    
    // Server-side validation
    if (strlen($full_name) < 3) {
        $errors[] = 'Full name must be at least 3 characters';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Check if email already exists
    $check = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $errors[] = 'This email is already registered. Please login instead.';
    }
    
    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        $errors[] = 'Please enter a valid 10-digit SA phone number';
    }
    
    // SA ID validation: 13 digits
    if (!preg_match('/^[0-9]{13}$/', $id_number)) {
        $errors[] = 'Please enter a valid 13-digit South African ID number';
    }
    
    // Check if ID already registered
    $checkId = $conn->query("SELECT user_id FROM users WHERE id_number = '$id_number'");
    if ($checkId->num_rows > 0) {
        $errors[] = 'This ID number is already registered on the platform.';
    }
    
    if (!preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/', $password)) {
        $errors[] = 'Password must be 8+ characters with at least 1 number and 1 special character';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!in_array($role, ['buyer', 'seller'])) {
        $role = 'buyer';
    }
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate 6-digit verification code
        $email_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // id_verified = 0: new users must wait for admin approval
        // email_verified = 0: must verify email first
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, id_number, password_hash, role, verified, email_verified, email_code, id_verified) VALUES (?, ?, ?, ?, ?, ?, 1, 0, ?, 0)");
        $stmt->bind_param('sssssss', $full_name, $email, $phone, $id_number, $password_hash, $role, $email_code);
        
        if ($stmt->execute()) {
            $new_user_id = $conn->insert_id;
            
            // Store verification info in session for the verify page
            $_SESSION['verify_user_id'] = $new_user_id;
            $_SESSION['verify_email'] = $email;
            $_SESSION['verify_code'] = $email_code;
            
            // Redirect to email verification page
            redirect('verify_email.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo showMessage($error, 'error');
        }
    }
}

$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'buyer';
?>

<div class="auth-container">
    <h2><?php echo __('auth_register_title'); ?></h2>

    <form method="POST" action="" id="registerForm" novalidate>

        <div class="form-group">
            <label><?php echo __('buy_products'); ?> / <?php echo __('sell_products'); ?></label>
            <div style="display: flex; gap: 15px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 5px; font-weight: normal; cursor: pointer;">
                    <input type="radio" name="role" value="buyer" <?php echo $type !== 'seller' ? 'checked' : ''; ?>>
                    <?php echo __('buy_products'); ?>
                </label>
                <label style="display: flex; align-items: center; gap: 5px; font-weight: normal; cursor: pointer;">
                    <input type="radio" name="role" value="seller" <?php echo $type === 'seller' ? 'checked' : ''; ?>>
                    <?php echo __('sell_products'); ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="full_name"><?php echo __('full_name'); ?></label>
            <input type="text" id="full_name" name="full_name" required
                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="email"><?php echo __('email'); ?></label>
            <input type="email" id="email" name="email" required
                   placeholder="you@example.com"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="phone"><?php echo __('phone'); ?></label>
            <input type="tel" id="phone" name="phone" required
                   placeholder="0721234567"
                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            <small style="color: var(--gray);"><?php echo __('phone_hint'); ?></small>
        </div>

        <div class="form-group">
            <label for="id_number"><?php echo __('sa_id_number'); ?></label>
            <input type="text" id="id_number" name="id_number" required
                   placeholder="8501015800085" maxlength="13"
                   value="<?php echo isset($_POST['id_number']) ? htmlspecialchars($_POST['id_number']) : ''; ?>">
            <small style="color: var(--gray);"><?php echo __('id_hint'); ?></small>
        </div>

        <div class="form-group">
            <label for="password"><?php echo __('password'); ?></label>
            <input type="password" id="password" name="password" required
                   placeholder="<?php echo __('password_hint'); ?>">
            <small style="color: var(--gray);"><?php echo __('password_hint'); ?></small>
        </div>

        <div class="form-group">
            <label for="confirm_password"><?php echo __('confirm_password'); ?></label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;"><?php echo __('btn_create_account'); ?></button>

    </form>

    <div class="auth-link">
        <?php echo __('have_account'); ?> <a href="login.php"><?php echo __('login_here'); ?></a>
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>

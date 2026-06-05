<?php
$pageTitle = 'Login';
require_once 'includes/header.php';

// login processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (empty($email) || empty($password)) {
        $errors[] = 'Please enter both email and password';
    }
    
    if (empty($errors)) {
        // fetch user from database
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash, role, email_verified, id_verified, email_code FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        // DEBUG: echo "rows found: " . $result->num_rows . "<br>";
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                
                // Check if email is verified (skip for admin)
                if ($user['role'] !== 'admin' && !$user['email_verified']) {
                    // Generate new code if needed
                    if (empty($user['email_code'])) {
                        $new_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                        $codeStmt = $conn->prepare("UPDATE users SET email_code = ? WHERE user_id = ?");
                        $codeStmt->bind_param('si', $new_code, $user['user_id']);
                        $codeStmt->execute();
                        $user['email_code'] = $new_code;
                    }
                    
                    $_SESSION['verify_user_id'] = $user['user_id'];
                    $_SESSION['verify_email'] = $user['email'];
                    $_SESSION['verify_code'] = $user['email_code'];
                    
                    $_SESSION['message'] = 'Please verify your email before logging in.';
                    $_SESSION['message_type'] = 'error';
                    redirect('verify_email.php');
                }
                
                // Login successful - set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email_verified'] = (int)$user['email_verified'];
                $_SESSION['id_verified'] = (int)$user['id_verified'];
                
                $_SESSION['message'] = __('welcome_back') . ', ' . $user['full_name'] . '!';
                $_SESSION['message_type'] = 'success';
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $errors[] = 'Invalid email or password';
            }
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo showMessage($error, 'error');
        }
    }
}
?>

<div class="auth-container">
    <h2><?php echo __('auth_login_title'); ?></h2>

    <form method="POST" action="" id="loginForm" novalidate>

        <div class="form-group">
            <label for="email"><?php echo __('email'); ?></label>
            <input type="email" id="email" name="email" required
                   placeholder="you@example.com"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="password"><?php echo __('password'); ?></label>
            <input type="password" id="password" name="password" required
                   placeholder="<?php echo __('password'); ?>">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;"><?php echo __('btn_login'); ?></button>

    </form>

    <div class="auth-link">
        <?php echo __('no_account'); ?> <a href="register.php"><?php echo __('register_here'); ?></a>
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>

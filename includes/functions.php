<?php
// helper functions used across the site
// learnt this pattern in week 8 - separating reusable code

ob_start(); // buffer output so header() / redirect() still work after HTML
session_start(); // IMPORTANT: must be first before any output

require_once 'db.php';
require_once 'translations.php';

// check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// check role - used for admin and seller pages
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// email check - added this for the verification system
function isEmailVerified() {
    return isset($_SESSION['email_verified']) && $_SESSION['email_verified'] === 1;
}

// ID verification check - Goal 1 from deliverable 1
function isIdVerified() {
    return isset($_SESSION['id_verified']) && $_SESSION['id_verified'] === 1;
}

// blocks users who haven't been ID verified yet
function requireIdVerification() {
    if (!isIdVerified()) {
        $_SESSION['message'] = 'Your account is pending ID verification. Please complete verification to access this feature.';
        $_SESSION['message_type'] = 'error';
        redirect('verification_pending.php');
    }
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Sanitize user input
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

/**
 * Show success/error message
 */
function showMessage($message, $type = 'success') {
    $class = $type === 'success' ? 'alert-success' : 'alert-error';
    return "<div class='alert $class'>" . htmlspecialchars($message) . "</div>";
}

/**
 * Format price in Rands
 */
function formatPrice($price) {
    return 'R ' . number_format($price, 2);
}

/**
 * Get cart count for current user
 */
function getCartCount() {
    global $conn;
    if (!isLoggedIn()) return 0;
    
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id");
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

/**
 * Calculate average rating for a seller
 */
function getSellerRating($seller_id) {
    global $conn;
    $result = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE seller_id = $seller_id");
    $row = $result->fetch_assoc();
    return [
        'average' => round($row['avg_rating'] ?? 0, 1),
        'total' => $row['total'] ?? 0
    ];
}

/**
 * Truncate text
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Generate star rating HTML
 */
function renderStars($rating) {
    $filled = (int) round($rating);
    $stars = '<span class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        $class = ($i <= $filled) ? 'star' : 'star empty';
        $stars .= '<span class="' . $class . '">*</span>';
    }
    $stars .= '</span>';
    return $stars;
}
?>
